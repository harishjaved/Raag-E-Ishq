<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\InventoryService;
use App\Services\RazorpayService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request, InventoryService $inventoryService, TicketService $ticketService): View
    {
        $inventoryService->releaseExpiredHolds();

        $query = Booking::with(['contact', 'items.category', 'tickets.category']);

        $status = $request->query('status');
        if ($status) {
            if ($status === 'PENDING' || $status === 'PAYMENT_PENDING') {
                $query->whereIn('status', [Booking::STATUS_PAYMENT_PENDING, 'PENDING']);
            } elseif ($status === 'CANCELLED') {
                $query->whereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_HOLD_EXPIRED]);
            } else {
                $query->where('status', $status);
            }
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->searchTerm($search);
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        // Precompute quick WhatsApp URLs & QR codes for each booking
        foreach ($bookings as $booking) {
            $booking->whatsapp_payment_url = $ticketService->buildWhatsAppUrl(
                $booking->contact->phone,
                $ticketService->buildWhatsAppPaymentMessage($booking)
            );

            // Passes link & QR codes for confirmed bookings
            if ($booking->status === Booking::STATUS_CONFIRMED) {
                $ticketMsg = $ticketService->buildWhatsAppTicketMessage($booking);
                $booking->whatsapp_ticket_url = $ticketService->buildWhatsAppUrl($booking->contact->phone, $ticketMsg);
                $booking->whatsapp_ticket_text = $ticketMsg;

                // Precompute QR SVGs and download URLs so admin can view/copy immediately
                foreach ($booking->tickets as $ticket) {
                    $ticket->qr_svg = $ticketService->generateAdmissionQrSvg($ticket, 240);
                    $ticket->qr_download_url = route('ticket.qr.download', ['uuid' => $ticket->qr_uuid]);
                    $ticket->download_url = \Illuminate\Support\Facades\URL::signedRoute('ticket.download', ['uuid' => $ticket->qr_uuid]);
                }
            }
        }

        $justApprovedBooking = null;
        if ($justApprovedId = session('just_approved_booking_id')) {
            $justApprovedBooking = Booking::with(['contact', 'items.category', 'tickets.category'])->find($justApprovedId);
            if ($justApprovedBooking) {
                $ticketMsg = $ticketService->buildWhatsAppTicketMessage($justApprovedBooking);
                $justApprovedBooking->whatsapp_ticket_url = $ticketService->buildWhatsAppUrl($justApprovedBooking->contact->phone, $ticketMsg);
                $justApprovedBooking->whatsapp_ticket_text = $ticketMsg;

                foreach ($justApprovedBooking->tickets as $ticket) {
                    $ticket->qr_svg = $ticketService->generateAdmissionQrSvg($ticket, 240);
                    $ticket->qr_download_url = route('ticket.qr.download', ['uuid' => $ticket->qr_uuid]);
                    $ticket->download_url = \Illuminate\Support\Facades\URL::signedRoute('ticket.download', ['uuid' => $ticket->qr_uuid]);
                }
            }
        }

        $counts = [
            'all' => Booking::count(),
            'pending' => Booking::whereIn('status', [Booking::STATUS_PAYMENT_PENDING, 'PENDING'])->count(),
            'in_process' => Booking::where('status', Booking::STATUS_IN_PROCESS)->count(),
            'completed' => Booking::where('status', Booking::STATUS_CONFIRMED)->count(),
            'cancelled' => Booking::whereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_HOLD_EXPIRED])->count(),
        ];

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'currentStatus' => $status,
            'search' => $search,
            'counts' => $counts,
            'justApprovedBooking' => $justApprovedBooking,
        ]);
    }

    public function show(int $id, TicketService $ticketService): View
    {
        $booking = Booking::with([
            'contact',
            'items.category',
            'paymentAttempts',
            'tickets.category',
            'tickets.checkin.scannedBy',
            'refunds',
        ])->findOrFail($id);

        $booking->whatsapp_payment_url = $ticketService->buildWhatsAppUrl(
            $booking->contact->phone,
            $ticketService->buildWhatsAppPaymentMessage($booking)
        );

        if ($booking->status === Booking::STATUS_CONFIRMED) {
            $ticketMsg = $ticketService->buildWhatsAppTicketMessage($booking);
            $booking->whatsapp_ticket_url = $ticketService->buildWhatsAppUrl($booking->contact->phone, $ticketMsg);
            $booking->whatsapp_ticket_text = $ticketMsg;
        }

        foreach ($booking->tickets as $ticket) {
            $ticket->qr_svg = $ticketService->generateAdmissionQrSvg($ticket);
            $ticket->qr_download_url = route('ticket.qr.download', ['uuid' => $ticket->qr_uuid]);
            $ticket->download_url = \Illuminate\Support\Facades\URL::signedRoute('ticket.download', ['uuid' => $ticket->qr_uuid]);
        }

        return view('admin.bookings.show', [
            'booking' => $booking,
        ]);
    }

    public function confirmPayment(int $id, RazorpayService $razorpayService): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status === Booking::STATUS_CONFIRMED) {
            return back()->with('info', 'Booking is already confirmed.');
        }

        $providerEventId = 'evt_admin_' . Str::random(16);
        $paymentId = 'pay_admin_' . Str::random(14);
        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $paymentId,
                        'amount' => $booking->total_amount_paise,
                        'currency' => 'INR',
                        'status' => 'captured',
                        'notes' => ['booking_reference' => $booking->booking_reference],
                        'method' => 'upi',
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $secret = config('services.razorpay.webhook_secret') ?: 'whsec_test_dummy';
        config(['services.razorpay.webhook_secret' => $secret]);
        $signature = hash_hmac('sha256', $rawBody, $secret);

        $razorpayService->handleWebhook($providerEventId, 'payment.captured', $payload, $rawBody, $signature);

        return back()->with([
            'success' => "Payment for booking {$booking->booking_reference} confirmed and tickets successfully issued.",
            'just_approved_booking_id' => $booking->id,
        ]);
    }

    public function updateStatus(Request $request, int $id, RazorpayService $razorpayService, InventoryService $inventoryService): RedirectResponse
    {
        $booking = Booking::with('contact')->findOrFail($id);
        $newStatus = strtoupper($request->input('status'));

        if ($newStatus === 'CONFIRMED' || $newStatus === 'COMPLETED') {
            return $this->confirmPayment($id, $razorpayService);
        }

        if ($newStatus === 'CANCELLED') {
            $inventoryService->cancelBooking($booking, $request->input('reason', 'Cancelled from admin desk'), auth()->id());
            return back()->with('info', "Booking {$booking->booking_reference} marked as cancelled and reserved seats released.");
        }

        if (in_array($newStatus, [Booking::STATUS_IN_PROCESS, Booking::STATUS_PAYMENT_PENDING, 'PENDING'])) {
            $oldStatus = $booking->status;
            $normalizedStatus = ($newStatus === 'PENDING') ? Booking::STATUS_PAYMENT_PENDING : $newStatus;

            $booking->update(['status' => $normalizedStatus]);

            \App\Models\AuditLog::log(
                action: 'BOOKING_STATUS_UPDATED',
                targetType: 'Booking',
                targetId: $booking->id,
                oldValues: ['status' => $oldStatus],
                newValues: [
                    'status' => $normalizedStatus,
                    'booking_reference' => $booking->booking_reference,
                    'note' => $request->input('note', 'Updated via Back Office'),
                ],
                userId: auth()->id()
            );

            return back()->with('success', "Booking {$booking->booking_reference} status updated to {$normalizedStatus}.");
        }

        return back()->withErrors(['status' => 'Invalid status transition requested.']);
    }
}

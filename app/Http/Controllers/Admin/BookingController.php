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
    public function index(Request $request, InventoryService $inventoryService): View
    {
        $inventoryService->releaseExpiredHolds();

        $query = Booking::with(['contact', 'items.category'])->latest();

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

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhereHas('contact', function ($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->paginate(20);

        // Precompute quick WhatsApp URLs for each booking
        foreach ($bookings as $booking) {
            $phoneClean = preg_replace('/[^0-9]/', '', $booking->contact->phone);
            if (strlen($phoneClean) === 10) {
                $phoneClean = '91' . $phoneClean;
            }
            $amountFormatted = number_format($booking->total_amount_paise / 100, 2);
            
            // Payment instructions message for Pending / In-Process enquiries
            $payMsg = "Hello " . $booking->contact->full_name . "! Thank you for booking Raag-E-Ishq (Ref: " . $booking->booking_reference . "). Total payable is ₹" . $amountFormatted . ". Please pay via UPI to mujammilibrahim007@okhdfcbank and reply with your payment screenshot/UTR to confirm your tickets.";
            $booking->whatsapp_payment_url = "https://wa.me/" . $phoneClean . "?text=" . urlencode($payMsg);

            // Tickets link for confirmed bookings
            if ($booking->status === Booking::STATUS_CONFIRMED) {
                $ticketsUrl = route('booking.confirmed', $booking->booking_reference);
                $ticketMsg = "Hello " . $booking->contact->full_name . "! Your admission passes for Joygivers Raag-E-Ishq (Booking Ref: " . $booking->booking_reference . ") are confirmed! View and download your QR entry passes here: " . $ticketsUrl;
                $booking->whatsapp_ticket_url = "https://wa.me/" . $phoneClean . "?text=" . urlencode($ticketMsg);
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

        $phoneClean = preg_replace('/[^0-9]/', '', $booking->contact->phone);
        if (strlen($phoneClean) === 10) {
            $phoneClean = '91' . $phoneClean;
        }
        $amountFormatted = number_format($booking->total_amount_paise / 100, 2);
        $payMsg = "Hello " . $booking->contact->full_name . "! Thank you for booking Raag-E-Ishq (Ref: " . $booking->booking_reference . "). Total payable is ₹" . $amountFormatted . ". Please pay via UPI to mujammilibrahim007@okhdfcbank and reply with your payment screenshot/UTR to confirm your tickets.";
        $booking->whatsapp_payment_url = "https://wa.me/" . $phoneClean . "?text=" . urlencode($payMsg);

        if ($booking->status === Booking::STATUS_CONFIRMED) {
            $ticketsUrl = route('booking.confirmed', $booking->booking_reference);
            $ticketMsg = "Hello " . $booking->contact->full_name . "! Your admission passes for Joygivers Raag-E-Ishq (Booking Ref: " . $booking->booking_reference . ") are confirmed! View and download your QR entry passes here: " . $ticketsUrl;
            $booking->whatsapp_ticket_url = "https://wa.me/" . $phoneClean . "?text=" . urlencode($ticketMsg);
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

        return back()->with('success', "Payment for booking {$booking->booking_reference} confirmed and tickets successfully issued.");
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

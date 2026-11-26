<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\OutOfStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Concerns\SanitizesInputs;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Donation;
use App\Models\TicketCategory;
use App\Services\InventoryService;
use App\Services\RazorpayService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    use SanitizesInputs;

    public function index(Request $request, InventoryService $inventoryService, TicketService $ticketService): View
    {
        $inventoryService->releaseExpiredHolds();

        $query = Booking::with(['contact', 'items.category', 'tickets.category']);

        $status = $request->query('status');
        if ($status) {
            if (in_array($status, ['PENDING', 'PAYMENT_PENDING', 'REQUEST_SUBMITTED'], true)) {
                $query->whereIn('status', [Booking::STATUS_REQUEST_SUBMITTED, Booking::STATUS_PAYMENT_PENDING, 'PENDING']);
            } elseif (in_array($status, ['PAYMENT_SUBMITTED', 'IN_PROCESS'], true)) {
                $query->whereIn('status', [Booking::STATUS_PAYMENT_SUBMITTED, Booking::STATUS_VERIFYING_PAYMENT, Booking::STATUS_IN_PROCESS]);
            } elseif ($status === 'CANCELLED') {
                $query->whereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_HOLD_EXPIRED]);
            } elseif ($status === 'CONFIRMED') {
                $query->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_BOOKING_CONFIRMED, Booking::STATUS_TICKETS_ISSUED]);
            } else {
                $query->where('status', $status);
            }
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->searchTerm($search);
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        foreach ($bookings as $booking) {
            $this->hydrateBookingDispatchMeta($booking, $ticketService);
        }

        $justApprovedBooking = null;
        if ($justApprovedId = session('just_approved_booking_id')) {
            $justApprovedBooking = Booking::with(['contact', 'items.category', 'tickets.category'])->find($justApprovedId);
            if ($justApprovedBooking) {
                $this->hydrateBookingDispatchMeta($justApprovedBooking, $ticketService);
            }
        }

        $statusCounts = Booking::query()
            ->selectRaw('COUNT(*) as all_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN 1 ELSE 0 END), 0) as pending_count', [
                Booking::STATUS_REQUEST_SUBMITTED,
                Booking::STATUS_PAYMENT_PENDING,
                'PENDING',
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN 1 ELSE 0 END), 0) as in_process_count', [
                Booking::STATUS_PAYMENT_SUBMITTED,
                Booking::STATUS_VERIFYING_PAYMENT,
                Booking::STATUS_IN_PROCESS,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN 1 ELSE 0 END), 0) as completed_count', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_BOOKING_CONFIRMED,
                Booking::STATUS_TICKETS_ISSUED,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END), 0) as cancelled_count', [
                Booking::STATUS_CANCELLED,
                Booking::STATUS_HOLD_EXPIRED,
            ])
            ->first();

        $counts = [
            'all' => (int) ($statusCounts->all_count ?? 0),
            'pending' => (int) ($statusCounts->pending_count ?? 0),
            'in_process' => (int) ($statusCounts->in_process_count ?? 0),
            'completed' => (int) ($statusCounts->completed_count ?? 0),
            'cancelled' => (int) ($statusCounts->cancelled_count ?? 0),
        ];

        $categories = TicketCategory::with('event')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->map(function ($cat) use ($inventoryService) {
                $cat->available = $inventoryService->getAvailableCapacity($cat);
                return $cat;
            });

        $recentDonations = Donation::with('contact')->latest()->limit(15)->get();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'currentStatus' => $status,
            'search' => $search,
            'counts' => $counts,
            'justApprovedBooking' => $justApprovedBooking,
            'categories' => $categories,
            'recentDonations' => $recentDonations,
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
            'createdBy',
            'verifiedBy',
        ])->findOrFail($id);

        $this->hydrateBookingDispatchMeta($booking, $ticketService);

        return view('admin.bookings.show', [
            'booking' => $booking,
        ]);
    }

    public function storeOffline(Request $request, InventoryService $inventoryService, TicketService $ticketService): RedirectResponse
    {
        $request->merge([
            'full_name' => $this->sanitizeString($request->input('full_name')),
            'email' => strtolower(trim((string) $request->input('email'))),
            'phone' => preg_replace('/[^0-9]/', '', (string) $request->input('phone')),
            'utr_reference' => $this->filledSanitized($request, 'utr_reference'),
        ]);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'ticket_category_id' => ['required', 'integer', 'exists:ticket_categories,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:25'],
            'payment_channel' => ['required', 'in:CASH,UPI_OFFLINE,BANK_TRANSFER,POS_CARD,COMPLIMENTARY'],
            'utr_reference' => ['nullable', 'string', 'max:120'],
            'mark_paid' => ['nullable', 'boolean'],
        ]);

        try {
            $booking = $inventoryService->createOfflineBooking(
                data: $validated,
                adminUser: $request->user(),
                ticketService: $ticketService,
                ipAddress: $request->ip()
            );

            $flash = [
                'success' => "Offline booking {$booking->booking_reference} recorded in live inventory" . ($booking->isConfirmed() ? ' with QR Entry Passes issued.' : ' with 30-minute temporary hold.'),
            ];
            if ($booking->isConfirmed()) {
                $flash['just_approved_booking_id'] = $booking->id;
            }

            return back()->with($flash);
        } catch (OutOfStockException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }
    }

    public function confirmPayment(Request $request, int $id, RazorpayService $razorpayService): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        if ($booking->isConfirmed()) {
            return back()->with('info', 'Booking is already confirmed.');
        }

        if ($utr = $this->filledSanitized($request, 'utr_reference')) {
            $booking->update([
                'utr_reference' => $utr,
                'payment_submitted_at' => $booking->payment_submitted_at ?? now(),
            ]);
        }

        $providerEventId = 'evt_admin_' . Str::random(16);
        $paymentId = $booking->utr_reference ?: ('pay_admin_' . Str::random(14));
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
                        'method' => strtolower((string) ($booking->payment_channel ?: 'upi')),
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $secret = config('services.razorpay.webhook_secret') ?: 'whsec_test_dummy';
        config(['services.razorpay.webhook_secret' => $secret]);
        $signature = hash_hmac('sha256', $rawBody, $secret);

        $result = $razorpayService->handleWebhook($providerEventId, 'payment.captured', $payload, $rawBody, $signature);
        $booking->refresh();

        if (!$booking->isConfirmed()) {
            return back()->with('error', "Could not confirm booking {$booking->booking_reference}: " . ($result['message'] ?? "Current status is {$booking->status}."));
        }

        if (!$booking->verified_by_user_id && auth()->id()) {
            $booking->update(['verified_by_user_id' => auth()->id(), 'payment_verified_at' => $booking->payment_verified_at ?? now()]);
        }

        return back()->with([
            'success' => "Payment verified & booking {$booking->booking_reference} confirmed; QR Entry Passes issued.",
            'just_approved_booking_id' => $booking->id,
        ]);
    }

    public function resendPasses(int $id, TicketService $ticketService): RedirectResponse
    {
        $booking = Booking::with(['tickets', 'contact'])->findOrFail($id);

        if (!$booking->isConfirmed()) {
            return back()->withErrors(['status' => 'Passes can only be resent after payment is verified and booking is confirmed.']);
        }

        if ($booking->is_attending && $booking->tickets->isEmpty()) {
            $ticketService->issueTicketsForBooking($booking);
        } elseif ($booking->contact && !empty($booking->contact->email)) {
            \App\Jobs\SendTicketEmailJob::dispatch($booking);
        }

        AuditLog::log(
            action: 'TICKET_PASSES_RESENT',
            targetType: 'Booking',
            targetId: $booking->id,
            newValues: [
                'booking_reference' => $booking->booking_reference,
                'tickets_count' => $booking->tickets()->count(),
                'resent_by' => auth()->user()?->name,
            ],
            userId: auth()->id()
        );

        return back()->with([
            'success' => "Ticket passes prepared & dispatched for {$booking->booking_reference}.",
            'just_approved_booking_id' => $booking->id,
        ]);
    }

    public function acknowledgeDonation(Request $request, int $id): RedirectResponse
    {
        $donation = Donation::findOrFail($id);

        $utr = $this->filledSanitized($request, 'utr_reference') ?: $donation->utr_reference;
        $oldStatus = $donation->status;

        $donation->update([
            'status' => Donation::STATUS_ACKNOWLEDGED,
            'utr_reference' => $utr,
            'acknowledged_at' => now(),
            'verified_by_user_id' => auth()->id(),
        ]);

        AuditLog::log(
            action: 'CAUSE_DONATION_ACKNOWLEDGED',
            targetType: 'Donation',
            targetId: $donation->id,
            oldValues: ['status' => $oldStatus],
            newValues: [
                'status' => Donation::STATUS_ACKNOWLEDGED,
                'donation_reference' => $donation->donation_reference,
                'utr_reference' => $utr,
            ],
            userId: auth()->id()
        );

        return back()->with('success', "Donation {$donation->donation_reference} verified and acknowledged.");
    }

    public function updateStatus(Request $request, int $id, RazorpayService $razorpayService, InventoryService $inventoryService): RedirectResponse
    {
        $booking = Booking::with('contact')->findOrFail($id);
        $newStatus = strtoupper((string) $request->input('status'));

        if (in_array($newStatus, ['CONFIRMED', 'COMPLETED', 'BOOKING_CONFIRMED', 'PAYMENT_VERIFIED'], true)) {
            return $this->confirmPayment($request, $id, $razorpayService);
        }

        if ($newStatus === 'CANCELLED') {
            $inventoryService->cancelBooking($booking, $request->input('reason', 'Cancelled from admin desk'), auth()->id());
            return back()->with('info', "Booking {$booking->booking_reference} marked as cancelled and reserved seats released.");
        }

        if (in_array($newStatus, [Booking::STATUS_IN_PROCESS, Booking::STATUS_PAYMENT_PENDING, Booking::STATUS_PAYMENT_SUBMITTED, 'PENDING'], true)) {
            $oldStatus = $booking->status;
            $normalizedStatus = ($newStatus === 'PENDING') ? Booking::STATUS_PAYMENT_PENDING : $newStatus;

            $booking->update(['status' => $normalizedStatus]);

            AuditLog::log(
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

    protected function hydrateBookingDispatchMeta(Booking $booking, TicketService $ticketService): void
    {
        $booking->whatsapp_payment_url = $ticketService->buildWhatsAppUrl(
            $booking->contact->phone,
            $ticketService->buildWhatsAppPaymentMessage($booking)
        );

        if ($booking->isConfirmed()) {
            $ticketMsg = $ticketService->buildWhatsAppTicketMessage($booking);
            $booking->whatsapp_ticket_url = $ticketService->buildWhatsAppUrl($booking->contact->phone, $ticketMsg);
            $booking->whatsapp_ticket_text = $ticketMsg;

            foreach ($booking->tickets as $ticket) {
                $ticket->qr_svg = $ticketService->generateAdmissionQrSvg($ticket, 240);
                $ticket->qr_download_url = route('ticket.qr.download', ['uuid' => $ticket->qr_uuid]);
                $ticket->download_url = \Illuminate\Support\Facades\URL::signedRoute('ticket.download', ['uuid' => $ticket->qr_uuid]);
            }
        }
    }

    protected function filledSanitized(Request $request, string $key): ?string
    {
        if (!$request->filled($key)) {
            return null;
        }
        $clean = $this->sanitizeString((string) $request->input($key));
        return $clean !== '' ? $clean : null;
    }
}

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

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhereHas('contact', function ($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->paginate(20);

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'currentStatus' => $status,
            'search' => $search,
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

        foreach ($booking->tickets as $ticket) {
            $ticket->qr_svg = $ticketService->generateAdmissionQrSvg($ticket);
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
}

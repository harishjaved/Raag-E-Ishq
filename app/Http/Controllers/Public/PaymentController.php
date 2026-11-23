<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(string $reference, RazorpayService $razorpayService): View|RedirectResponse
    {
        $booking = Booking::with(['items.category', 'contact'])
            ->where('booking_reference', $reference)
            ->firstOrFail();

        if ($booking->status === Booking::STATUS_CONFIRMED) {
            return redirect()->route('booking.confirmed', ['reference' => $reference]);
        }

        $paymentAttempt = $razorpayService->createPaymentAttempt($booking);
        $qrSvg = $razorpayService->generatePaymentQrSvg($paymentAttempt);

        $remainingSeconds = max(0, $booking->hold_expires_at ? now()->diffInSeconds($booking->hold_expires_at, false) : 600);

        return view('public.payment', [
            'booking' => $booking,
            'paymentAttempt' => $paymentAttempt,
            'qrSvg' => $qrSvg,
            'remainingSeconds' => $remainingSeconds,
            'rupeesFormatted' => number_format($booking->total_amount_paise / 100, 2),
        ]);
    }

    public function status(string $reference): JsonResponse
    {
        $booking = Booking::where('booking_reference', $reference)->firstOrFail();

        return response()->json([
            'booking_reference' => $booking->booking_reference,
            'status' => $booking->status,
            'confirmed' => $booking->status === Booking::STATUS_CONFIRMED,
            'hold_expired' => $booking->status === Booking::STATUS_HOLD_EXPIRED,
            'reconciliation' => $booking->status === Booking::STATUS_RECONCILIATION_REQUIRED,
        ]);
    }

    public function simulatePayment(string $reference, RazorpayService $razorpayService, \App\Services\TicketService $ticketService): RedirectResponse|JsonResponse
    {
        $booking = Booking::with(['items.category', 'contact'])->where('booking_reference', $reference)->firstOrFail();

        if ($booking->status === Booking::STATUS_CONFIRMED) {
            return redirect()->route('booking.confirmed', ['reference' => $reference]);
        }

        $providerEventId = 'evt_sim_' . \Illuminate\Support\Str::random(16);
        $paymentId = 'pay_sim_' . \Illuminate\Support\Str::random(14);
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

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'redirect' => route('booking.confirmed', ['reference' => $reference])]);
        }

        return redirect()->route('booking.confirmed', ['reference' => $reference]);
    }
}

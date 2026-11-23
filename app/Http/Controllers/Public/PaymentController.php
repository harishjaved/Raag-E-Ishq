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
}

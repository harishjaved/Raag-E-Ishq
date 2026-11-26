<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\EventSetting;
use App\Models\PaymentAttempt;
use App\Services\InventoryService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(string $reference, RazorpayService $razorpayService, InventoryService $inventoryService): View|RedirectResponse
    {
        $inventoryService->releaseExpiredHolds();

        $booking = Booking::with(['items.category', 'contact', 'tickets'])
            ->where('booking_reference', $reference)
            ->first();

        if (!$booking) {
            return redirect()->route('event.show')->withErrors([
                'quantity' => "Booking reference '{$reference}' not found or has expired. Please select your seats below to submit a new reservation request."
            ]);
        }

        if ($booking->isConfirmed()) {
            return redirect()->route('booking.confirmed', ['reference' => $reference]);
        }

        $holdMinutes = max(1, (int) EventSetting::get('hold_duration_minutes', 30));

        if ($booking->status === Booking::STATUS_HOLD_EXPIRED) {
            return redirect()->route('event.show')->withErrors([
                'quantity' => "Your {$holdMinutes}-minute seat hold for {$booking->booking_reference} expired before payment was completed. Released seats have returned to availability; please submit a fresh reservation below."
            ]);
        }

        $paymentAttempt = $razorpayService->createPaymentAttempt($booking);
        $qrSvg = $razorpayService->generatePaymentQrSvg($paymentAttempt);

        $remainingSeconds = $booking->hold_expires_at
            ? max(0, (int) Carbon::now()->diffInSeconds($booking->hold_expires_at, false))
            : ($holdMinutes * 60);

        $rupeesFormatted = $booking->total_amount_paise % 100 === 0
            ? number_format($booking->total_amount_paise / 100, 0)
            : number_format($booking->total_amount_paise / 100, 2);

        return view('public.payment', [
            'booking' => $booking,
            'paymentAttempt' => $paymentAttempt,
            'qrSvg' => $qrSvg,
            'remainingSeconds' => $remainingSeconds,
            'holdMinutes' => $holdMinutes,
            'rupeesFormatted' => $rupeesFormatted,
            'upiVpa' => config('services.razorpay.merchant_vpa', 'mujammilibrahim007@okhdfcbank'),
            'upiPayee' => config('services.razorpay.merchant_name', 'Joygivers Event Desk'),
        ]);
    }

    public function submitPaymentProof(Request $request, string $reference, InventoryService $inventoryService): RedirectResponse|JsonResponse
    {
        $inventoryService->releaseExpiredHolds();

        $validated = $request->validate([
            'utr_reference' => ['required', 'string', 'min:4', 'max:120'],
            'payment_screenshot_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $booking = Booking::where('booking_reference', $reference)->firstOrFail();

        if ($booking->isConfirmed()) {
            return redirect()->route('booking.confirmed', ['reference' => $reference]);
        }

        if ($booking->status === Booking::STATUS_HOLD_EXPIRED || $booking->status === Booking::STATUS_CANCELLED) {
            return redirect()->route('event.show')->withErrors([
                'quantity' => "This reservation ({$booking->booking_reference}) has expired or been cancelled. Please place a new reservation request."
            ]);
        }

        $utr = trim(strip_tags($validated['utr_reference']));
        $screenshotRef = !empty($validated['payment_screenshot_reference'])
            ? trim(strip_tags($validated['payment_screenshot_reference']))
            : null;

        $oldStatus = $booking->status;
        $booking->update([
            'status' => Booking::STATUS_PAYMENT_SUBMITTED,
            'utr_reference' => $utr,
            'payment_screenshot_reference' => $screenshotRef,
            'payment_submitted_at' => Carbon::now(),
        ]);

        $paymentAttempt = $booking->paymentAttempts()->latest()->first();
        if ($paymentAttempt) {
            $paymentAttempt->update([
                'provider_payment_id' => $utr,
                'status' => PaymentAttempt::STATUS_AWAITING_PAYMENT,
            ]);
        }

        AuditLog::log(
            action: 'PAYMENT_PROOF_SUBMITTED',
            targetType: 'Booking',
            targetId: $booking->id,
            oldValues: ['status' => $oldStatus],
            newValues: [
                'status' => Booking::STATUS_PAYMENT_SUBMITTED,
                'booking_reference' => $booking->booking_reference,
                'utr_reference' => $utr,
                'payment_screenshot_reference' => $screenshotRef,
            ]
        );

        $msg = "Payment proof (UTR: {$utr}) submitted! Our finance desk is verifying payment before confirming and issuing your QR Entry Passes.";

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'booking_status' => $booking->status, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    public function status(string $reference, InventoryService $inventoryService): JsonResponse
    {
        $inventoryService->releaseExpiredHolds();

        $booking = Booking::where('booking_reference', $reference)->firstOrFail();
        $remainingSeconds = $booking->hold_expires_at
            ? max(0, (int) Carbon::now()->diffInSeconds($booking->hold_expires_at, false))
            : 0;

        return response()->json([
            'booking_reference' => $booking->booking_reference,
            'status' => $booking->status,
            'confirmed' => $booking->isConfirmed(),
            'payment_submitted' => $booking->isPaymentSubmitted(),
            'hold_expired' => $booking->status === Booking::STATUS_HOLD_EXPIRED,
            'reconciliation' => $booking->status === Booking::STATUS_RECONCILIATION_REQUIRED,
            'remaining_seconds' => $remainingSeconds,
            'utr_reference' => $booking->utr_reference,
        ]);
    }

    public function simulatePayment(string $reference, RazorpayService $razorpayService, \App\Services\TicketService $ticketService): RedirectResponse|JsonResponse
    {
        if (config('services.razorpay.live_payments_enabled') || config('app.env') === 'production') {
            abort(403, 'Payment simulation is strictly forbidden when live payments are active.');
        }

        $booking = Booking::with(['items.category', 'contact'])->where('booking_reference', $reference)->firstOrFail();

        if ($booking->isConfirmed()) {
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

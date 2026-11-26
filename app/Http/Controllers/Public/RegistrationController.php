<?php

namespace App\Http\Controllers\Public;

use App\Exceptions\OutOfStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Models\EventSetting;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class RegistrationController extends Controller
{
    public function store(RegistrationRequest $request, InventoryService $inventoryService): RedirectResponse|JsonResponse
    {
        try {
            $booking = $inventoryService->createBookingWithHold(
                data: $request->validated(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );

            $holdMinutes = max(1, (int) EventSetting::get('hold_duration_minutes', 30));
            $message = $booking->is_attending
                ? "Reservation request submitted ({$booking->booking_reference}). Your seats are temporarily held for {$holdMinutes} minutes while awaiting payment verification."
                : "Support request submitted ({$booking->booking_reference}). Please complete UPI payment to receive your Official Supporter Receipt.";

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'booking_reference' => $booking->booking_reference,
                    'booking_status' => $booking->status,
                    'total_amount_paise' => $booking->total_amount_paise,
                    'is_attending' => $booking->is_attending,
                    'hold_minutes' => $holdMinutes,
                    'hold_expires_at' => $booking->hold_expires_at?->toIso8601String(),
                    'payment_url' => route('booking.payment', ['reference' => $booking->booking_reference]),
                    'message' => $message,
                ], 201);
            }

            return redirect()->route('booking.payment', ['reference' => $booking->booking_reference])
                ->with('success', $message);
        } catch (OutOfStockException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => ['quantity' => [$e->getMessage()]],
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['quantity' => $e->getMessage()]);
        }
    }
}

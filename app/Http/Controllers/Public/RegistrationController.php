<?php

namespace App\Http\Controllers\Public;

use App\Exceptions\OutOfStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
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

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'booking_reference' => $booking->booking_reference,
                    'total_amount_paise' => $booking->total_amount_paise,
                    'is_attending' => $booking->is_attending,
                    'payment_url' => route('booking.payment', ['reference' => $booking->booking_reference]),
                    'message' => 'Your booking enquiry has been received! Our payment team will call you shortly on your phone number to confirm details and share payment options.',
                ], 201);
            }

            return redirect()->route('booking.payment', ['reference' => $booking->booking_reference])
                ->with('success', 'Your booking enquiry has been received! Our payment team will call you shortly on your phone number to confirm details and share payment options.');
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

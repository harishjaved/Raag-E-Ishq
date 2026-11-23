<?php

namespace App\Http\Controllers\Public;

use App\Exceptions\OutOfStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;

class RegistrationController extends Controller
{
    public function store(RegistrationRequest $request, InventoryService $inventoryService): RedirectResponse
    {
        try {
            $booking = $inventoryService->createBookingWithHold(
                data: $request->validated(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );

            return redirect()->route('booking.payment', ['reference' => $booking->booking_reference])
                ->with('success', 'Your booking enquiry has been received! Our payment team will call you shortly on your phone number to confirm details and share payment options.');
        } catch (OutOfStockException $e) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => $e->getMessage()]);
        }
    }
}

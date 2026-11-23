<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConfirmationController extends Controller
{
    public function show(string $reference, TicketService $ticketService): View|RedirectResponse
    {
        $booking = Booking::with(['items.category', 'contact', 'tickets.category'])
            ->where('booking_reference', $reference)
            ->firstOrFail();

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return redirect()->route('booking.payment', ['reference' => $reference]);
        }

        // Ensure tickets are loaded with admission QR SVGs
        $ticketsWithQrs = $booking->tickets->map(function ($ticket) use ($ticketService) {
            $ticket->qr_svg = $ticketService->generateAdmissionQrSvg($ticket);
            $ticket->verification_url = $ticketService->getAdmissionQrPayload($ticket);
            return $ticket;
        });

        return view('public.confirmed', [
            'booking' => $booking,
            'tickets' => $ticketsWithQrs,
        ]);
    }
}

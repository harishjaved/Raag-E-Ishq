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

        // Ensure tickets are loaded with admission QR SVGs and download URLs
        $ticketsWithQrs = $booking->tickets->map(function ($ticket) use ($ticketService) {
            $ticket->qr_svg = $ticketService->generateAdmissionQrSvg($ticket);
            $ticket->verification_url = $ticketService->getAdmissionQrPayload($ticket);
            $ticket->download_url = \Illuminate\Support\Facades\URL::signedRoute('ticket.download', ['uuid' => $ticket->qr_uuid]);
            $ticket->qr_download_url = route('ticket.qr.download', ['uuid' => $ticket->qr_uuid]);
            $ticket->qr_data_uri = 'data:image/svg+xml;base64,' . base64_encode($ticket->qr_svg);
            return $ticket;
        });

        return view('public.confirmed', [
            'booking' => $booking,
            'tickets' => $ticketsWithQrs,
        ]);
    }
}

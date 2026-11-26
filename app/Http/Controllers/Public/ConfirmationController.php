<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfirmationController extends Controller
{
    public function show(Request $request, string $reference, TicketService $ticketService): View|RedirectResponse
    {
        $booking = Booking::with(['items.category', 'contact', 'tickets.category'])
            ->where('booking_reference', $reference)
            ->first();

        if (!$booking) {
            $restored = $ticketService->restoreConfirmedBookingFromToken(
                reference: $reference,
                encoded: (string) $request->query('p', ''),
                signature: (string) $request->query('s', '')
            );

            if ($restored !== null) {
                return view('public.confirmed', $restored);
            }

            return redirect()->route('event.show')->withErrors([
                'quantity' => "Booking reference '{$reference}' not found. Please place an enquiry below or contact support."
            ]);
        }

        return $this->renderConfirmedBooking($booking, $ticketService);
    }

    public function showShort(string $token, TicketService $ticketService): View|RedirectResponse
    {
        $restored = $ticketService->restoreConfirmedBookingFromShortToken($token);
        if ($restored === null) {
            return redirect()->route('event.show')->withErrors([
                'quantity' => 'Ticket pass link is invalid or corrupted. Please place an enquiry or request a resend below.',
            ]);
        }

        $reference = $restored['booking']->booking_reference;
        $booking = Booking::with(['items.category', 'contact', 'tickets.category'])
            ->where('booking_reference', $reference)
            ->first();

        if ($booking) {
            return $this->renderConfirmedBooking($booking, $ticketService);
        }

        return view('public.confirmed', $restored);
    }

    protected function renderConfirmedBooking(Booking $booking, TicketService $ticketService): View|RedirectResponse
    {
        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return redirect()->route('booking.payment', ['reference' => $booking->booking_reference]);
        }

        // Ensure tickets are loaded with admission QR SVGs and download URLs
        $ticketsWithQrs = $booking->tickets->map(function ($ticket) use ($ticketService) {
            $ticket->qr_svg = $ticketService->generateAdmissionQrSvg($ticket);
            $ticket->verification_url = $ticketService->getAdmissionQrPayload($ticket);
            $ticket->download_url = \Illuminate\Support\Facades\URL::signedRoute('ticket.download', ['uuid' => $ticket->qr_uuid]);
            $ticket->qr_download_url = route('ticket.qr.download', ['uuid' => $ticket->qr_uuid]);
            $ticket->og_qr_url = route('ticket.qr.png', ['uuid' => $ticket->qr_uuid]);
            $ticket->qr_data_uri = 'data:image/svg+xml;base64,' . base64_encode($ticket->qr_svg);
            return $ticket;
        });

        return view('public.confirmed', [
            'booking' => $booking,
            'tickets' => $ticketsWithQrs,
        ]);
    }
}

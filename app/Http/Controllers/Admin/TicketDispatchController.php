<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\RazorpayService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketDispatchController extends Controller
{
    public function index(Request $request, TicketService $ticketService): View
    {
        $filter = $request->query('filter', 'all');

        $query = Booking::with([
            'contact',
            'items.category',
            'tickets.category',
        ])->latest();

        if ($filter === 'needs_generation') {
            // Bookings that have no tickets yet
            $query->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_IN_PROCESS, Booking::STATUS_PAYMENT_PENDING])
                  ->whereDoesntHave('tickets');
        } elseif ($filter === 'dispatched') {
            $query->whereHas('tickets');
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhereHas('contact', function ($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->paginate(15);

        // Populate QR SVGs and WhatsApp dispatch URLs for all loaded tickets
        foreach ($bookings as $booking) {
            foreach ($booking->tickets as $ticket) {
                $ticket->qr_svg = $ticketService->generateAdmissionQrSvg($ticket, 180);
                $ticket->download_url = \Illuminate\Support\Facades\URL::signedRoute('ticket.download', ['uuid' => $ticket->qr_uuid]);
                $ticket->qr_download_url = route('ticket.qr.download', ['uuid' => $ticket->qr_uuid]);
            }
            if ($booking->tickets->isNotEmpty()) {
                $publicUrl = route('booking.confirmed', $booking->booking_reference);
                $waText = "Hello " . $booking->contact->full_name . "! Your admission passes for Joygivers Sagar Kinare (Booking Ref: " . $booking->booking_reference . ") are confirmed! View and download your unique QR entry passes here: " . $publicUrl;
                $phoneClean = preg_replace('/[^0-9]/', '', $booking->contact->phone);
                if (strlen($phoneClean) === 10) {
                    $phoneClean = '91' . $phoneClean;
                }
                $booking->whatsapp_dispatch_url = "https://wa.me/" . $phoneClean . "?text=" . urlencode($waText);
            }
        }

        $counts = [
            'total' => Booking::count(),
            'needs_generation' => Booking::whereDoesntHave('tickets')->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_IN_PROCESS, Booking::STATUS_PAYMENT_PENDING])->count(),
            'dispatched' => Booking::whereHas('tickets')->count(),
        ];

        return view('admin.dispatch.index', [
            'bookings' => $bookings,
            'filter' => $filter,
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    public function generate(int $id, RazorpayService $razorpayService, TicketService $ticketService): RedirectResponse
    {
        $booking = Booking::with(['items.category', 'contact'])->findOrFail($id);

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            $app = app(BookingController::class);
            $app->confirmPayment($id, $razorpayService);
        } else {
            if ($booking->tickets->isEmpty()) {
                $ticketService->issueTicketsForBooking($booking);
            }
        }

        return back()->with('success', "Unique QR admission tickets generated successfully for {$booking->booking_reference}!");
    }
}

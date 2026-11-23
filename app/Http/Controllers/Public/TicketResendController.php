<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Jobs\SendTicketEmailJob;
use App\Models\Booking;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketResendController extends Controller
{
    public function show(): View
    {
        return view('public.resend');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));

        $contacts = Contact::where('email', $email)->get();
        foreach ($contacts as $contact) {
            $confirmedBookings = $contact->bookings()
                ->where('status', Booking::STATUS_CONFIRMED)
                ->get();

            foreach ($confirmedBookings as $booking) {
                SendTicketEmailJob::dispatch($booking);
            }
        }

        return back()->with('status', 'If confirmed tickets match this email, a secure ticket link has been resent.');
    }
}

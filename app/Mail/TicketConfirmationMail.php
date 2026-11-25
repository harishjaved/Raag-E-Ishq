<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $passUrl;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->passUrl = app(\App\Services\TicketService::class)->buildSignedPassUrl($booking);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Tickets for Raag-E-Ishq ({$this->booking->booking_reference})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket_confirmation',
        );
    }
}

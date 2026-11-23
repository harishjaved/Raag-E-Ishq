<?php

namespace App\Jobs;

use App\Mail\TicketConfirmationMail;
use App\Models\Booking;
use App\Models\EmailDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTicketEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public Booking $booking;
    public ?EmailDelivery $deliveryRecord = null;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function handle(): void
    {
        $recipient = $this->booking->contact->email;

        $delivery = EmailDelivery::firstOrCreate(
            [
                'booking_id' => $this->booking->id,
                'recipient_email' => $recipient,
                'email_type' => 'BOOKING_CONFIRMATION',
            ],
            [
                'status' => EmailDelivery::STATUS_PENDING,
                'attempts' => 0,
            ]
        );

        $delivery->increment('attempts');

        try {
            Mail::to($recipient)->send(new TicketConfirmationMail($this->booking));

            $delivery->update([
                'status' => EmailDelivery::STATUS_SENT,
                'sent_at' => Carbon::now(),
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            $delivery->update([
                'status' => EmailDelivery::STATUS_FAILED,
                'error_message' => substr($e->getMessage(), 0, 1000),
            ]);

            // Re-throw so Laravel queue retry mechanism triggers without recreating or re-issuing tickets!
            throw $e;
        }
    }
}

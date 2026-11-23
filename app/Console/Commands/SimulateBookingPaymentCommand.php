<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\PaymentAttempt;
use App\Services\RazorpayService;
use App\Services\TicketService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SimulateBookingPaymentCommand extends Command
{
    protected $signature = 'booking:simulate-payment {reference : The booking reference e.g. SK-20261123-WG8U6}';
    protected $description = 'Simulate an incoming verified UPI payment webhook from Razorpay for testing';

    public function handle(RazorpayService $razorpayService, TicketService $ticketService): int
    {
        $reference = trim($this->argument('reference'));
        $booking = Booking::with(['items.category', 'contact'])->where('booking_reference', $reference)->first();

        if (!$booking) {
            $this->error("Booking reference '{$reference}' not found.");
            return 1;
        }

        if ($booking->status === Booking::STATUS_CONFIRMED) {
            $this->warn("Booking '{$reference}' is already CONFIRMED. Tickets are already issued.");
            $this->info("Admission URL: " . route('booking.confirmed', ['reference' => $reference]));
            return 0;
        }

        $providerEventId = 'evt_sim_' . Str::random(16);
        $paymentId = 'pay_sim_' . Str::random(14);
        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $paymentId,
                        'amount' => $booking->total_amount_paise,
                        'currency' => 'INR',
                        'status' => 'captured',
                        'notes' => ['booking_reference' => $booking->booking_reference],
                        'method' => 'upi',
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $secret = config('services.razorpay.webhook_secret') ?: 'whsec_test_dummy';
        config(['services.razorpay.webhook_secret' => $secret]);
        $signature = hash_hmac('sha256', $rawBody, $secret);

        $result = $razorpayService->handleWebhook($providerEventId, 'payment.captured', $payload, $rawBody, $signature);

        if ($result['status'] === 'error') {
            $this->error("Failed to process payment webhook: " . ($result['message'] ?? 'Unknown error'));
            return 1;
        }

        $booking->refresh();
        $tickets = $booking->tickets;

        $this->info("=================================================");
        $this->info(" [SUCCESS] Payment Verified for {$reference}");
        $this->info("=================================================");
        $this->line("Amount Paid: ₹" . number_format($booking->total_amount_paise / 100, 2));
        $this->line("Payment ID:  {$paymentId}");
        $this->line("Status:      {$booking->status}");
        $this->line("Tickets Issued: " . $tickets->count());
        $this->newLine();
        $this->info("View Confirmed Tickets with Admission QRs at:");
        $this->comment(route('booking.confirmed', ['reference' => $reference]));

        return 0;
    }
}

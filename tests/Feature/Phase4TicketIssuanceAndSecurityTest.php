<?php

namespace Tests\Feature;

use App\Jobs\SendTicketEmailJob;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Services\InventoryService;
use App\Services\TicketService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class Phase4TicketIssuanceAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TicketCategory $tier1;
    protected InventoryService $inventoryService;
    protected TicketService $ticketService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->event = Event::where('slug', 'sagar-kinare-2026')->first();
        $this->tier1 = $this->event->categories()->where('name', 'Tier 1')->first();
        $this->inventoryService = app(InventoryService::class);
        $this->ticketService = app(TicketService::class);
    }

    protected function createConfirmedBooking(int $quantity = 2): Booking
    {
        $booking = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Rahul Bose',
            'email' => 'rahul@example.com',
            'phone' => '9876500000',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => $quantity,
            'privacy_consent' => true,
        ]);

        $booking->update(['status' => Booking::STATUS_CONFIRMED]);
        $this->ticketService->issueTicketsForBooking($booking);

        return $booking;
    }

    public function test_tickets_issued_one_per_entitled_admission_with_cryptographic_nonce(): void
    {
        $booking = $this->createConfirmedBooking(quantity: 3);

        $tickets = $booking->tickets;
        $this->assertCount(3, $tickets);

        $nonces = $tickets->pluck('qr_nonce')->unique();
        $this->assertCount(3, $nonces, 'Each ticket MUST have a unique high-entropy nonce.');

        $uuids = $tickets->pluck('qr_uuid')->unique();
        $this->assertCount(3, $uuids, 'Each ticket MUST have a distinct public UUID.');

        foreach ($tickets as $ticket) {
            $this->assertEquals(Ticket::STATUS_ISSUED, $ticket->status);
            $sig = $this->ticketService->generateHmacSignature($ticket);
            $this->assertNotEmpty($sig);

            // Verification check
            $verify = $this->ticketService->verifyAdmissionToken($ticket->qr_uuid, $sig);
            $this->assertTrue($verify['valid']);
            $this->assertEquals('VALID', $verify['status']);
        }
    }

    public function test_counterfeit_or_tampered_hmac_signature_fails_verification(): void
    {
        $booking = $this->createConfirmedBooking(quantity: 1);
        $ticket = $booking->tickets->first();

        // Forged signature
        $verify = $this->ticketService->verifyAdmissionToken($ticket->qr_uuid, 'forged_counterfeit_signature_123');
        $this->assertFalse($verify['valid']);
        $this->assertEquals('INVALID_SIGNATURE', $verify['status']);
    }

    public function test_ticket_download_enforces_signed_url_and_prevents_unauthorized_access(): void
    {
        $booking = $this->createConfirmedBooking(quantity: 1);
        $ticket = $booking->tickets->first();

        // Unsigned request MUST be rejected with 403
        $unsignedUrl = "/tickets/download/{$ticket->qr_uuid}";
        $response = $this->get($unsignedUrl);
        $response->assertStatus(403);

        // Valid signed URL must succeed
        $signedUrl = $this->ticketService->getDownloadSignedUrl($ticket);
        $validResponse = $this->get($signedUrl);
        $validResponse->assertStatus(200);
        $validResponse->assertSee($ticket->attendee_name);
        $validResponse->assertSee($ticket->ticket_number);

        // Crucial security check: must NOT leak customer phone or billing payment details
        $validResponse->assertDontSee($booking->contact->phone);
        $validResponse->assertDontSee('total_amount_paise');
    }

    public function test_voided_ticket_is_rejected_at_verification(): void
    {
        $booking = $this->createConfirmedBooking(quantity: 1);
        $ticket = $booking->tickets->first();
        $sig = $this->ticketService->generateHmacSignature($ticket);

        // Void the ticket
        $this->ticketService->voidTicket($ticket, 'Chargeback requested by cardholder');

        $verify = $this->ticketService->verifyAdmissionToken($ticket->qr_uuid, $sig);
        $this->assertFalse($verify['valid']);
        $this->assertEquals('VOID', $verify['status']);
        $this->assertStringContainsString('Chargeback', $verify['message']);
    }

    public function test_email_failure_is_retried_without_regenerating_tickets(): void
    {
        $booking = $this->createConfirmedBooking(quantity: 2);
        $originalTicketIds = $booking->tickets->pluck('id')->all();
        $originalNonces = $booking->tickets->pluck('qr_nonce')->all();

        // Mock mail failure
        Mail::shouldReceive('to')->andThrow(new Exception('SMTP connection timed out'));

        // Execute job
        try {
            $job = new SendTicketEmailJob($booking);
            $job->handle();
            $this->fail('Expected mail exception to be thrown');
        } catch (Exception $e) {
            $this->assertEquals('SMTP connection timed out', $e->getMessage());
        }

        // Verify tickets remain completely intact and untouched!
        $booking->refresh();
        $currentTicketIds = $booking->tickets->pluck('id')->all();
        $currentNonces = $booking->tickets->pluck('qr_nonce')->all();

        $this->assertEquals($originalTicketIds, $currentTicketIds);
        $this->assertEquals($originalNonces, $currentNonces);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\GatewayWebhookEvent;
use App\Models\InventoryHold;
use App\Models\PaymentAttempt;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Services\InventoryService;
use App\Services\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class Phase3RazorpayWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TicketCategory $tier1;
    protected InventoryService $inventoryService;
    protected string $webhookSecret;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->event = Event::where('slug', 'sagar-kinare-2026')->first();
        $this->tier1 = $this->event->categories()->where('name', 'Tier 1')->first();
        $this->inventoryService = app(InventoryService::class);
        $this->webhookSecret = 'test_webhook_secret_key_12345';
        config(['services.razorpay.webhook_secret' => $this->webhookSecret]);
    }

    protected function createTestBooking(int $quantity = 2): Booking
    {
        return $this->inventoryService->createBookingWithHold([
            'full_name' => 'Meera Nair',
            'email' => 'meera@example.com',
            'phone' => '9847012345',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => $quantity,
            'privacy_consent' => true,
        ]);
    }

    protected function buildWebhookPayload(string $eventId, Booking $booking, int $amountPaise, string $currency = 'INR'): array
    {
        return [
            'entity' => 'event',
            'account_id' => 'acc_test_joygivers',
            'event' => 'payment.captured',
            'event_id' => $eventId,
            'contains' => ['payment'],
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_' . substr(md5($eventId), 0, 14),
                        'entity' => 'payment',
                        'amount' => $amountPaise,
                        'currency' => $currency,
                        'status' => 'captured',
                        'order_id' => 'order_test_123',
                        'method' => 'upi',
                        'description' => 'Joygivers Sagar Kinare',
                        'notes' => [
                            'booking_reference' => $booking->booking_reference,
                        ],
                    ],
                ],
            ],
            'created_at' => time(),
        ];
    }

    public function test_forged_webhook_signature_is_rejected_with_400(): void
    {
        $booking = $this->createTestBooking();
        $payload = $this->buildWebhookPayload('evt_forged_001', $booking, $booking->total_amount_paise);
        $rawBody = json_encode($payload);

        $response = $this->postJson('/webhooks/razorpay', $payload, [
            'X-Razorpay-Signature' => 'forged_invalid_signature',
            'X-Razorpay-Event-Id' => 'evt_forged_001',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Invalid webhook signature']);

        // Booking MUST remain payment pending
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_PAYMENT_PENDING, $booking->status);
        $this->assertEquals(0, Ticket::where('booking_id', $booking->id)->count());
    }

    public function test_valid_webhook_captures_payment_and_issues_tickets(): void
    {
        $booking = $this->createTestBooking(quantity: 2);
        $payload = $this->buildWebhookPayload('evt_valid_002', $booking, $booking->total_amount_paise);
        $rawBody = json_encode($payload);
        $validSignature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->call(
            'POST',
            '/webhooks/razorpay',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAZORPAY_SIGNATURE' => $validSignature,
                'HTTP_X_RAZORPAY_EVENT_ID' => 'evt_valid_002',
            ],
            content: $rawBody
        );

        $response->assertStatus(200);

        // Booking must be CONFIRMED
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);
        $this->assertNotNull($booking->confirmed_at);

        // Inventory hold must be CONVERTED
        $hold = $booking->inventoryHolds()->first();
        $this->assertEquals(InventoryHold::STATUS_CONVERTED, $hold->status);

        // Exactly 2 tickets must be issued (one per admission)
        $tickets = Ticket::where('booking_id', $booking->id)->get();
        $this->assertCount(2, $tickets);

        foreach ($tickets as $ticket) {
            $this->assertEquals(Ticket::STATUS_ISSUED, $ticket->status);
            $this->assertNotEmpty($ticket->qr_uuid);
            $this->assertNotEmpty($ticket->qr_nonce);
            $this->assertStringStartsWith('TKT-', $ticket->ticket_number);
        }

        // Webhook event recorded in deduplication table as PROCESSED
        $webhookEvent = GatewayWebhookEvent::where('provider_event_id', 'evt_valid_002')->first();
        $this->assertNotNull($webhookEvent);
        $this->assertEquals(GatewayWebhookEvent::STATUS_PROCESSED, $webhookEvent->processing_status);
    }

    public function test_duplicate_webhook_is_idempotent_and_does_not_duplicate_tickets(): void
    {
        $booking = $this->createTestBooking(quantity: 1);
        $payload = $this->buildWebhookPayload('evt_dup_003', $booking, $booking->total_amount_paise);
        $rawBody = json_encode($payload);
        $validSignature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        // First delivery
        $res1 = $this->call(
            'POST',
            '/webhooks/razorpay',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAZORPAY_SIGNATURE' => $validSignature,
                'HTTP_X_RAZORPAY_EVENT_ID' => 'evt_dup_003',
            ],
            content: $rawBody
        );
        $res1->assertStatus(200);
        $this->assertEquals(1, Ticket::where('booking_id', $booking->id)->count());

        // Second duplicate delivery
        $res2 = $this->call(
            'POST',
            '/webhooks/razorpay',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAZORPAY_SIGNATURE' => $validSignature,
                'HTTP_X_RAZORPAY_EVENT_ID' => 'evt_dup_003',
            ],
            content: $rawBody
        );
        $res2->assertStatus(200);
        $res2->assertJson(['status' => 'duplicate']);

        // Ticket count MUST still be exactly 1, not duplicated!
        $this->assertEquals(1, Ticket::where('booking_id', $booking->id)->count());
    }

    public function test_amount_mismatch_routes_to_reconciliation(): void
    {
        $booking = $this->createTestBooking(quantity: 1); // Expected 250000 paise (₹2,500)
        // Payload with tampered/incorrect amount (e.g. 100000 paise)
        $payload = $this->buildWebhookPayload('evt_mismatch_004', $booking, 100000);
        $rawBody = json_encode($payload);
        $validSignature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->call(
            'POST',
            '/webhooks/razorpay',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAZORPAY_SIGNATURE' => $validSignature,
                'HTTP_X_RAZORPAY_EVENT_ID' => 'evt_mismatch_004',
            ],
            content: $rawBody
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'reconciliation_required']);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_RECONCILIATION_REQUIRED, $booking->status);
        $this->assertEquals(0, Ticket::where('booking_id', $booking->id)->count());
    }

    public function test_late_payment_after_hold_expiry_when_category_exhausted_routes_to_reconciliation(): void
    {
        // 1. Create booking with hold
        $booking = $this->createTestBooking(quantity: 1);

        // 2. Mark hold expired and release it
        $booking->update([
            'status' => Booking::STATUS_HOLD_EXPIRED,
            'hold_expires_at' => Carbon::now()->subMinutes(15),
        ]);
        $booking->inventoryHolds()->update(['status' => InventoryHold::STATUS_RELEASED]);

        // 3. Exhaust category capacity so 0 tickets remain
        $this->tier1->update(['capacity' => 0]);

        // 4. Late payment arrives via webhook
        $payload = $this->buildWebhookPayload('evt_late_005', $booking, $booking->total_amount_paise);
        $rawBody = json_encode($payload);
        $validSignature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->call(
            'POST',
            '/webhooks/razorpay',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAZORPAY_SIGNATURE' => $validSignature,
                'HTTP_X_RAZORPAY_EVENT_ID' => 'evt_late_005',
            ],
            content: $rawBody
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'reconciliation_required']);

        // Must NOT create a 451st admission!
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_RECONCILIATION_REQUIRED, $booking->status);
        $this->assertEquals(0, Ticket::where('booking_id', $booking->id)->count());
    }
}

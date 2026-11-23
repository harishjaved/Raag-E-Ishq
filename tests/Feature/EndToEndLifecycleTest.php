<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Checkin;
use App\Models\Contact;
use App\Models\Event;
use App\Models\GatewayWebhookEvent;
use App\Models\InventoryHold;
use App\Models\PaymentAttempt;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EndToEndLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TicketCategory $tier1;
    protected string $webhookSecret;
    protected User $gateStaff;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->event = Event::where('slug', 'sagar-kinare-2026')->first();
        $this->tier1 = $this->event->categories()->where('name', 'Tier 1')->first();
        $this->gateStaff = User::where('email', 'gate@joygivers.org')->first();
        $this->superAdmin = User::where('email', 'superadmin@joygivers.org')->first();

        $this->webhookSecret = 'e2e_test_razorpay_secret_key';
        config(['services.razorpay.webhook_secret' => $this->webhookSecret]);
    }

    /**
     * Definition of Done E2E Automated Integration Test:
     * Guest link -> Registration -> CRM -> Unique payment QR -> Verified payment ->
     * Admission QR per ticket -> Emailed / Downloadable ticket -> Phone scan ->
     * Exactly one recorded entry -> Accurate dashboard update.
     */
    public function test_complete_end_to_end_audience_to_gate_lifecycle(): void
    {
        Queue::fake();

        // STEP 1: Event Discovery & Campaign Tracking Link
        $discoveryResponse = $this->get('/?utm_source=whatsapp&utm_medium=broadcast&utm_campaign=vip_preview');
        $discoveryResponse->assertStatus(200);
        $discoveryResponse->assertSee('Sagar Kinare');
        $discoveryResponse->assertSee('Tier 1');

        // STEP 2: Registration & CRM Contact Creation with Inventory Hold
        $registrationPayload = [
            'full_name' => 'Dr. Ananya Sen',
            'email' => 'ananya.sen@example.com',
            'phone' => '9847998877',
            'country_code' => '+91',
            'ticket_category_id' => $this->tier1->id, // ₹2,500
            'quantity' => 2, // 2 tickets
            'privacy_consent' => '1',
            'marketing_opt_in' => '1',
            'utm_source' => 'whatsapp',
            'utm_medium' => 'broadcast',
            'utm_campaign' => 'vip_preview',
        ];

        $regResponse = $this->post('/register', $registrationPayload);

        // Verify CRM Contact & Booking
        $contact = Contact::where('email', 'ananya.sen@example.com')->first();
        $this->assertNotNull($contact);
        $this->assertEquals('Dr. Ananya Sen', $contact->full_name);
        $this->assertEquals('whatsapp', $contact->utm_source);

        $booking = Booking::where('contact_id', $contact->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals(Booking::STATUS_PAYMENT_PENDING, $booking->status);
        $this->assertEquals(500000, $booking->total_amount_paise); // 2 * 250,000 paise (Server calculated)
        $regResponse->assertRedirect(route('booking.payment', ['reference' => $booking->booking_reference]));

        // STEP 3: Payment Page Presentation (UPI QR & Mobile Intent)
        $paymentPageResponse = $this->get(route('booking.payment', ['reference' => $booking->booking_reference]));
        $paymentPageResponse->assertStatus(200);
        $paymentPageResponse->assertSee($booking->booking_reference);
        $paymentPageResponse->assertSee('₹5,000.00');
        $paymentPageResponse->assertSee('UPI Payment QR');
        $paymentPageResponse->assertSee('Open UPI App to Pay');

        // Check polling status is initially pending
        $statusPoll1 = $this->getJson(route('booking.status', ['reference' => $booking->booking_reference]));
        $statusPoll1->assertStatus(200);
        $statusPoll1->assertJson(['confirmed' => false]);

        // STEP 4: Razorpay Raw Webhook Delivery (Signature Verified)
        $webhookEventId = 'evt_e2e_live_capture_001';
        $webhookPayload = [
            'entity' => 'event',
            'account_id' => 'acc_joygivers_prod',
            'event' => 'payment.captured',
            'event_id' => $webhookEventId,
            'contains' => ['payment'],
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_rzp_e2e_999888',
                        'entity' => 'payment',
                        'amount' => 500000, // Exact 500,000 paise
                        'currency' => 'INR',
                        'status' => 'captured',
                        'method' => 'upi',
                        'notes' => [
                            'booking_reference' => $booking->booking_reference,
                        ],
                    ],
                ],
            ],
            'created_at' => time(),
        ];

        $rawBody = json_encode($webhookPayload);
        $validSignature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $webhookResponse = $this->call(
            'POST',
            '/webhooks/razorpay',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAZORPAY_SIGNATURE' => $validSignature,
                'HTTP_X_RAZORPAY_EVENT_ID' => $webhookEventId,
            ],
            content: $rawBody
        );

        $webhookResponse->assertStatus(200);

        // STEP 5: Verification of Booking State & 1 Ticket Per Entitled Admission
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);
        $this->assertNotNull($booking->confirmed_at);

        // Holds must be converted
        $hold = $booking->inventoryHolds()->first();
        $this->assertEquals(InventoryHold::STATUS_CONVERTED, $hold->status);

        // Exactly 2 distinct tickets issued
        $tickets = $booking->tickets;
        $this->assertCount(2, $tickets);
        $this->assertEquals('Dr. Ananya Sen', $tickets[0]->attendee_name);
        $this->assertEquals('Dr. Ananya Sen (Guest #2)', $tickets[1]->attendee_name);

        // Transactional email queued
        Queue::assertPushed(\App\Jobs\SendTicketEmailJob::class);

        // Polling status now confirms payment
        $statusPoll2 = $this->getJson(route('booking.status', ['reference' => $booking->booking_reference]));
        $statusPoll2->assertStatus(200);
        $statusPoll2->assertJson(['confirmed' => true]);

        // STEP 6: Confirmation View & Downloadable Ticket
        $confirmView = $this->get(route('booking.confirmed', ['reference' => $booking->booking_reference]));
        $confirmView->assertStatus(200);
        $confirmView->assertSee('Payment Verified');
        $confirmView->assertSee($tickets[0]->ticket_number);
        $confirmView->assertSee($tickets[1]->ticket_number);

        // Download signed ticket pass
        $ticketService = app(TicketService::class);
        $signedDownloadUrl = $ticketService->getDownloadSignedUrl($tickets[0]);
        $downloadResponse = $this->get($signedDownloadUrl);
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertSee($tickets[0]->ticket_number);
        $downloadResponse->assertDontSee($contact->phone); // Zero PII leakage

        // STEP 7: Mobile Gate Scanner & Atomic Single Check-in
        $ticket1 = $tickets[0];
        $sig1 = $ticketService->generateHmacSignature($ticket1);

        // Scan Ticket 1 at Gate 1
        $gateScanResponse1 = $this->actingAs($this->gateStaff)->postJson('/staff/scan/verify', [
            'uuid' => $ticket1->qr_uuid,
            'signature' => $sig1,
            'gate' => 'GATE_MAIN',
        ]);

        $gateScanResponse1->assertStatus(200);
        $gateScanResponse1->assertJson([
            'result' => 'GREEN_VALID',
            'attendee_name' => 'Dr. Ananya Sen',
            'ticket_number' => $ticket1->ticket_number,
        ]);

        // Verify Checkin record
        $this->assertEquals(1, Checkin::where('ticket_id', $ticket1->id)->count());
        $ticket1->refresh();
        $this->assertEquals(Ticket::STATUS_CHECKED_IN, $ticket1->status);

        // STEP 8: Duplicate Scan Rejection (Orange Warning)
        $gateScanResponse2 = $this->actingAs($this->gateStaff)->postJson('/staff/scan/verify', [
            'uuid' => $ticket1->qr_uuid,
            'signature' => $sig1,
            'gate' => 'GATE_MAIN',
        ]);

        $gateScanResponse2->assertStatus(409);
        $gateScanResponse2->assertJson([
            'result' => 'ORANGE_ALREADY_USED',
        ]);
        // Database still contains strictly 1 check-in
        $this->assertEquals(1, Checkin::where('ticket_id', $ticket1->id)->count());

        // STEP 9: Back Office Dashboard Verification
        $analytics = app(AnalyticsService::class);
        $metrics = $analytics->getDashboardMetrics();

        $this->assertEquals(500000, $metrics['gross_collections_paise']);
        $this->assertEquals('5,000.00', $metrics['gross_collections_rupees']);
        $this->assertEquals(2, $metrics['paid_tickets_issued']);
        $this->assertEquals(1, $metrics['total_checked_in']);
        $this->assertEquals(498, $metrics['global_remaining']); // 500 - 2 = 498
        $this->assertEquals(50.0, $metrics['showup_rate']); // 1 of 2 checked in = 50%
    }
}

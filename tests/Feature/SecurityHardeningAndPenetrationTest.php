<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityHardeningAndPenetrationTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TicketCategory $tier1;
    protected User $gateStaff;
    protected TicketService $ticketService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->event = Event::where('slug', 'sagar-kinare-2026')->first();
        $this->tier1 = $this->event->categories()->where('name', 'Tier 1')->first();
        $this->gateStaff = User::where('role', User::ROLE_GATE_STAFF)->first();
        $this->ticketService = app(TicketService::class);
    }

    public function test_security_headers_are_present_on_http_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString('camera=(self)', $response->headers->get('Permissions-Policy'));
    }

    public function test_xss_injection_payload_is_sanitized_during_registration(): void
    {
        $xssName = '<script>alert("hacked")</script>Vikram <img src=x onerror=alert(1)>Patel';
        $xssUtm = '<script>document.cookie</script>social';

        $response = $this->post('/register', [
            'full_name' => $xssName,
            'email' => 'vikram.security@example.com',
            'phone' => '9820011223',
            'country_code' => '+91',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => '1',
            'marketing_opt_in' => '0',
            'attending' => 'yes',
            'utm_source' => $xssUtm,
        ]);

        $response->assertStatus(302);

        $booking = Booking::with('contact')->latest('id')->first();
        $this->assertNotNull($booking);

        // Verify HTML tags and script contents were stripped
        $this->assertStringNotContainsString('<script>', $booking->contact->full_name);
        $this->assertStringNotContainsString('<img', $booking->contact->full_name);
        $this->assertStringNotContainsString('alert', $booking->contact->full_name);
        $this->assertEquals('Vikram Patel', $booking->contact->full_name);
        $this->assertEquals('social', $booking->contact->utm_source);
    }

    public function test_sql_injection_payloads_in_inputs_are_safely_parameterized_and_isolated(): void
    {
        // Vector 1: SQL injection in email failing format validation cleanly
        $sqliEmailResponse = $this->post('/register', [
            'full_name' => "Admin'; DROP TABLE bookings; --",
            'email' => "' OR '1'='1",
            'phone' => '9820011224',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => '1',
        ]);
        $sqliEmailResponse->assertSessionHasErrors(['email']);

        // Vector 2: SQL injection in full_name safely parameterized by Eloquent PDO
        $sqliPayload = "Robert'); DROP TABLE bookings;--";
        $response = $this->post('/register', [
            'full_name' => $sqliPayload,
            'email' => 'robert.tables@example.com',
            'phone' => '9820011225',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => '1',
        ]);

        $response->assertStatus(302);

        // Verify database table was not dropped and data was inserted literally
        $contact = Contact::where('email', 'robert.tables@example.com')->first();
        $this->assertNotNull($contact);
        $this->assertEquals($sqliPayload, $contact->full_name);

        $this->assertDatabaseHas('bookings', [
            'contact_id' => $contact->id,
        ]);
    }

    public function test_rate_limiting_protects_login_endpoint_from_brute_force(): void
    {
        // 5 requests allowed per minute
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'admin@joygivers.org',
                'password' => 'wrong-password-' . $i,
            ]);
            $this->assertEquals(302, $response->getStatusCode());
        }

        // 6th request from same IP must be rate limited with HTTP 429
        $throttledResponse = $this->post('/login', [
            'email' => 'admin@joygivers.org',
            'password' => 'wrong-password-6',
        ]);

        $throttledResponse->assertStatus(429);
    }

    public function test_price_and_amount_tampering_is_prevented(): void
    {
        // Malicious client tries to inject price parameters of ₹1
        $response = $this->post('/register', [
            'full_name' => 'Tamper Test',
            'email' => 'tamper@example.com',
            'phone' => '9820011226',
            'ticket_category_id' => $this->tier1->id, // Tier 1 is ₹2,500 (250000 paise)
            'quantity' => 2,
            'privacy_consent' => '1',
            // Injected tampering fields
            'unit_price' => 100,
            'total_amount' => 200,
            'price_in_paise' => 100,
            'amount' => 1,
        ]);

        $response->assertStatus(302);

        $booking = Booking::with('items')->latest('id')->first();
        $this->assertNotNull($booking);

        // Total must be calculated strictly server-side: 2 x 250,000 = 500,000 paise (₹5,000)
        $this->assertEquals(500000, $booking->total_amount_paise);
        $this->assertEquals(250000, $booking->items->first()->unit_price_paise);
    }

    public function test_simulate_payment_is_forbidden_in_production(): void
    {
        $contact = Contact::create([
            'full_name' => 'Sec Test Contact',
            'email' => 'sectest@example.com',
            'phone' => '9820011227',
        ]);

        $booking = Booking::create([
            'event_id' => $this->event->id,
            'contact_id' => $contact->id,
            'booking_reference' => 'SK-SEC-TEST01',
            'total_amount_paise' => 250000,
            'currency' => 'INR',
            'status' => Booking::STATUS_PAYMENT_PENDING,
            'is_attending' => true,
        ]);

        // Scenario 1: APP_ENV = production
        config(['app.env' => 'production']);
        config(['services.razorpay.live_payments_enabled' => false]);

        $response = $this->post("/booking/{$booking->booking_reference}/simulate-payment");
        $response->assertStatus(403);

        // Scenario 2: LIVE_PAYMENTS_ENABLED = true
        config(['app.env' => 'local']);
        config(['services.razorpay.live_payments_enabled' => true]);

        $response2 = $this->post("/booking/{$booking->booking_reference}/simulate-payment");
        $response2->assertStatus(403);
    }

    public function test_razorpay_webhook_forged_signature_is_rejected(): void
    {
        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_forged_999',
                        'amount' => 250000,
                        'currency' => 'INR',
                        'status' => 'captured',
                        'notes' => ['booking_reference' => 'SK-FAKE-REF'],
                    ],
                ],
            ],
        ];

        $response = $this->postJson(
            '/webhooks/razorpay',
            $payload,
            [
                'X-Razorpay-Signature' => 'forged_fake_hmac_signature_attempt',
                'Content-Type' => 'application/json',
            ]
        );

        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Invalid webhook signature',
        ]);
    }

    protected function createValidTicket(string $name = 'Valid Attendee', string $email = 'attendee@example.com', string $phone = '9820011228'): Ticket
    {
        $inventoryService = app(InventoryService::class);
        $booking = $inventoryService->createBookingWithHold([
            'full_name' => $name,
            'email' => $email,
            'phone' => $phone,
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => true,
        ]);

        $booking->update(['status' => Booking::STATUS_CONFIRMED]);
        $tickets = $this->ticketService->issueTicketsForBooking($booking);

        return $tickets->first();
    }

    public function test_tampered_signed_ticket_download_url_is_rejected(): void
    {
        $ticket = $this->createValidTicket('Valid Attendee', 'attendee@example.com', '9820011228');

        // Generate legitimate signed URL
        $validSignedUrl = URL::signedRoute('ticket.download', ['uuid' => $ticket->qr_uuid]);

        // Tamper with URL (altering query signature)
        $tamperedUrl = $validSignedUrl . '&tampered=1';

        $response = $this->get($tamperedUrl);
        $response->assertStatus(403);
    }

    public function test_forged_qr_hmac_is_rejected_at_gate_scanner(): void
    {
        $ticket = $this->createValidTicket('Counterfeit Target', 'target@example.com', '9820011229');

        // Try scanning with forged signature
        $response = $this->actingAs($this->gateStaff)->postJson('/staff/scan/verify', [
            'uuid' => $ticket->qr_uuid,
            'signature' => 'forged_fake_hmac_signature',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'result' => 'RED_INVALID',
        ]);
    }
}

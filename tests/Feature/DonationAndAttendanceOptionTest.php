<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\InventoryService;
use App\Services\RazorpayService;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DonationAndAttendanceOptionTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TicketCategory $tier1;
    protected InventoryService $inventoryService;
    protected AnalyticsService $analyticsService;
    protected RazorpayService $razorpayService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->event = Event::where('slug', 'sagar-kinare-2026')->first();
        $this->tier1 = $this->event->categories()->where('name', 'Gold')->first();
        $this->inventoryService = app(InventoryService::class);
        $this->analyticsService = app(AnalyticsService::class);
        $this->razorpayService = app(RazorpayService::class);
    }

    public function test_booking_with_attending_no_does_not_reduce_available_seats(): void
    {
        $initialAvailable = $this->inventoryService->getAvailableCapacity($this->tier1);
        $initialGlobal = $this->inventoryService->getGlobalRemainingCapacity($this->event);

        $response = $this->post('/register', [
            'full_name' => 'Dr. K. Raman',
            'email' => 'raman@supporter.org',
            'phone' => '9847111222',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 5, // 5 x ₹2,500 = ₹12,500 donation
            'attending' => 'no', // Non-attending supporter
            'privacy_consent' => true,
        ]);

        $response->assertRedirect();

        $booking = Booking::where('contact_id', function ($q) {
            $q->select('id')->from('contacts')->where('email', 'raman@supporter.org');
        })->first();

        $this->assertNotNull($booking);
        $this->assertFalse($booking->is_attending);
        $this->assertTrue($booking->is_donation);
        $this->assertEquals(1250000, $booking->total_amount_paise);

        // Core business rule: Because they are NOT attending, the seats MUST NOT be deducted!
        $this->assertEquals($initialAvailable, $this->inventoryService->getAvailableCapacity($this->tier1));
        $this->assertEquals($initialGlobal, $this->inventoryService->getGlobalRemainingCapacity($this->event));
    }

    public function test_booking_with_attending_yes_reduces_available_seats(): void
    {
        $initialAvailable = $this->inventoryService->getAvailableCapacity($this->tier1);

        $response = $this->post('/register', [
            'full_name' => 'Meera Nambiar',
            'email' => 'meera@attendee.org',
            'phone' => '9847333444',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 2,
            'attending' => 'yes',
            'privacy_consent' => true,
        ]);

        $response->assertRedirect();

        $booking = Booking::where('contact_id', function ($q) {
            $q->select('id')->from('contacts')->where('email', 'meera@attendee.org');
        })->first();

        $this->assertTrue($booking->is_attending);
        $this->assertFalse($booking->is_donation);

        // Attending booking MUST reduce available seats by 2
        $this->assertEquals($initialAvailable - 2, $this->inventoryService->getAvailableCapacity($this->tier1));
    }

    public function test_donation_payment_captured_and_reflected_in_admin_analytics(): void
    {
        // 1. Create non-attending booking
        $booking = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Kochi Foundation',
            'email' => 'contact@kochifoundation.org',
            'phone' => '9847555888',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 4, // ₹10,000
            'attending' => 'no',
            'privacy_consent' => true,
        ]);

        // 2. Simulate successful webhook
        $providerEventId = 'evt_don_' . Str::random(16);
        $paymentId = 'pay_don_' . Str::random(14);
        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $paymentId,
                        'amount' => 1000000,
                        'currency' => 'INR',
                        'status' => 'captured',
                        'notes' => ['booking_reference' => $booking->booking_reference],
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $secret = config('services.razorpay.webhook_secret');
        $signature = hash_hmac('sha256', $rawBody, $secret);

        $result = $this->razorpayService->handleWebhook(
            $providerEventId,
            'payment.captured',
            $payload,
            $rawBody,
            $signature
        );

        $this->assertEquals('confirmed', $result['status']);
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);

        // 3. Verify Admin Dashboard metrics distinguish donations
        $metrics = $this->analyticsService->getDashboardMetrics();
        $this->assertEquals(1000000, $metrics['donation_collections_paise']);
        $this->assertEquals('10,000.00', $metrics['donation_collections_rupees']);
        $this->assertEquals(1, $metrics['donation_bookings_count']);
    }

    public function test_confirmation_page_displays_official_supporter_receipt_when_not_attending(): void
    {
        $booking = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Suresh Menon',
            'email' => 'suresh@donor.com',
            'phone' => '9847999000',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 2,
            'attending' => 'no',
            'privacy_consent' => true,
        ]);
        $booking->update(['status' => Booking::STATUS_CONFIRMED]);

        $response = $this->get("/booking/{$booking->booking_reference}/confirmed");
        $response->assertStatus(200);
        $response->assertSee('Payment Verified &amp; Contribution Acknowledged', false);
        $response->assertSee('Non-Attending Contribution');
        $response->assertSee('Official Supporter Receipt');
        $response->assertSee('Suresh Menon');
        // Crucial test: Ensure admission QR is NOT rendered for non-attending donors
        $response->assertDontSee('Admission QR');
    }
}

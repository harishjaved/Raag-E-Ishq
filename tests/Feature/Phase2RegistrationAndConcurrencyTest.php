<?php

namespace Tests\Feature;

use App\Exceptions\OutOfStockException;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\Event;
use App\Models\InventoryHold;
use App\Models\TicketCategory;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class Phase2RegistrationAndConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TicketCategory $tier1;
    protected TicketCategory $tier2;
    protected TicketCategory $tier3;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->event = Event::where('slug', 'sagar-kinare-2026')->first();
        $this->tier1 = $this->event->categories()->where('name', 'Tier 1')->first();
        $this->tier2 = $this->event->categories()->where('name', 'Tier 2')->first();
        $this->tier3 = $this->event->categories()->where('name', 'Tier 3')->first();
    }

    public function test_public_event_page_renders_with_live_availability(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Sagar Kinare');
        $response->assertSee('Chavara Hall');
        $response->assertSee('Tier 1');
        $response->assertSee('Tier 2');
        $response->assertSee('Tier 3');
        $response->assertSee('₹2,500.00');
        $response->assertSee('₹1,500.00');
        $response->assertSee('₹1,000.00');
    }

    public function test_guest_can_register_and_create_booking_with_hold(): void
    {
        $payload = [
            'full_name' => 'Kavita Menon',
            'email' => 'kavita@example.com',
            'phone' => '9876543210',
            'country_code' => '+91',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 2,
            'privacy_consent' => '1',
            'marketing_opt_in' => '1',
            'utm_source' => 'whatsapp',
            'utm_campaign' => 'early_bird',
        ];

        $response = $this->post('/register', $payload);

        // Should redirect to payment page
        $booking = Booking::whereHas('contact', function ($q) {
            $q->where('email', 'kavita@example.com');
        })->first();

        $this->assertNotNull($booking);
        $response->assertRedirect(route('booking.payment', ['reference' => $booking->booking_reference]));

        // Verify Contact
        $contact = $booking->contact;
        $this->assertEquals('Kavita Menon', $contact->full_name);
        $this->assertEquals('9876543210', $contact->phone);
        $this->assertEquals('whatsapp', $contact->utm_source);
        $this->assertTrue($contact->marketing_opt_in);

        // Verify Server-side exact amount computation in paise: 2 * 250,000 = 500,000 paise (₹5,000)
        $this->assertEquals(500000, $booking->total_amount_paise);
        $this->assertEquals(Booking::STATUS_PAYMENT_PENDING, $booking->status);
        $this->assertNotNull($booking->hold_expires_at);

        // Verify Booking Item
        $this->assertCount(1, $booking->items);
        $item = $booking->items->first();
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals(250000, $item->unit_price_paise);
        $this->assertEquals(500000, $item->subtotal_paise);

        // Verify Active Inventory Hold
        $this->assertCount(1, $booking->inventoryHolds);
        $hold = $booking->inventoryHolds->first();
        $this->assertEquals(InventoryHold::STATUS_ACTIVE, $hold->status);
        $this->assertEquals(2, $hold->quantity);
    }

    public function test_client_cannot_tamper_with_price(): void
    {
        // Malicious client posts forged price parameters (e.g. price=100 or total=10)
        $payload = [
            'full_name' => 'Hacker Bob',
            'email' => 'bob@hack.com',
            'phone' => '9999999999',
            'ticket_category_id' => $this->tier1->id, // Tier 1 price is ₹2,500 (250000 paise)
            'quantity' => 1,
            'price' => 10,
            'price_paise' => 100,
            'total_amount' => 10,
            'privacy_consent' => '1',
        ];

        $response = $this->post('/register', $payload);

        $booking = Booking::whereHas('contact', function ($q) {
            $q->where('email', 'bob@hack.com');
        })->first();

        $this->assertNotNull($booking);
        // Server MUST calculate 250000 paise, completely ignoring tampered client fields
        $this->assertEquals(250000, $booking->total_amount_paise);
    }

    public function test_registration_validation_rules(): void
    {
        // Missing required privacy consent and invalid phone
        $payload = [
            'full_name' => 'Incomplete User',
            'email' => 'invalid-email',
            'phone' => '123', // invalid phone length
            'ticket_category_id' => 999999, // nonexistent category
            'quantity' => 0, // min is 1
        ];

        $response = $this->post('/register', $payload);
        $response->assertSessionHasErrors(['email', 'phone', 'ticket_category_id', 'quantity', 'privacy_consent']);
    }

    public function test_concurrency_no_oversell_for_last_seat(): void
    {
        // Reduce Tier 1 capacity to exactly 1 seat for strict concurrency testing
        $this->tier1->update(['capacity' => 1]);

        $inventoryService = app(InventoryService::class);
        $successfulBookings = 0;
        $failedAttempts = 0;

        // Simulate 15 concurrent buyers contending for the final 1 seat
        for ($i = 1; $i <= 15; $i++) {
            try {
                $booking = $inventoryService->createBookingWithHold([
                    'full_name' => "Concurrent Buyer {$i}",
                    'email' => "buyer{$i}@test.com",
                    'phone' => '987654321' . ($i % 10),
                    'ticket_category_id' => $this->tier1->id,
                    'quantity' => 1,
                    'privacy_consent' => true,
                ]);
                $successfulBookings++;
            } catch (OutOfStockException $e) {
                $failedAttempts++;
            }
        }

        // Exactly 1 must succeed, exactly 14 must fail
        $this->assertEquals(1, $successfulBookings, 'Only 1 booking should succeed for the last seat.');
        $this->assertEquals(14, $failedAttempts, 'All other 14 attempts must be cleanly rejected.');

        // Verify remaining capacity is now 0, NEVER negative
        $this->assertEquals(0, $inventoryService->getAvailableCapacity($this->tier1));
    }

    public function test_inventory_hold_expiry_releases_stock_and_updates_booking(): void
    {
        $inventoryService = app(InventoryService::class);

        // Create booking with hold
        $booking = $inventoryService->createBookingWithHold([
            'full_name' => 'Expiring Buyer',
            'email' => 'expiring@test.com',
            'phone' => '9876543210',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 5,
            'privacy_consent' => true,
        ]);

        $initialAvailable = $inventoryService->getAvailableCapacity($this->tier1);
        $this->assertEquals(95, $initialAvailable); // 100 - 5 = 95

        // Fast-forward hold expiration past 10 minutes
        $hold = $booking->inventoryHolds->first();
        $hold->update(['expires_at' => Carbon::now()->subMinute()]);

        // Run the expiry console command
        $this->artisan('app:expire-inventory-holds')
            ->expectsOutputToContain('Successfully released 1 expired inventory hold(s).')
            ->assertSuccessful();

        // Hold status must be RELEASED
        $hold->refresh();
        $this->assertEquals(InventoryHold::STATUS_RELEASED, $hold->status);

        // Booking status must be HOLD_EXPIRED
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_HOLD_EXPIRED, $booking->status);

        // Available capacity must be restored back to 100
        $restoredAvailable = $inventoryService->getAvailableCapacity($this->tier1);
        $this->assertEquals(100, $restoredAvailable);
    }
}

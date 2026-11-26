<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketAllocation;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\CheckinService;
use App\Services\InventoryService;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase6BackOfficeAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TicketCategory $tier1;
    protected TicketCategory $tier2;
    protected TicketCategory $tier3;

    protected User $superAdmin;
    protected User $eventManager;
    protected User $financeOfficer;
    protected User $gateStaff;

    protected InventoryService $inventoryService;
    protected TicketService $ticketService;
    protected CheckinService $checkinService;
    protected AnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->event = Event::where('slug', 'sagar-kinare-2026')->first();
        $this->tier1 = $this->event->categories()->where('name', 'Tier 1')->first();
        $this->tier2 = $this->event->categories()->where('name', 'Tier 2')->first();
        $this->tier3 = $this->event->categories()->where('name', 'Tier 3')->first();

        $this->superAdmin = User::where('email', 'superadmin@joygivers.org')->first();
        $this->eventManager = User::where('email', 'manager@joygivers.org')->first();
        $this->financeOfficer = User::where('email', 'finance@joygivers.org')->first();
        $this->gateStaff = User::where('email', 'gate@joygivers.org')->first();

        $this->inventoryService = app(InventoryService::class);
        $this->ticketService = app(TicketService::class);
        $this->checkinService = app(CheckinService::class);
        $this->analyticsService = app(AnalyticsService::class);
    }

    public function test_rbac_denial_cases(): void
    {
        // 1. Gate Staff denied access to dashboard and finance
        $this->actingAs($this->gateStaff)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($this->gateStaff)->get('/admin/payments')->assertStatus(403);
        $this->actingAs($this->gateStaff)->get('/admin/reports/export')->assertStatus(403);

        // 2. Event Manager can access dashboard and CRM, but denied payments/financials
        $this->actingAs($this->eventManager)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($this->eventManager)->get('/admin/crm/contacts')->assertStatus(200);
        $this->actingAs($this->eventManager)->get('/admin/payments')->assertStatus(403);
        $this->actingAs($this->eventManager)->get('/admin/reports/export')->assertStatus(403);

        // 3. Finance officer can access dashboard and payments, but not gate scanner
        $this->actingAs($this->financeOfficer)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($this->financeOfficer)->get('/admin/payments')->assertStatus(200);
        $this->actingAs($this->financeOfficer)->get('/admin/reports/sales')->assertStatus(200);
        $this->actingAs($this->financeOfficer)->get('/admin/reports/export')->assertStatus(200);
        $this->actingAs($this->financeOfficer)->get('/staff/scan')->assertStatus(403);

        // 4. Super Admin can access everything including audit logs
        $this->actingAs($this->superAdmin)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($this->superAdmin)->get('/admin/audit-logs')->assertStatus(200);
    }

    public function test_dashboard_metrics_match_database_fixtures(): void
    {
        // Fixture 1: Confirmed booking in Tier 1 (2 tickets @ 2500 = ₹5,000)
        $b1 = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Alice Walker',
            'email' => 'alice@test.com',
            'phone' => '9847111001',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 2,
            'privacy_consent' => true,
        ]);
        $b1->update(['status' => Booking::STATUS_CONFIRMED]);
        $b1->inventoryHolds()->update(['status' => \App\Models\InventoryHold::STATUS_CONVERTED]);
        $t1 = $this->ticketService->issueTicketsForBooking($b1);

        // Fixture 2: Confirmed booking in Tier 2 (1 ticket @ 1500 = ₹1,500)
        $b2 = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Bob Singer',
            'email' => 'bob@test.com',
            'phone' => '9847111002',
            'ticket_category_id' => $this->tier2->id,
            'quantity' => 1,
            'privacy_consent' => true,
        ]);
        $b2->update(['status' => Booking::STATUS_CONFIRMED]);
        $b2->inventoryHolds()->update(['status' => \App\Models\InventoryHold::STATUS_CONVERTED]);
        $t2 = $this->ticketService->issueTicketsForBooking($b2);

        // Fixture 3: Pending booking in Tier 3 (2 tickets held @ 1000 = ₹2,000)
        $b3 = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Charlie Held',
            'email' => 'charlie@test.com',
            'phone' => '9847111003',
            'ticket_category_id' => $this->tier3->id,
            'quantity' => 2,
            'privacy_consent' => true,
        ]);

        // Fixture 4: Comp / Sponsor Allocation in Tier 3 (10 seats)
        TicketAllocation::create([
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->tier3->id,
            'allocation_type' => 'SPONSOR',
            'quantity' => 10,
            'beneficiary_name' => 'Media Sponsor',
        ]);

        // Fixture 5: Check-in 1 ticket
        $admittedTicket = $t1->first();
        $sig = $this->ticketService->generateHmacSignature($admittedTicket);
        $this->checkinService->checkinTicketByQr($admittedTicket->qr_uuid, $sig, 'GATE_1', $this->gateStaff);

        // Retrieve Dashboard Metrics
        $metrics = $this->analyticsService->getDashboardMetrics();

        // 1. Collections: 500,000 + 150,000 = 650,000 paise (₹6,500)
        $this->assertEquals(650000, $metrics['gross_collections_paise']);
        $this->assertEquals('6,500.00', $metrics['gross_collections_rupees']);

        // 2. Admissions counts
        $this->assertEquals(3, $metrics['paid_tickets_issued']); // 2 + 1 = 3
        $this->assertEquals(10, $metrics['comps_issued']);
        $this->assertEquals(1, $metrics['total_checked_in']);

        // 3. 450 Shared Pool Math:
        // Total allocated: 3 (paid) + 2 (active holds) + 10 (comps) = 15
        // Remaining: 450 - 15 = 435
        $this->assertEquals(15, $metrics['total_allocated']);
        $this->assertEquals(435, $metrics['global_remaining']);
        $this->assertEquals(450, $metrics['total_capacity']);

        // 4. Rates
        $this->assertEquals(33.3, $metrics['showup_rate']); // 1 checked in out of 3 paid tickets = 33.3%
        $this->assertEquals(66.7, $metrics['conversion_rate']); // 2 paid out of 3 registrations = 66.7%
    }

    public function test_complimentary_allocations_reduce_available_seats_and_prevent_oversell(): void
    {
        $initialAvailable = $this->inventoryService->getAvailableCapacity($this->tier3);
        $this->assertEquals(250, $initialAvailable);

        // Post 25 sponsor seats
        $response = $this->actingAs($this->eventManager)->post('/admin/allocations', [
            'ticket_category_id' => $this->tier3->id,
            'allocation_type' => 'COMPLIMENTARY',
            'quantity' => 25,
            'beneficiary_name' => 'Artist Guest List',
            'notes' => 'Reserved by Joygivers board',
        ]);
        $response->assertRedirect();

        // Available must now be 225
        $updatedAvailable = $this->inventoryService->getAvailableCapacity($this->tier3);
        $this->assertEquals(225, $updatedAvailable);

        // Global capacity must also decrease by 25 (from 450 to 425)
        $this->assertEquals(425, $this->inventoryService->getGlobalRemainingCapacity($this->event));
    }

    public function test_finance_csv_export_streams_sanitized_settlement_ledger(): void
    {
        // Create confirmed booking
        $b = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Priya Nair',
            'email' => 'priya@test.com',
            'phone' => '9847222333',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => true,
        ]);
        $b->update(['status' => Booking::STATUS_CONFIRMED]);

        $response = $this->actingAs($this->financeOfficer)->get('/admin/reports/export');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Booking Reference', $content);
        $this->assertStringContainsString($b->booking_reference, $content);
        $this->assertStringContainsString('2,500.00', $content);

        // Crucial security test: Ensure customer phone number is NOT dumped into finance exports
        $this->assertStringNotContainsString('9847222333', $content);
    }

    public function test_admin_booking_details_view_renders_tickets_and_qr_codes(): void
    {
        $b = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Aravind Swamy',
            'email' => 'aravind@test.com',
            'phone' => '9847555666',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 2,
            'privacy_consent' => true,
        ]);
        $b->update(['status' => Booking::STATUS_CONFIRMED]);
        $this->ticketService->issueTicketsForBooking($b);

        $response = $this->actingAs($this->eventManager)->get("/admin/bookings/{$b->id}");
        $response->assertStatus(200);
        $response->assertSee($b->booking_reference);
        $response->assertSee('Aravind Swamy');
        $response->assertSee('Issued Admission Tickets (2)');
        $response->assertSee('<svg', false); // Confirms SVG QR code is rendered
    }

    public function test_concierge_enquiry_status_transitions_and_actions(): void
    {
        $b = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Fahadh Faasil',
            'email' => 'fahadh@test.com',
            'phone' => '9847111222',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => true,
        ]);
        $this->assertEquals(Booking::STATUS_PAYMENT_PENDING, $b->status);

        // 1. Transition to IN_PROCESS (team called customer)
        $response = $this->actingAs($this->eventManager)->post("/admin/bookings/{$b->id}/status", [
            'status' => 'IN_PROCESS',
            'note' => 'Payment team called, UPI details sent via WhatsApp',
        ]);
        $response->assertRedirect();
        $b->refresh();
        $this->assertEquals(Booking::STATUS_IN_PROCESS, $b->status);

        // 2. Transition to CONFIRMED (customer paid)
        $response2 = $this->actingAs($this->eventManager)->post("/admin/bookings/{$b->id}/status", [
            'status' => 'CONFIRMED',
        ]);
        $response2->assertRedirect();
        $b->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $b->status);
        $this->assertEquals(1, $b->tickets()->count());
    }

    public function test_admin_can_cancel_enquiry_and_release_held_seats(): void
    {
        $initialCapacity = $this->inventoryService->getAvailableCapacity($this->tier2);

        $b = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Prithviraj Sukumaran',
            'email' => 'prithvi@test.com',
            'phone' => '9847333444',
            'ticket_category_id' => $this->tier2->id,
            'quantity' => 2,
            'privacy_consent' => true,
        ]);

        $heldCapacity = $this->inventoryService->getAvailableCapacity($this->tier2);
        $this->assertEquals($initialCapacity - 2, $heldCapacity);

        // Admin cancels enquiry
        $response = $this->actingAs($this->eventManager)->post("/admin/bookings/{$b->id}/status", [
            'status' => 'CANCELLED',
            'reason' => 'Customer requested cancellation over call',
        ]);
        $response->assertRedirect();

        $b->refresh();
        $this->assertEquals(Booking::STATUS_CANCELLED, $b->status);

        // Verify seats returned to pool
        $restoredCapacity = $this->inventoryService->getAvailableCapacity($this->tier2);
        $this->assertEquals($initialCapacity, $restoredCapacity);
    }

    public function test_ticket_dispatch_desk_renders_and_generates_tickets(): void
    {
        $b = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Mohanlal Viswanathan',
            'email' => 'lalettan@test.com',
            'phone' => '9847999888',
            'ticket_category_id' => $this->tier3->id,
            'quantity' => 2,
            'privacy_consent' => true,
        ]);

        // Dispatch desk page renders
        $deskResponse = $this->actingAs($this->superAdmin)->get('/admin/dispatch');
        $deskResponse->assertStatus(200);
        $deskResponse->assertSee('Ticket Generator &amp; Dispatch Desk', false);
        $deskResponse->assertSee($b->booking_reference);

        // Generate tickets from dispatch desk
        $genResponse = $this->actingAs($this->superAdmin)->post("/admin/dispatch/{$b->id}/generate");
        $genResponse->assertRedirect();

        $b->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $b->status);
        $this->assertEquals(2, $b->tickets()->count());
    }

    public function test_whatsapp_pass_message_contains_structured_event_details_and_direct_qr_links(): void
    {
        $b = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Kavya Madhavan',
            'email' => 'kavya@test.com',
            'phone' => '9847333444',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => true,
        ]);
        $b->update(['status' => Booking::STATUS_CONFIRMED]);
        $this->ticketService->issueTicketsForBooking($b);

        $msg = $this->ticketService->buildWhatsAppTicketMessage($b);
        $this->assertStringContainsString('Raag-E-Ishq', $msg);
        $this->assertStringContainsString('Chavara Hall', $msg);
        $this->assertStringContainsString('Kavya Madhavan', $msg);
        $this->assertStringContainsString($b->booking_reference, $msg);
        $this->assertStringContainsString('Pass #1', $msg);
        $this->assertStringContainsString('17th January 2027', $msg);

        $url = $this->ticketService->buildWhatsAppUrl($b->contact->phone, $msg);
        $this->assertStringStartsWith('https://wa.me/919847333444?text=', $url);

        // Also check bookings desk index returns the page with modal markup and button
        $response = $this->actingAs($this->eventManager)->get('/admin/bookings');
        $response->assertStatus(200);
        $response->assertSee('whatsappDispatchModal');
        $response->assertSee('WhatsApp Pass');
    }

    public function test_signed_pass_url_renders_tickets_even_when_booking_is_absent_from_database(): void
    {
        $b = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Devika Menon',
            'email' => 'devika@test.com',
            'phone' => '9847555666',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => true,
        ]);
        $b->update(['status' => Booking::STATUS_CONFIRMED]);
        $tickets = $this->ticketService->issueTicketsForBooking($b);
        $ticketNumber = $tickets->first()->ticket_number;
        $reference = $b->booking_reference;

        $signedUrl = $this->ticketService->buildSignedPassUrl($b);
        $msg = $this->ticketService->buildWhatsAppTicketMessage($b);

        $this->assertStringContainsString('/p/', $signedUrl);
        $this->assertStringNotContainsString('?p=', $signedUrl);
        $this->assertLessThan(160, strlen((string) parse_url($signedUrl, PHP_URL_PATH)));
        $this->assertStringContainsString($signedUrl, $msg);
        $this->assertStringNotContainsString("\u{FFFD}", $msg);

        // When DB row exists, short link renders live DB record (including email)
        $this->get($signedUrl)
            ->assertStatus(200)
            ->assertSee('devika@test.com');

        // Simulate serverless /tmp/database.sqlite container reset by removing DB rows
        $b->emailDeliveries()->delete();
        $b->tickets()->delete();
        $b->inventoryHolds()->delete();
        $b->items()->delete();
        $b->delete();

        $this->assertDatabaseMissing('bookings', ['booking_reference' => $reference]);

        $response = $this->get($signedUrl);
        $response->assertStatus(200);
        $response->assertSee('Devika Menon');
        $response->assertSee($reference);
        $response->assertSee($ticketNumber);
        $response->assertSee('<svg', false);
        $response->assertSee('data:image/svg+xml;base64,', false);

        // Tampered short tokens and legacy tokens must be rejected
        $this->get('/p/tampered-invalid-token')->assertRedirect(route('event.show'));
        $tamperedShortUrl = substr($signedUrl, 0, -4) . 'AAAA';
        $this->get($tamperedShortUrl)->assertRedirect(route('event.show'));
        $this->get('/booking/' . $reference . '/confirmed?p=tampered&s=badsignature')->assertRedirect(route('event.show'));
    }

    public function test_admin_search_and_filter_rank_name_matches_first_and_normalize_phone(): void
    {
        $olderHari = $this->inventoryService->createBookingWithHold([
            'full_name' => 'hari',
            'email' => 'iyhfsfh@example.com',
            'phone' => '8493939393',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => true,
        ]);
        $olderHari->update(['status' => Booking::STATUS_CONFIRMED, 'created_at' => now()->subDay()]);

        $newerSooraj = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Sooraj',
            'email' => 'mohammedharishjaved@example.com',
            'phone' => '6282130150',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => true,
        ]);

        $unrelated = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Arun Kumar',
            'email' => 'arun@example.com',
            'phone' => '9847000111',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => true,
        ]);

        // Name match ('hari') must rank above newer email-only match ('Sooraj'), and exclude unrelated ('Arun')
        $searchResp = $this->actingAs($this->eventManager)->get('/admin/bookings?search=hari');
        $searchResp->assertStatus(200);
        $searchResp->assertSeeInOrder([$olderHari->booking_reference, $newerSooraj->booking_reference]);
        $searchResp->assertDontSee($unrelated->booking_reference);
        $searchResp->assertSee('matched email');
        $searchResp->assertSee('matching (of');

        // Normalized +91 spaced phone query finds stored 10-digit phone cleanly
        $phoneResp = $this->actingAs($this->eventManager)->get('/admin/bookings?search=' . urlencode('+91 84939 39393'));
        $phoneResp->assertStatus(200);
        $phoneResp->assertSee($olderHari->booking_reference);
        $phoneResp->assertDontSee($unrelated->booking_reference);

        // Combined status + search filter excludes non-confirmed bookings and preserves search on tabs
        $filteredResp = $this->actingAs($this->eventManager)->get('/admin/bookings?status=CONFIRMED&search=hari');
        $filteredResp->assertStatus(200);
        $filteredResp->assertSee($olderHari->booking_reference);
        $filteredResp->assertDontSee($newerSooraj->booking_reference);
        $filteredResp->assertSee('search=hari');

        // Dispatch & CRM search honor relevance and phone normalization too
        $this->actingAs($this->eventManager)->get('/admin/dispatch?search=hari')
            ->assertStatus(200)
            ->assertSeeInOrder([$olderHari->booking_reference, $newerSooraj->booking_reference])
            ->assertDontSee($unrelated->booking_reference);

        $this->actingAs($this->eventManager)->get('/admin/crm/contacts?search=' . urlencode('+91 8493939393'))
            ->assertStatus(200)
            ->assertSee('8493939393')
            ->assertDontSee('9847000111');
    }

    public function test_dashboard_updates_seats_gross_collections_and_isolates_comps_and_expired_holds(): void
    {
        // 1. Issue 2 Tier 1 sponsor comp tickets (₹0, consumes 2 seats)
        $this->actingAs($this->eventManager)->post('/admin/allocations', [
            'ticket_category_id' => $this->tier1->id,
            'allocation_type' => 'SPONSOR',
            'quantity' => 2,
            'beneficiary_name' => 'Sponsor Patron',
        ])->assertRedirect();

        // 2. Create 1 expired hold in Tier 2 (should be released on dashboard load)
        $expired = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Expired Guest',
            'email' => 'expired@test.com',
            'phone' => '9847000222',
            'ticket_category_id' => $this->tier2->id,
            'quantity' => 3,
            'privacy_consent' => true,
        ]);
        $expired->update(['hold_expires_at' => now()->subMinutes(5)]);
        $expired->inventoryHolds()->update(['expires_at' => now()->subMinutes(5)]);

        // 3. Create 1 pending Tier 1 booking (2 seats @ 2500 = ₹5,000) & approve via admin
        $paid = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Lakshmi Nair',
            'email' => 'lakshmi@test.com',
            'phone' => '9847111999',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 2,
            'privacy_consent' => true,
        ]);
        $this->actingAs($this->eventManager)->post("/admin/bookings/{$paid->id}/confirm-payment")->assertRedirect();

        // 4. Create 1 non-attending donation in Tier 3 (4 x ₹1,000 = ₹4,000, 0 seats) & approve via admin
        $donor = $this->inventoryService->createBookingWithHold([
            'full_name' => 'Charity Patron',
            'email' => 'donor@test.com',
            'phone' => '9847888999',
            'ticket_category_id' => $this->tier3->id,
            'quantity' => 4,
            'attending' => 'no',
            'privacy_consent' => true,
        ]);
        $this->actingAs($this->eventManager)->post("/admin/bookings/{$donor->id}/confirm-payment")->assertRedirect();

        // Load dashboard — must release expired hold, forbid caching, and match exact KPIs
        $dash = $this->actingAs($this->eventManager)->get('/admin/dashboard');
        $dash->assertOk();
        $this->assertStringContainsString('no-store', (string) $dash->headers->get('Cache-Control'));

        $metrics = $this->analyticsService->getDashboardMetrics();
        // Gross = ₹5,000 (paid) + ₹4,000 (donation) + ₹0 (sponsor) = ₹9,000
        $this->assertEquals(900000, $metrics['gross_collections_paise']);
        // Paid tickets issued = 2 only (NOT sponsor comp 2 or non-attending donation 4)
        $this->assertEquals(2, $metrics['paid_tickets_issued']);
        $this->assertEquals(2, $metrics['comps_issued']);
        // Remaining seats: 450 - 2 (paid) - 2 (sponsor) = 446 (expired 3 seats released)
        $this->assertEquals(446, $metrics['global_remaining']);
        $this->assertEquals(4, $metrics['total_allocated']);

        $tier1Row = collect($metrics['category_breakdown'])->firstWhere('name', 'Tier 1');
        $this->assertEquals(2, $tier1Row['sold']);
        $this->assertEquals(2, $tier1Row['comps']);
        $this->assertEquals(96, $tier1Row['available']);
        $this->assertEquals(500000, $tier1Row['revenue_paise']); // Does NOT add ₹5,000 fake revenue for comp

        $tier2Row = collect($metrics['category_breakdown'])->firstWhere('name', 'Tier 2');
        $this->assertEquals(100, $tier2Row['available']); // Expired hold freed cleanly
    }
}

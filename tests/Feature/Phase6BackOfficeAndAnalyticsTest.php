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
}

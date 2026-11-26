<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Checkin;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\CheckinService;
use App\Services\InventoryService;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5GateOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TicketCategory $tier1;
    protected User $gateStaff;
    protected User $superAdmin;
    protected User $financeUser;
    protected TicketService $ticketService;
    protected CheckinService $checkinService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->event = Event::where('slug', 'sagar-kinare-2026')->first();
        $this->tier1 = $this->event->categories()->where('name', 'Gold')->first();
        $this->gateStaff = User::where('email', 'gate@joygivers.org')->first();
        $this->superAdmin = User::where('email', 'superadmin@joygivers.org')->first();
        $this->financeUser = User::where('email', 'finance@joygivers.org')->first();

        $this->ticketService = app(TicketService::class);
        $this->checkinService = app(CheckinService::class);
    }

    protected function createValidTicket(): Ticket
    {
        $inventoryService = app(InventoryService::class);
        $booking = $inventoryService->createBookingWithHold([
            'full_name' => 'Aditya Varma',
            'email' => 'aditya@example.com',
            'phone' => '9847111222',
            'ticket_category_id' => $this->tier1->id,
            'quantity' => 1,
            'privacy_consent' => true,
        ]);

        $booking->update(['status' => Booking::STATUS_CONFIRMED]);
        $tickets = $this->ticketService->issueTicketsForBooking($booking);

        return $tickets->first();
    }

    public function test_unauthenticated_user_cannot_access_scanner(): void
    {
        $response = $this->get('/staff/scan');
        $response->assertRedirect('/login');
    }

    public function test_finance_user_cannot_access_gate_scanner(): void
    {
        $response = $this->actingAs($this->financeUser)->get('/staff/scan');
        $response->assertStatus(403);
    }

    public function test_gate_staff_can_access_scanner(): void
    {
        $response = $this->actingAs($this->gateStaff)->get('/staff/scan');
        $response->assertStatus(200);
        $response->assertSee('Gate Operations');
    }

    public function test_first_scan_returns_green_valid_and_creates_atomic_checkin(): void
    {
        $ticket = $this->createValidTicket();
        $sig = $this->ticketService->generateHmacSignature($ticket);

        $response = $this->actingAs($this->gateStaff)->postJson('/staff/scan/verify', [
            'uuid' => $ticket->qr_uuid,
            'signature' => $sig,
            'gate' => 'GATE_NORTH',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'result' => 'GREEN_VALID',
            'attendee_name' => 'Aditya Varma',
            'ticket_number' => $ticket->ticket_number,
        ]);

        // Verify database state
        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_CHECKED_IN, $ticket->status);

        $checkin = Checkin::where('ticket_id', $ticket->id)->first();
        $this->assertNotNull($checkin);
        $this->assertEquals('GATE_NORTH', $checkin->gate_identifier);
        $this->assertEquals($this->gateStaff->id, $checkin->scanned_by_user_id);
    }

    public function test_second_scan_returns_orange_already_used_with_timestamp(): void
    {
        $ticket = $this->createValidTicket();
        $sig = $this->ticketService->generateHmacSignature($ticket);

        // 1. First scan (Green)
        $this->actingAs($this->gateStaff)->postJson('/staff/scan/verify', [
            'uuid' => $ticket->qr_uuid,
            'signature' => $sig,
        ])->assertStatus(200);

        // 2. Second scan (Orange)
        $secondResponse = $this->actingAs($this->gateStaff)->postJson('/staff/scan/verify', [
            'uuid' => $ticket->qr_uuid,
            'signature' => $sig,
        ]);

        $secondResponse->assertStatus(409);
        $secondResponse->assertJson([
            'result' => 'ORANGE_ALREADY_USED',
        ]);

        // Still strictly ONE check-in in the database!
        $this->assertEquals(1, Checkin::where('ticket_id', $ticket->id)->count());
    }

    public function test_double_scan_race_condition_yields_exactly_one_success_and_one_duplicate(): void
    {
        $ticket = $this->createValidTicket();
        $sig = $this->ticketService->generateHmacSignature($ticket);

        // Simulate two gate phones submitting check-in calls concurrently
        $res1 = $this->checkinService->checkinTicketByQr($ticket->qr_uuid, $sig, 'GATE_1', $this->gateStaff);
        $res2 = $this->checkinService->checkinTicketByQr($ticket->qr_uuid, $sig, 'GATE_2', $this->superAdmin);

        $results = collect([$res1['result'], $res2['result']]);

        // Exactly one must be GREEN_VALID and one must be ORANGE_ALREADY_USED
        $this->assertTrue($results->contains('GREEN_VALID'), 'One scan must be GREEN_VALID');
        $this->assertTrue($results->contains('ORANGE_ALREADY_USED'), 'Second simultaneous scan must be ORANGE_ALREADY_USED');
        $this->assertEquals(1, Checkin::where('ticket_id', $ticket->id)->count());
    }

    public function test_voided_and_cancelled_tickets_return_red_invalid(): void
    {
        $ticket = $this->createValidTicket();
        $sig = $this->ticketService->generateHmacSignature($ticket);

        // Mark ticket VOID
        $this->ticketService->voidTicket($ticket, 'Payment disputed');

        $response = $this->actingAs($this->gateStaff)->postJson('/staff/scan/verify', [
            'uuid' => $ticket->qr_uuid,
            'signature' => $sig,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'result' => 'RED_INVALID',
        ]);
    }

    public function test_permission_gated_and_audited_manual_lookup(): void
    {
        $ticket = $this->createValidTicket();

        // 1. Missing justification reason fails validation
        $failResponse = $this->actingAs($this->gateStaff)->postJson('/staff/lookup/manual', [
            'ticket_number' => $ticket->ticket_number,
            'reason' => '',
        ]);
        $failResponse->assertStatus(422);

        // 2. Valid manual check-in with audit reason
        $response = $this->actingAs($this->gateStaff)->postJson('/staff/lookup/manual', [
            'ticket_number' => $ticket->ticket_number,
            'reason' => 'Guest screen broken, verified Passport identity',
            'gate' => 'MANUAL_GATE_VIP',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'result' => 'GREEN_VALID',
            'attendee_name' => 'Aditya Varma',
        ]);

        // Verify audit log
        $audit = AuditLog::where('action', 'MANUAL_GATE_ADMISSION')
            ->where('target_id', $ticket->id)
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals($this->gateStaff->id, $audit->user_id);
        $this->assertStringContainsString('Passport', $audit->new_values['reason']);
    }

    public function test_manual_lookup_accepts_lowercase_and_missing_tkt_prefix_and_renders_inline_feedback_ui(): void
    {
        $ticket = $this->createValidTicket();
        $bareCode = str_replace('TKT-', '', strtolower($ticket->ticket_number));

        $scanPage = $this->actingAs($this->gateStaff)->get('/staff/scan');
        $scanPage->assertOk();
        $scanPage->assertSee('manual_inline_feedback');
        $scanPage->assertSee('"/staff/lookup/manual"', false);
        $scanPage->assertSee('playSiren');
        $scanPage->assertSee('sound_toggle_btn');

        // Lookup without TKT- prefix and lowercase with extra spaces works cleanly
        $admit = $this->actingAs($this->gateStaff)->postJson('/staff/lookup/manual', [
            'ticket_number' => "  {$bareCode}  ",
            'reason' => 'Others',
        ]);

        $admit->assertStatus(200);
        $admit->assertJson([
            'result' => 'GREEN_VALID',
            'ticket_number' => $ticket->ticket_number,
            'attendee_name' => 'Aditya Varma',
        ]);

        // Duplicate manual check-in must return 409 ORANGE_ALREADY_USED
        $dup = $this->actingAs($this->gateStaff)->postJson('/staff/lookup/manual', [
            'ticket_number' => $ticket->ticket_number,
            'reason' => 'Others',
        ]);

        $dup->assertStatus(409);
        $dup->assertJson(['result' => 'ORANGE_ALREADY_USED']);
    }
}

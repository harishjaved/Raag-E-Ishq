<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Donation;
use App\Models\Event;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\InventoryService;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RaagEIshqRefactorAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TicketCategory $gold;
    protected TicketCategory $silver;
    protected TicketCategory $bronze;
    protected User $superAdmin;
    protected User $eventManager;
    protected User $financeOfficer;
    protected User $gateStaff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->event = Event::where('slug', 'sagar-kinare-2026')->firstOrFail();
        $this->gold = $this->event->categories()->where('name', 'Gold')->firstOrFail();
        $this->silver = $this->event->categories()->where('name', 'Silver')->firstOrFail();
        $this->bronze = $this->event->categories()->where('name', 'Bronze')->firstOrFail();

        $this->superAdmin = User::where('email', 'superadmin@joygivers.org')->firstOrFail();
        $this->eventManager = User::where('email', 'manager@joygivers.org')->firstOrFail();
        $this->financeOfficer = User::where('email', 'finance@joygivers.org')->firstOrFail();
        $this->gateStaff = User::where('email', 'gate@joygivers.org')->firstOrFail();
    }

    public function test_login_page_never_exposes_demo_credentials_or_passwords(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Staff &amp; Operations Sign In', false);
        $response->assertDontSee('Password123!');
        $response->assertDontSee('superadmin@joygivers.org');
        $response->assertDontSee('manager@joygivers.org');
        $response->assertDontSee('finance@joygivers.org');
        $response->assertDontSee('gate@joygivers.org');
    }

    public function test_login_rate_limiter_locks_out_after_five_failed_attempts_and_audits(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $resp = $this->post('/login', [
                'email' => 'superadmin@joygivers.org',
                'password' => 'WrongPassword!',
            ]);
            $resp->assertSessionHasErrors('email');
        }

        // 6th attempt triggers 429 Too Many Requests
        $locked = $this->post('/login', [
            'email' => 'superadmin@joygivers.org',
            'password' => 'Password123!',
        ]);
        $locked->assertStatus(429);
        $this->assertGuest();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'AUTH_LOGIN_LOCKOUT',
        ]);
    }

    public function test_optional_totp_2fa_setup_challenge_recovery_code_and_password_rotation(): void
    {
        $totpService = app(TotpService::class);

        // 1. Open security page to provision pending TOTP secret
        $securityPage = $this->actingAs($this->superAdmin)->get('/admin/security');
        $securityPage->assertOk();
        $securityPage->assertSee('Two-Factor Authentication');
        $secret = session('setup_2fa_secret');
        $this->assertNotEmpty($secret);

        // 2. Reject invalid TOTP code
        $this->actingAs($this->superAdmin)->post('/admin/security/2fa/enable', [
            'code' => '000000',
        ])->assertSessionHasErrors('code');

        // 3. Enable TOTP with real live RFC 6238 code
        $liveCode = $totpService->generateCode($secret);
        $enableRes = $this->actingAs($this->superAdmin)->post('/admin/security/2fa/enable', [
            'code' => $liveCode,
        ]);
        $enableRes->assertRedirect();
        $enableRes->assertSessionHas('new_recovery_codes');
        $recoveryCodes = session('new_recovery_codes');
        $this->assertCount(8, $recoveryCodes);

        $this->superAdmin->refresh();
        $this->assertTrue($this->superAdmin->hasTwoFactorEnabled());

        // 4. Logout & sign back in -> must gate behind two-factor challenge before granting auth session
        $this->actingAs($this->superAdmin)->post('/logout')->assertRedirect('/login');
        $loginRes = $this->post('/login', [
            'email' => 'superadmin@joygivers.org',
            'password' => 'Password123!',
        ]);
        $loginRes->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        // 5. Wrong 2FA code fails and logs audit event
        $this->post('/two-factor-challenge', ['code' => '999999'])
            ->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['action' => 'AUTH_2FA_FAILED']);

        // 6. Valid one-time recovery code succeeds and consumes that recovery code
        $verifyRes = $this->post('/two-factor-challenge', [
            'code' => $recoveryCodes[0],
        ]);
        $verifyRes->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->superAdmin);

        $this->superAdmin->refresh();
        $this->assertCount(7, $this->superAdmin->two_factor_recovery_codes);

        // 7. Rotate password cleanly & disable 2FA with current password verification
        $this->actingAs($this->superAdmin)->post('/admin/security/password', [
            'current_password' => 'Password123!',
            'new_password' => 'NewSecurePass#2027',
            'new_password_confirmation' => 'NewSecurePass#2027',
        ])->assertRedirect();

        $this->superAdmin->refresh();
        $this->assertTrue(Hash::check('NewSecurePass#2027', $this->superAdmin->password));

        $this->actingAs($this->superAdmin)->post('/admin/security/2fa/disable', [
            'current_password' => 'NewSecurePass#2027',
        ])->assertRedirect();
        $this->superAdmin->refresh();
        $this->assertFalse($this->superAdmin->hasTwoFactorEnabled());
    }

    public function test_standalone_donation_does_not_reduce_hall_seats_and_tracks_utr_acknowledgement(): void
    {
        $inventoryService = app(InventoryService::class);

        // Create standalone cause donation via /donate JSON API
        $donateRes = $this->postJson('/donate', [
            'full_name' => 'Arundhati Verma',
            'phone' => '9847123456',
            'email' => 'arundhati@donor.org',
            'amount_rupees' => 2500,
            'donor_note' => 'For bright classrooms in Kerala',
        ]);

        $donateRes->assertCreated();
        $donateRes->assertJson([
            'status' => 'success',
            'donation_status' => Donation::STATUS_PENDING_PAYMENT,
            'formatted_amount' => '₹2,500',
            'amount_paise' => 250000,
        ]);

        $donationRef = $donateRes->json('donation_reference');
        $this->assertStringStartsWith('REI-DON-', $donationRef);

        // Critical invariant: 0 seats deducted from any category or 450-seat global capacity
        $this->assertEquals(450, $inventoryService->getGlobalRemainingCapacity($this->event));
        $this->assertEquals(100, $inventoryService->getAvailableCapacity($this->gold));
        $this->assertEquals(100, $inventoryService->getAvailableCapacity($this->silver));
        $this->assertEquals(250, $inventoryService->getAvailableCapacity($this->bronze));

        // Donor opens acknowledgement page & submits UTR proof
        $this->get(route('donation.show', $donationRef))
            ->assertOk()
            ->assertSee($donationRef)
            ->assertSee('₹2,500');

        $this->post(route('donation.submit-payment', $donationRef), [
            'utr_reference' => 'UTR-DON-998877',
        ])->assertRedirect();

        $donation = Donation::where('donation_reference', $donationRef)->firstOrFail();
        $this->assertEquals(Donation::STATUS_PAYMENT_SUBMITTED, $donation->status);
        $this->assertEquals('UTR-DON-998877', $donation->utr_reference);

        // Admin verifies and acknowledges donation
        $this->actingAs($this->eventManager)
            ->post(route('admin.donations.acknowledge', $donation->id))
            ->assertRedirect();

        $donation->refresh();
        $this->assertEquals(Donation::STATUS_ACKNOWLEDGED, $donation->status);
        $this->assertNotNull($donation->acknowledged_at);

        $metrics = app(AnalyticsService::class)->getDashboardMetrics();
        $this->assertEquals(1, $metrics['standalone_donations_count']);
        $this->assertEquals(250000, $metrics['standalone_donations_paise']);
        $this->assertEquals(250000, $metrics['standalone_donations_acknowledged_paise']);
        $this->assertEquals(0, $metrics['paid_tickets_issued']);
    }

    public function test_six_stage_reservation_hold_utr_proof_verification_and_ics_download(): void
    {
        Queue::fake();

        // Stage 1 & 2: Guest requests 2 Silver seats -> REI- prefix + 30-minute hold
        $regRes = $this->post('/register', [
            'full_name' => 'Karthik Menon',
            'email' => 'karthik@example.com',
            'phone' => '9847551122',
            'ticket_category_id' => $this->silver->id,
            'quantity' => 2,
            'privacy_consent' => '1',
        ]);

        $booking = Booking::whereHas('contact', fn ($q) => $q->where('email', 'karthik@example.com'))->firstOrFail();
        $regRes->assertRedirect(route('booking.payment', $booking->booking_reference));
        $this->assertStringStartsWith('REI-', $booking->booking_reference);
        $this->assertEquals(Booking::STATUS_PAYMENT_PENDING, $booking->status);
        $this->assertEquals(300000, $booking->total_amount_paise); // 2 x ₹1,500 = ₹3,000
        $this->assertTrue($booking->hold_expires_at->greaterThan(now()->addMinutes(25)));

        // Payment page shows 30-min countdown banner + UPI QR + 6 stages
        $this->get(route('booking.payment', $booking->booking_reference))
            ->assertOk()
            ->assertSee('Your seats are held for 30 minutes while awaiting payment')
            ->assertSee('₹3,000')
            ->assertSee('Stage 2 of 6');

        // Stage 3: Guest submits UTR payment proof
        $this->post(route('booking.submit-payment', $booking->booking_reference), [
            'utr_reference' => 'UPI445566778899',
            'payment_screenshot_reference' => 'Screenshot shared on WhatsApp',
        ])->assertRedirect(route('booking.payment', $booking->booking_reference));

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_PAYMENT_SUBMITTED, $booking->status);
        $this->assertEquals('UPI445566778899', $booking->utr_reference);
        $this->assertNotNull($booking->payment_submitted_at);

        // Stage 4-6: Admin verifies & confirms payment -> passes issued & resent
        $this->actingAs($this->financeOfficer)
            ->post(route('admin.bookings.confirm-payment', $booking->id))
            ->assertRedirect();

        $booking->refresh();
        $this->assertTrue($booking->isConfirmed());
        $this->assertNotNull($booking->payment_verified_at);
        $this->assertCount(2, $booking->tickets);

        $this->actingAs($this->eventManager)
            ->post(route('admin.bookings.resend', $booking->id))
            ->assertRedirect()
            ->assertSessionHas('just_approved_booking_id', $booking->id);

        Queue::assertPushed(\App\Jobs\SendTicketEmailJob::class);

        // Calendar .ics download works properly
        $icsRes = $this->get(route('event.calendar'));
        $icsRes->assertOk();
        $icsRes->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $icsRes->getContent());
        $this->assertStringContainsString('Raag-E-Ishq Featuring Kuldeep Sagar', $icsRes->getContent());
    }

    public function test_offline_booking_entry_prevents_oversell_and_exports_utr_in_reconciliation_csv(): void
    {
        Queue::fake();

        // 1. Offline paid Cash booking of 4 Gold seats
        $this->actingAs($this->eventManager)->post(route('admin.bookings.offline'), [
            'full_name' => 'Gopalakrishnan Nair',
            'email' => 'gopal@patron.in',
            'phone' => '9847009900',
            'ticket_category_id' => $this->gold->id,
            'quantity' => 4,
            'payment_channel' => 'CASH',
            'utr_reference' => 'CASH-DESK-101',
            'mark_paid' => '1',
        ])->assertRedirect()->assertSessionHas('just_approved_booking_id');

        $offlineBooking = Booking::where('utr_reference', 'CASH-DESK-101')->firstOrFail();
        $this->assertTrue($offlineBooking->is_offline_booking);
        $this->assertEquals('CASH', $offlineBooking->payment_channel);
        $this->assertTrue($offlineBooking->isConfirmed());
        $this->assertCount(4, $offlineBooking->tickets);

        // Remaining Gold seats dropped from 100 to 96
        $inventoryService = app(InventoryService::class);
        $this->assertEquals(96, $inventoryService->getAvailableCapacity($this->gold));

        // 2. Oversell prevention when remaining capacity drops below requested offline quantity
        $this->silver->update(['capacity' => 2]);
        $this->actingAs($this->eventManager)->post(route('admin.bookings.offline'), [
            'full_name' => 'Oversell Attempt',
            'email' => 'oversell@test.com',
            'phone' => '9847009911',
            'ticket_category_id' => $this->silver->id,
            'quantity' => 5,
            'payment_channel' => 'UPI_OFFLINE',
            'mark_paid' => '1',
        ])->assertSessionHasErrors('quantity');
        $this->assertEquals(2, $inventoryService->getAvailableCapacity($this->silver));

        // 3. Create standalone donation & confirm CSV reconciliation exports offline UTR & donation safely without leaking phones
        $this->post('/donate', [
            'full_name' => 'Vidya Menon',
            'phone' => '9847009922',
            'email' => 'vidya@donor.in',
            'amount_rupees' => 5000,
            'utr_reference' => 'UTR-CSV-DON-777',
        ]);

        $csvRes = $this->actingAs($this->financeOfficer)->get(route('admin.reports.export'));
        $csvRes->assertOk();
        $csv = $csvRes->streamedContent();
        $this->assertStringContainsString('Offline Ticket Booking', $csv);
        $this->assertStringContainsString('CASH-DESK-101', $csv);
        $this->assertStringContainsString('Cause Donation', $csv);
        $this->assertStringContainsString('UTR-CSV-DON-777', $csv);
        $this->assertStringNotContainsString('9847009900', $csv);
        $this->assertStringNotContainsString('9847009922', $csv);
    }

    public function test_rbac_strictly_isolates_gate_staff_and_staff_toggles(): void
    {
        $booking = app(InventoryService::class)->createBookingWithHold([
            'full_name' => 'RBAC Check',
            'email' => 'rbac@test.com',
            'phone' => '9847221100',
            'ticket_category_id' => $this->bronze->id,
            'quantity' => 1,
            'privacy_consent' => true,
        ]);

        // Gate Staff denied all admin & financial routes
        $this->actingAs($this->gateStaff)->get(route('admin.security'))->assertStatus(403);
        $this->actingAs($this->gateStaff)->post(route('admin.bookings.offline'), [])->assertStatus(403);
        $this->actingAs($this->gateStaff)->post(route('admin.bookings.resend', $booking->id))->assertStatus(403);
        $this->actingAs($this->gateStaff)->get(route('admin.reports.sales'))->assertStatus(403);
        $this->actingAs($this->gateStaff)->get(route('admin.reports.export'))->assertStatus(403);

        // Non-SuperAdmin denied SuperAdmin staff toggle
        $this->actingAs($this->eventManager)
            ->post(route('admin.security.staff.toggle', $this->gateStaff->id))
            ->assertStatus(403);

        // SuperAdmin can deactivate and block sign-in for inactive staff account
        $this->actingAs($this->superAdmin)
            ->post(route('admin.security.staff.toggle', $this->gateStaff->id))
            ->assertRedirect();

        $this->gateStaff->refresh();
        $this->assertFalse($this->gateStaff->is_active);

        auth()->logout();

        $this->post('/login', [
            'email' => 'gate@joygivers.org',
            'password' => 'Password123!',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}

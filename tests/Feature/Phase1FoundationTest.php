<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSetting;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase1FoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_all_16_system_tables_exist(): void
    {
        $tables = [
            'users',
            'password_reset_tokens',
            'sessions',
            'cache',
            'jobs',
            'events',
            'ticket_categories',
            'contacts',
            'bookings',
            'booking_items',
            'inventory_holds',
            'payment_attempts',
            'gateway_webhook_events',
            'tickets',
            'checkins',
            'ticket_allocations',
            'refunds',
            'audit_logs',
            'event_settings',
            'email_deliveries',
            'donations',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected table {$table} to exist.");
        }
    }

    public function test_default_event_and_capacity_allocations(): void
    {
        $event = Event::where('slug', 'sagar-kinare-2026')->first();
        $this->assertNotNull($event);
        $this->assertEquals(450, $event->total_capacity);
        $this->assertFalse($event->is_published);
        $this->assertFalse($event->live_payments_enabled);

        $categories = $event->categories;
        $this->assertCount(3, $categories);

        $gold = $categories->firstWhere('name', 'Gold');
        $silver = $categories->firstWhere('name', 'Silver');
        $bronze = $categories->firstWhere('name', 'Bronze');

        $this->assertEquals(250000, $gold->price_paise);
        $this->assertEquals(100, $gold->capacity);

        $this->assertEquals(150000, $silver->price_paise);
        $this->assertEquals(100, $silver->capacity);

        $this->assertEquals(100000, $bronze->price_paise);
        $this->assertEquals(250, $bronze->capacity);

        // Sum of tier capacities MUST equal 450
        $this->assertEquals(450, $categories->sum('capacity'));
    }

    public function test_rbac_users_and_role_permissions(): void
    {
        $superAdmin = User::where('email', 'superadmin@joygivers.org')->first();
        $manager = User::where('email', 'manager@joygivers.org')->first();
        $finance = User::where('email', 'finance@joygivers.org')->first();
        $gateStaff = User::where('email', 'gate@joygivers.org')->first();

        $this->assertNotNull($superAdmin);
        $this->assertNotNull($manager);
        $this->assertNotNull($finance);
        $this->assertNotNull($gateStaff);

        $this->assertTrue(Hash::check('Password123!', $superAdmin->password));

        // Test permissions
        $this->assertTrue($superAdmin->canAccessAdmin());
        $this->assertTrue($superAdmin->canScan());
        $this->assertTrue($superAdmin->canManageFinancials());

        $this->assertTrue($manager->canAccessAdmin());
        $this->assertTrue($manager->canScan());
        $this->assertFalse($manager->canManageFinancials());

        $this->assertTrue($finance->canAccessAdmin());
        $this->assertFalse($finance->canScan());
        $this->assertTrue($finance->canManageFinancials());

        $this->assertFalse($gateStaff->canAccessAdmin());
        $this->assertTrue($gateStaff->canScan());
        $this->assertFalse($gateStaff->canManageFinancials());
    }

    public function test_event_settings_seeded(): void
    {
        $this->assertEquals('30', EventSetting::get('hold_duration_minutes'));
        $this->assertEquals('events@joygivers.org', EventSetting::get('support_email'));
        $this->assertEquals('test', EventSetting::get('razorpay_mode'));
    }
}

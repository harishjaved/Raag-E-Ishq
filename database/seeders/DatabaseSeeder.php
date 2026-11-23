<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventSetting;
use App\Models\TicketCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed RBAC Users
        $defaultPassword = Hash::make('Password123!');

        User::updateOrCreate(
            ['email' => 'superadmin@joygivers.org'],
            [
                'name' => 'Joygivers Super Admin',
                'password' => $defaultPassword,
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@joygivers.org'],
            [
                'name' => 'Joygivers Event Manager',
                'password' => $defaultPassword,
                'role' => User::ROLE_EVENT_MANAGER,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'finance@joygivers.org'],
            [
                'name' => 'Joygivers Finance Officer',
                'password' => $defaultPassword,
                'role' => User::ROLE_FINANCE,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'gate@joygivers.org'],
            [
                'name' => 'Joygivers Gate Scanner',
                'password' => $defaultPassword,
                'role' => User::ROLE_GATE_STAFF,
                'is_active' => true,
            ]
        );

        // 2. Seed Default Event
        $event = Event::updateOrCreate(
            ['slug' => 'sagar-kinare-2026'],
            [
                'name' => 'Raage-E-Ishq',
                'description' => 'A celebration of timeless Hindi melodies in Kochi, to support child girls.',
                'venue_name' => 'Chavara Hall',
                'venue_address' => 'Chavara Cultural Centre, Near South Railway Station, Kochi, Kerala',
                'event_start_at' => Carbon::create(2026, 12, 17, 19, 0, 0),
                'event_end_at' => Carbon::create(2026, 12, 17, 22, 0, 0),
                'total_capacity' => 500,
                'is_published' => false,
                'live_payments_enabled' => false,
                'hold_duration_minutes' => 1440,
            ]
        );

        // 3. Seed Ticket Categories (100 @ 2500, 100 @ 1500, 250 @ 1000, 50 @ 2 = 500 total)
        TicketCategory::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Tier 1'],
            [
                'display_name' => 'Tier 1 (₹2,500)',
                'description' => 'Premium front seating area.',
                'price_paise' => 250000,
                'capacity' => 100,
                'display_order' => 1,
                'is_active' => true,
            ]
        );

        TicketCategory::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Tier 2'],
            [
                'display_name' => 'Tier 2 (₹1,500)',
                'description' => 'Prime central seating area.',
                'price_paise' => 150000,
                'capacity' => 100,
                'display_order' => 2,
                'is_active' => true,
            ]
        );

        TicketCategory::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Tier 3'],
            [
                'display_name' => 'Tier 3 (₹1,000)',
                'description' => 'General musical hall seating area.',
                'price_paise' => 100000,
                'capacity' => 250,
                'display_order' => 3,
                'is_active' => true,
            ]
        );

        TicketCategory::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Tier 4'],
            [
                'display_name' => 'Tier 4 Test (₹2)',
                'description' => 'Test admission pass for live UPI testing.',
                'price_paise' => 200,
                'capacity' => 50,
                'display_order' => 4,
                'is_active' => true,
            ]
        );

        // 4. Seed Event Settings
        EventSetting::set('hold_duration_minutes', '10', 'Default inventory hold TTL in minutes');
        EventSetting::set('support_email', 'events@joygivers.org', 'Support contact for ticket enquiries');
        EventSetting::set('support_phone', '+91 98765 43210', 'Support phone line');
        EventSetting::set('razorpay_mode', 'test', 'Gateway mode: test or live');
        EventSetting::set('ticket_hmac_secret', 'sagar_kinare_hmac_production_key_nonce_secret_2026', 'HMAC admission QR signing secret');
    }
}

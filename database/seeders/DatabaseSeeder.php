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
        // 1. Seed RBAC Users (never expose default password in UI; configurable via env)
        $defaultPassword = Hash::make((string) env('SEED_STAFF_PASSWORD', 'Password123!'));

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
                'name' => 'Raag-E-Ishq',
                'description' => 'An evening celebrating timeless Hindi melodies with a live orchestra, in support of girls’ education.',
                'venue_name' => 'Chavara Hall',
                'venue_address' => 'Chavara Cultural Centre, Near South Railway Station, Kochi, Kerala',
                'event_start_at' => Carbon::create(2027, 1, 17, 19, 0, 0),
                'event_end_at' => Carbon::create(2027, 1, 17, 22, 0, 0),
                'total_capacity' => 450,
                'is_published' => false,
                'live_payments_enabled' => false,
                'hold_duration_minutes' => 30,
            ]
        );

        // 3. Upgrade any legacy Tier 1/2/3 category names in-place and ensure Gold / Silver / Bronze exist
        $categoryDefinitions = [
            'Tier 1' => [
                'name' => 'Gold',
                'display_name' => 'Gold (₹2,500)',
                'description' => 'Premium front seating',
                'price_paise' => 250000,
                'capacity' => 100,
                'display_order' => 1,
                'is_active' => true,
            ],
            'Tier 2' => [
                'name' => 'Silver',
                'display_name' => 'Silver (₹1,500)',
                'description' => 'Central seating',
                'price_paise' => 150000,
                'capacity' => 100,
                'display_order' => 2,
                'is_active' => true,
            ],
            'Tier 3' => [
                'name' => 'Bronze',
                'display_name' => 'Bronze (₹1,000)',
                'description' => 'General seating',
                'price_paise' => 100000,
                'capacity' => 250,
                'display_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($categoryDefinitions as $legacyName => $attrs) {
            $existingNew = TicketCategory::where('event_id', $event->id)->where('name', $attrs['name'])->first();
            $existingLegacy = TicketCategory::where('event_id', $event->id)->where('name', $legacyName)->first();

            if ($existingLegacy && !$existingNew) {
                $existingLegacy->update($attrs);
            } else {
                TicketCategory::updateOrCreate(
                    ['event_id' => $event->id, 'name' => $attrs['name']],
                    $attrs
                );
            }
        }

        // 4. Seed Event Settings (30-minute hold countdown window)
        EventSetting::set('hold_duration_minutes', '30', 'Temporary seat hold window in minutes while awaiting payment');
        EventSetting::set('support_email', 'events@joygivers.org', 'Support contact for ticket enquiries');
        EventSetting::set('support_phone', '+91 7736606506', 'Support phone line');
        EventSetting::set('razorpay_mode', 'test', 'Gateway mode: test or live');
        EventSetting::set('ticket_hmac_secret', 'sagar_kinare_hmac_production_key_nonce_secret_2026', 'HMAC admission QR signing secret');
    }
}

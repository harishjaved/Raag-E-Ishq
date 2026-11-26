<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JoygiversLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_joygivers_standalone_files_and_assets_exist(): void
    {
        $this->assertFileExists(base_path('joygivers-website/index.html'));
        $this->assertFileExists(base_path('joygivers-website/images/joygivers-logo.jpg'));
        $this->assertFileExists(base_path('joygivers-website/images/joygivers-icon.jpg'));
        $this->assertFileExists(base_path('joygivers-website/images/girl-education-hero.jpg'));
        $this->assertFileExists(base_path('joygivers-website/images/raag-e-ishq-hero.jpg'));

        $this->get('/joygivers/images/joygivers-logo.jpg')->assertOk();
        $this->get('/joygivers/images/joygivers-icon.jpg')->assertOk();
        $this->get('/joygivers/images/girl-education-hero.jpg')->assertOk();
        $this->get('/joygivers/images/raag-e-ishq-hero.jpg')->assertOk();

        // Guard against directory traversal
        $this->get('/joygivers/images/..%2Findex.html')->assertNotFound();
    }

    public function test_unified_raag_e_ishq_landing_page_renders_all_10_sections_on_root_and_event(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        // 1. Slim Nav & Hero Section
        $response->assertSee('Joygivers');
        $response->assertSee('Raag-E-Ishq');
        $response->assertSee('Kuldeep Sagar');
        $response->assertSee('Timeless melodies. Brighter tomorrows.');
        $response->assertSee('17 January 2027 (Sunday)');
        $response->assertSee('7:00 PM Onwards');
        $response->assertSee('Chavara Hall, Kochi');
        $response->assertSee('Reserve Your Seats');
        $response->assertSee('Support the Cause');
        $response->assertSee('Unable to attend? Support the cause');

        // 2-4. Musical Experience, Programme & The Cause
        $response->assertSee('A Musical Journey Through Timeless Hindi Melodies');
        $response->assertSee('One evening for you.');
        $response->assertSee('A brighter tomorrow for her.');
        $response->assertSee('100% of net proceeds go directly towards girls’ education initiatives.');

        // 5-6. Tickets (Gold/Silver/Bronze, no .00, no raw 450 counter) & Simplified Booking Form
        $response->assertSee('Gold');
        $response->assertSee('Silver');
        $response->assertSee('Bronze');
        $response->assertSee('₹2,500');
        $response->assertSee('₹1,500');
        $response->assertSee('₹1,000');
        $response->assertDontSee('₹2,500.00');
        $response->assertDontSee('450 seats available');
        $response->assertSee('Select a ticket category');
        $response->assertSee('Request Reservation');

        // 7-10. Standalone Donate Module, About Joygivers, Sponsors & FAQs / Calendar
        $response->assertSee('₹500');
        $response->assertSee('₹5,000');
        $response->assertSee('About Joygivers');
        $response->assertSee('Concert Partners &amp; Sponsors', false);
        $response->assertSee(route('event.calendar'), false);

        // Legacy /joygivers URL redirects cleanly to root (/)
        $this->get('/joygivers')->assertRedirect(route('home'));
    }

    public function test_inline_json_booking_submission_creates_real_booking(): void
    {
        $this->seed(DatabaseSeeder::class);

        $category = \App\Models\TicketCategory::orderBy('display_order')->firstOrFail();

        $response = $this->postJson('/register', [
            'full_name' => 'Meera Nair',
            'email' => 'meera@example.com',
            'phone' => '9876543210',
            'ticket_category_id' => $category->id,
            'quantity' => 2,
            'attending' => 'yes',
            'privacy_consent' => 1,
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'status',
            'booking_reference',
            'total_amount_paise',
            'is_attending',
            'payment_url',
            'message',
        ]);

        $this->assertDatabaseHas('bookings', [
            'booking_reference' => $response->json('booking_reference'),
            'total_amount_paise' => $category->price_paise * 2,
        ]);
    }

    public function test_raag_e_ishq_ticketing_page_at_event_route_remains_intact(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/event');

        $response->assertOk();
        $response->assertSee('Raag-E-Ishq');
        $response->assertSee('Request Reservation');
        $response->assertSee(route('home'));
    }
}

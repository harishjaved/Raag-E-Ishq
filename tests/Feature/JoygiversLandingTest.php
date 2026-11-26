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

    public function test_joygivers_book_now_opens_modal_and_more_details_links_to_event_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        // Branding Logo & Slide 1
        $response->assertSee('/joygivers/images/joygivers-logo.jpg');
        $response->assertSee('/joygivers/images/joygivers-icon.jpg');
        $response->assertSee('Empowering Her Future');
        $response->assertSee('Through Education');

        // Slide 2: More Details -> eventPageUrl (/event) | Book Now -> openBookingModal()
        $response->assertSee('Joygivers Presents:');
        $response->assertSee('Raag-E-Ishq');
        $response->assertSee('More Details');
        $response->assertSee('data-event-page-link', false);
        $response->assertSee('eventPageUrl', false);
        $response->assertSee('/event', false);
        $response->assertSee('Book Now');
        $response->assertSee('openBookingModal()');

        // Legacy /joygivers URL redirects cleanly to root (/)
        $this->get('/joygivers')->assertRedirect(route('home'));

        // Screenshot 1 Style Booking Modal with Interactive Tiers & 3-step Form/Confirmation
        $response->assertSee('booking-modal');
        $response->assertSee('modal-step-1');
        $response->assertSee('modal-step-2');
        $response->assertSee('modal-step-3');
        $response->assertSee('modal-booking-form');
        $response->assertSee('17th January, 7:00 PM IST');
        $response->assertSee('Chavara Hall, Kochi');
        $response->assertSee('Charitable Supporter');
        $response->assertSee('+91 77366 06506');
        $response->assertDontSee("addEventListener('mouseenter', pauseCarousel)", false);
        $response->assertDontSee("statusEl.textContent = 'Paused'", false);
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
        $response->assertSee('Submit Booking Enquiry');
        $response->assertSee(route('home'));
    }
}

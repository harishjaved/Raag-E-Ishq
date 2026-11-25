<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Checkin;
use App\Models\Contact;
use App\Models\Event;
use App\Models\InventoryHold;
use App\Models\PaymentAttempt;
use App\Models\Ticket;
use App\Models\TicketAllocation;
use App\Models\TicketCategory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function getDashboardMetrics(): array
    {
        $event = Event::where('slug', 'sagar-kinare-2026')->first();
        if (!$event) {
            return [];
        }

        $totalContacts = Contact::count();
        $totalBookings = Booking::count();

        $pendingBookings = Booking::whereIn('status', [
            Booking::STATUS_PAYMENT_PENDING,
            Booking::STATUS_PENDING,
            Booking::STATUS_IN_PROCESS,
        ])->count();
        $paidBookings = Booking::where('status', Booking::STATUS_CONFIRMED)->count();

        $paidTicketsIssued = Ticket::whereHas('booking', function ($q) {
            $q->where('status', Booking::STATUS_CONFIRMED)
              ->where('is_attending', true)
              ->where('total_amount_paise', '>', 0);
        })->whereIn('status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])->count();

        $admittableTicketsTotal = Ticket::whereHas('booking', function ($q) {
            $q->where('status', Booking::STATUS_CONFIRMED)
              ->where('is_attending', true);
        })->whereIn('status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])->count();

        $compsIssued = TicketAllocation::sum('quantity');

        $totalCheckedIn = Checkin::count();

        $grossCollectionsPaise = Booking::where('status', Booking::STATUS_CONFIRMED)
            ->sum('total_amount_paise');

        $donationCollectionsPaise = Booking::where('status', Booking::STATUS_CONFIRMED)
            ->where('is_donation', true)
            ->sum('total_amount_paise');

        $ticketCollectionsPaise = Booking::where('status', Booking::STATUS_CONFIRMED)
            ->where('is_donation', false)
            ->sum('total_amount_paise');

        $donationBookingsCount = Booking::where('status', Booking::STATUS_CONFIRMED)
            ->where('is_donation', true)
            ->count();

        $globalRemaining = $this->inventoryService->getGlobalRemainingCapacity($event);
        $totalAllocated = $event->total_capacity - $globalRemaining;

        $showupDenominator = max($paidTicketsIssued, $admittableTicketsTotal);
        $showupRate = $showupDenominator > 0
            ? min(100.0, round(($totalCheckedIn / $showupDenominator) * 100, 1))
            : 0.0;

        $conversionRate = $totalBookings > 0
            ? round(($paidBookings / $totalBookings) * 100, 1)
            : 0.0;

        // Sales and inventory breakdown by category
        $now = Carbon::now();
        $categoryBreakdown = [];
        $categories = $event->categories()->orderBy('display_order')->get();
        foreach ($categories as $cat) {
            $soldTickets = Ticket::where('ticket_category_id', $cat->id)
                ->whereHas('booking', function ($q) {
                    $q->where('status', Booking::STATUS_CONFIRMED)
                      ->where('is_attending', true)
                      ->where('total_amount_paise', '>', 0);
                })
                ->whereIn('status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])
                ->count();
            $compTickets = TicketAllocation::where('ticket_category_id', $cat->id)->sum('quantity');
            $heldTickets = InventoryHold::where('ticket_category_id', $cat->id)
                ->whereHas('booking', function ($q) {
                    $q->where('is_attending', true);
                })
                ->where('status', InventoryHold::STATUS_ACTIVE)
                ->where('expires_at', '>', $now)
                ->sum('quantity');
            $available = $this->inventoryService->getAvailableCapacity($cat);
            $categoryRevenuePaise = (int) BookingItem::where('ticket_category_id', $cat->id)
                ->whereHas('booking', function ($q) {
                    $q->where('status', Booking::STATUS_CONFIRMED);
                })
                ->sum('subtotal_paise');

            $categoryBreakdown[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'price_paise' => $cat->price_paise,
                'capacity' => $cat->capacity,
                'sold' => $soldTickets,
                'comps' => $compTickets + $heldTickets,
                'available' => $available,
                'revenue_paise' => $categoryRevenuePaise,
            ];
        }

        // Campaign attribution by UTM source
        $campaignAttribution = Contact::select(
            DB::raw("COALESCE(utm_source, 'Direct / Organic') as source"),
            DB::raw('count(distinct contacts.id) as contacts_count'),
            DB::raw('count(distinct bookings.id) as bookings_count'),
            DB::raw("sum(case when bookings.status = 'CONFIRMED' then bookings.total_amount_paise else 0 end) as verified_revenue_paise")
        )
        ->leftJoin('bookings', 'contacts.id', '=', 'bookings.contact_id')
        ->groupBy('source')
        ->orderByDesc('verified_revenue_paise')
        ->get();

        return [
            'total_contacts' => $totalContacts,
            'total_bookings' => $totalBookings,
            'pending_bookings' => $pendingBookings,
            'paid_bookings' => $paidBookings,
            'paid_tickets_issued' => $paidTicketsIssued,
            'comps_issued' => $compsIssued,
            'total_checked_in' => $totalCheckedIn,
            'gross_collections_paise' => $grossCollectionsPaise,
            'gross_collections_rupees' => number_format($grossCollectionsPaise / 100, 2),
            'ticket_collections_paise' => $ticketCollectionsPaise,
            'ticket_collections_rupees' => number_format($ticketCollectionsPaise / 100, 2),
            'donation_collections_paise' => $donationCollectionsPaise,
            'donation_collections_rupees' => number_format($donationCollectionsPaise / 100, 2),
            'donation_bookings_count' => $donationBookingsCount,
            'global_remaining' => $globalRemaining,
            'total_allocated' => $totalAllocated,
            'total_capacity' => $event->total_capacity,
            'showup_rate' => $showupRate,
            'conversion_rate' => $conversionRate,
            'category_breakdown' => $categoryBreakdown,
            'campaign_attribution' => $campaignAttribution,
        ];
    }
}

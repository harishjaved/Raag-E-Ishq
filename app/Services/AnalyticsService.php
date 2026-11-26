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

        // Single aggregate query across all booking KPI counters & collections
        $bookingAgg = Booking::query()
            ->selectRaw('COUNT(*) as total_bookings')
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN 1 ELSE 0 END), 0) as pending_bookings', [
                Booking::STATUS_PAYMENT_PENDING,
                Booking::STATUS_PENDING,
                Booking::STATUS_IN_PROCESS,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as paid_bookings', [
                Booking::STATUS_CONFIRMED,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN total_amount_paise ELSE 0 END), 0) as gross_collections_paise', [
                Booking::STATUS_CONFIRMED,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? AND is_donation = TRUE THEN total_amount_paise ELSE 0 END), 0) as donation_collections_paise', [
                Booking::STATUS_CONFIRMED,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? AND is_donation = FALSE THEN total_amount_paise ELSE 0 END), 0) as ticket_collections_paise', [
                Booking::STATUS_CONFIRMED,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? AND is_donation = TRUE THEN 1 ELSE 0 END), 0) as donation_bookings_count', [
                Booking::STATUS_CONFIRMED,
            ])
            ->first();

        $totalBookings = (int) ($bookingAgg->total_bookings ?? 0);
        $pendingBookings = (int) ($bookingAgg->pending_bookings ?? 0);
        $paidBookings = (int) ($bookingAgg->paid_bookings ?? 0);
        $grossCollectionsPaise = (int) ($bookingAgg->gross_collections_paise ?? 0);
        $donationCollectionsPaise = (int) ($bookingAgg->donation_collections_paise ?? 0);
        $ticketCollectionsPaise = (int) ($bookingAgg->ticket_collections_paise ?? 0);
        $donationBookingsCount = (int) ($bookingAgg->donation_bookings_count ?? 0);

        // Single grouped ticket query across categories (paid + total admittable)
        $ticketAggByCat = Ticket::query()
            ->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->whereIn('tickets.status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])
            ->where('bookings.status', Booking::STATUS_CONFIRMED)
            ->where('bookings.is_attending', true)
            ->groupBy('tickets.ticket_category_id')
            ->selectRaw('tickets.ticket_category_id')
            ->selectRaw('COUNT(*) as admittable_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN bookings.total_amount_paise > 0 THEN 1 ELSE 0 END), 0) as paid_count')
            ->get()
            ->keyBy('ticket_category_id');

        $paidTicketsIssued = (int) $ticketAggByCat->sum('paid_count');
        $admittableTicketsTotal = (int) $ticketAggByCat->sum('admittable_count');

        $allocationsByCat = TicketAllocation::query()
            ->groupBy('ticket_category_id')
            ->selectRaw('ticket_category_id, COALESCE(SUM(quantity), 0) as total_quantity')
            ->pluck('total_quantity', 'ticket_category_id');

        $compsIssued = (int) $allocationsByCat->sum();

        $totalCheckedIn = Checkin::count();

        $categories = $event->categories()->orderBy('display_order')->get();
        $event->setRelation('categories', $categories);

        $inventorySnapshot = $this->inventoryService->getEventInventorySnapshot($event);
        $globalRemaining = $inventorySnapshot['global_remaining'];
        $totalAllocated = $event->total_capacity - $globalRemaining;

        $showupDenominator = max($paidTicketsIssued, $admittableTicketsTotal);
        $showupRate = $showupDenominator > 0
            ? min(100.0, round(($totalCheckedIn / $showupDenominator) * 100, 1))
            : 0.0;

        $conversionRate = $totalBookings > 0
            ? round(($paidBookings / $totalBookings) * 100, 1)
            : 0.0;

        // Batched category revenue across confirmed bookings
        $categoryIds = $categories->pluck('id');
        $revenueByCat = BookingItem::query()
            ->whereIn('ticket_category_id', $categoryIds)
            ->whereHas('booking', function ($q) {
                $q->where('status', Booking::STATUS_CONFIRMED);
            })
            ->groupBy('ticket_category_id')
            ->selectRaw('ticket_category_id, COALESCE(SUM(subtotal_paise), 0) as total')
            ->pluck('total', 'ticket_category_id');

        // Sales and inventory breakdown by category
        $categoryBreakdown = [];
        foreach ($categories as $cat) {
            $soldTickets = (int) ($ticketAggByCat[$cat->id]->paid_count ?? 0);
            $compTickets = (int) ($allocationsByCat[$cat->id] ?? 0);
            $heldTickets = (int) ($inventorySnapshot['per_category'][$cat->id]['active_holds'] ?? 0);
            $available = (int) ($inventorySnapshot['per_category'][$cat->id]['available'] ?? $cat->capacity);
            $categoryRevenuePaise = (int) ($revenueByCat[$cat->id] ?? 0);

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

<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Checkin;
use App\Models\Contact;
use App\Models\Donation;
use App\Models\Event;
use App\Models\InventoryHold;
use App\Models\Ticket;
use App\Models\TicketAllocation;
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
        $confirmedStatuses = [
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_BOOKING_CONFIRMED,
            Booking::STATUS_TICKETS_ISSUED,
        ];

        $bookingAgg = Booking::query()
            ->selectRaw('COUNT(*) as total_bookings')
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?, ?) THEN 1 ELSE 0 END), 0) as pending_bookings', [
                Booking::STATUS_REQUEST_SUBMITTED,
                Booking::STATUS_PAYMENT_PENDING,
                Booking::STATUS_PENDING,
                Booking::STATUS_IN_PROCESS,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END), 0) as payment_submitted_bookings', [
                Booking::STATUS_PAYMENT_SUBMITTED,
                Booking::STATUS_VERIFYING_PAYMENT,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as expired_bookings', [
                Booking::STATUS_HOLD_EXPIRED,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as cancelled_bookings', [
                Booking::STATUS_CANCELLED,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN 1 ELSE 0 END), 0) as paid_bookings', $confirmedStatuses)
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN total_amount_paise ELSE 0 END), 0) as gross_collections_paise', $confirmedStatuses)
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) AND is_donation = TRUE THEN total_amount_paise ELSE 0 END), 0) as donation_collections_paise', $confirmedStatuses)
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) AND is_donation = FALSE THEN total_amount_paise ELSE 0 END), 0) as ticket_collections_paise', $confirmedStatuses)
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) AND is_donation = TRUE THEN 1 ELSE 0 END), 0) as donation_bookings_count', $confirmedStatuses)
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) AND is_offline_booking = TRUE THEN 1 ELSE 0 END), 0) as offline_bookings_count', $confirmedStatuses)
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) AND is_offline_booking = TRUE THEN total_amount_paise ELSE 0 END), 0) as offline_collections_paise', $confirmedStatuses)
            ->first();

        $totalBookings = (int) ($bookingAgg->total_bookings ?? 0);
        $pendingBookings = (int) ($bookingAgg->pending_bookings ?? 0);
        $paymentSubmittedBookings = (int) ($bookingAgg->payment_submitted_bookings ?? 0);
        $expiredBookings = (int) ($bookingAgg->expired_bookings ?? 0);
        $cancelledBookings = (int) ($bookingAgg->cancelled_bookings ?? 0);
        $paidBookings = (int) ($bookingAgg->paid_bookings ?? 0);
        $grossCollectionsPaise = (int) ($bookingAgg->gross_collections_paise ?? 0);
        $donationCollectionsPaise = (int) ($bookingAgg->donation_collections_paise ?? 0);
        $ticketCollectionsPaise = (int) ($bookingAgg->ticket_collections_paise ?? 0);
        $donationBookingsCount = (int) ($bookingAgg->donation_bookings_count ?? 0);
        $offlineBookingsCount = (int) ($bookingAgg->offline_bookings_count ?? 0);
        $offlineCollectionsPaise = (int) ($bookingAgg->offline_collections_paise ?? 0);

        // Standalone Support-the-Cause donations (decoupled from seat inventory)
        $standaloneAgg = Donation::query()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as acknowledged_count', [Donation::STATUS_ACKNOWLEDGED])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN amount_paise ELSE 0 END), 0) as acknowledged_paise', [Donation::STATUS_ACKNOWLEDGED])
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?) THEN amount_paise ELSE 0 END), 0) as pending_paise', [Donation::STATUS_PENDING_PAYMENT, Donation::STATUS_PAYMENT_SUBMITTED])
            ->first();

        $standaloneDonationsCount = (int) ($standaloneAgg->total_count ?? 0);
        $standaloneAcknowledgedCount = (int) ($standaloneAgg->acknowledged_count ?? 0);
        $standaloneAcknowledgedPaise = (int) ($standaloneAgg->acknowledged_paise ?? 0);
        $standalonePendingPaise = (int) ($standaloneAgg->pending_paise ?? 0);

        // Ticket counts by category
        $ticketAggByCat = Ticket::query()
            ->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->whereIn('tickets.status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])
            ->whereIn('bookings.status', $confirmedStatuses)
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

        $allocByType = TicketAllocation::query()
            ->groupBy('allocation_type')
            ->selectRaw('allocation_type, COALESCE(SUM(quantity), 0) as total_quantity')
            ->pluck('total_quantity', 'allocation_type');

        $compsIssued = (int) $allocationsByCat->sum();
        $sponsorSeatsCount = (int) ($allocByType[TicketAllocation::TYPE_SPONSOR] ?? 0);
        $compSeatsCount = (int) ($allocByType[TicketAllocation::TYPE_COMPLIMENTARY] ?? 0);
        $organiserHoldSeatsCount = (int) ($allocByType[TicketAllocation::TYPE_ORGANISER_HOLD] ?? 0);

        $activeHeldSeats = (int) InventoryHold::query()
            ->where('status', InventoryHold::STATUS_ACTIVE)
            ->where('expires_at', '>', Carbon::now())
            ->whereHas('booking', fn ($q) => $q->where('is_attending', true))
            ->sum('quantity');

        $expiredSeatsReleased = (int) BookingItem::query()
            ->whereHas('booking', fn ($q) => $q->where('status', Booking::STATUS_HOLD_EXPIRED)->where('is_attending', true))
            ->sum('quantity');

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

        $categoryIds = $categories->pluck('id');
        $revenueByCat = BookingItem::query()
            ->whereIn('ticket_category_id', $categoryIds)
            ->whereHas('booking', fn ($q) => $q->whereIn('status', $confirmedStatuses))
            ->groupBy('ticket_category_id')
            ->selectRaw('ticket_category_id, COALESCE(SUM(subtotal_paise), 0) as total')
            ->pluck('total', 'ticket_category_id');

        $targetFullCapacityPaise = (int) $categories->sum(fn ($cat) => $cat->price_paise * $cat->capacity);

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
                'held' => $heldTickets,
                'comps_only' => $compTickets,
                'comps' => $compTickets + $heldTickets,
                'available' => $available,
                'revenue_paise' => $categoryRevenuePaise,
            ];
        }

        $campaignAttribution = Contact::select(
            DB::raw("COALESCE(utm_source, 'Direct / Organic') as source"),
            DB::raw('count(distinct contacts.id) as contacts_count'),
            DB::raw('count(distinct bookings.id) as bookings_count'),
            DB::raw("sum(case when bookings.status in ('CONFIRMED', 'BOOKING_CONFIRMED', 'TICKETS_ISSUED') then bookings.total_amount_paise else 0 end) as verified_revenue_paise")
        )
        ->leftJoin('bookings', 'contacts.id', '=', 'bookings.contact_id')
        ->groupBy('source')
        ->orderByDesc('verified_revenue_paise')
        ->get();

        $combinedDonationsPaise = $donationCollectionsPaise + $standaloneAcknowledgedPaise;

        return [
            'total_contacts' => $totalContacts,
            'total_bookings' => $totalBookings,
            'pending_bookings' => $pendingBookings,
            'payment_submitted_bookings' => $paymentSubmittedBookings,
            'expired_bookings' => $expiredBookings,
            'cancelled_bookings' => $cancelledBookings,
            'active_held_seats' => $activeHeldSeats,
            'expired_seats_released' => $expiredSeatsReleased,
            'paid_bookings' => $paidBookings,
            'paid_tickets_issued' => $paidTicketsIssued,
            'comps_issued' => $compsIssued,
            'sponsor_seats_count' => $sponsorSeatsCount,
            'comp_seats_count' => $compSeatsCount,
            'organiser_hold_seats_count' => $organiserHoldSeatsCount,
            'offline_bookings_count' => $offlineBookingsCount,
            'offline_collections_paise' => $offlineCollectionsPaise,
            'offline_collections_rupees' => number_format($offlineCollectionsPaise / 100, 0),
            'total_checked_in' => $totalCheckedIn,
            'gross_collections_paise' => $grossCollectionsPaise,
            'gross_collections_rupees' => number_format($grossCollectionsPaise / 100, 2),
            'ticket_collections_paise' => $ticketCollectionsPaise,
            'ticket_collections_rupees' => number_format($ticketCollectionsPaise / 100, 2),
            'donation_collections_paise' => $donationCollectionsPaise,
            'donation_collections_rupees' => number_format($donationCollectionsPaise / 100, 2),
            'donation_bookings_count' => $donationBookingsCount,
            'standalone_donations_count' => $standaloneDonationsCount,
            'standalone_donations_paise' => $standaloneAcknowledgedPaise + $standalonePendingPaise,
            'standalone_donations_acknowledged_paise' => $standaloneAcknowledgedPaise,
            'standalone_acknowledged_count' => $standaloneAcknowledgedCount,
            'standalone_acknowledged_paise' => $standaloneAcknowledgedPaise,
            'standalone_pending_paise' => $standalonePendingPaise,
            'combined_donations_paise' => $combinedDonationsPaise,
            'combined_donations_rupees' => number_format($combinedDonationsPaise / 100, 0),
            'target_full_capacity_paise' => $targetFullCapacityPaise,
            'target_full_capacity_rupees' => number_format($targetFullCapacityPaise / 100, 0),
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

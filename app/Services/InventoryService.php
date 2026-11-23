<?php

namespace App\Services;

use App\Exceptions\OutOfStockException;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Contact;
use App\Models\Event;
use App\Models\EventSetting;
use App\Models\InventoryHold;
use App\Models\Ticket;
use App\Models\TicketAllocation;
use App\Models\TicketCategory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryService
{
    /**
     * Compute real-time remaining capacity for a specific category within a transaction or snapshot.
     */
    public function getAvailableCapacity(TicketCategory $category): int
    {
        // 1. Issued/checked-in valid tickets
        $issuedCount = Ticket::where('ticket_category_id', $category->id)
            ->whereIn('status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])
            ->count();

        // 2. Active, unexpired inventory holds
        $activeHoldsCount = InventoryHold::where('ticket_category_id', $category->id)
            ->where('status', InventoryHold::STATUS_ACTIVE)
            ->where('expires_at', '>', Carbon::now())
            ->sum('quantity');

        // 3. Sponsor, complimentary, and organiser allocations
        $allocationsCount = TicketAllocation::where('ticket_category_id', $category->id)
            ->sum('quantity');

        $allocatedTotal = $issuedCount + $activeHoldsCount + $allocationsCount;
        $remaining = $category->capacity - $allocatedTotal;

        return max(0, $remaining);
    }

    /**
     * Compute real-time global remaining capacity across the entire 450 capacity pool.
     */
    public function getGlobalRemainingCapacity(Event $event): int
    {
        $categoryIds = $event->categories()->pluck('id');

        $issuedCount = Ticket::whereIn('ticket_category_id', $categoryIds)
            ->whereIn('status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])
            ->count();

        $activeHoldsCount = InventoryHold::whereIn('ticket_category_id', $categoryIds)
            ->where('status', InventoryHold::STATUS_ACTIVE)
            ->where('expires_at', '>', Carbon::now())
            ->sum('quantity');

        $allocationsCount = TicketAllocation::whereIn('ticket_category_id', $categoryIds)
            ->sum('quantity');

        $totalAllocated = $issuedCount + $activeHoldsCount + $allocationsCount;
        $remaining = $event->total_capacity - $totalAllocated;

        return max(0, $remaining);
    }

    /**
     * Atomically create a booking, contact, items, and inventory hold under row-level locking.
     * Prevents 10+ concurrent buyers from overselling even on the last seat.
     *
     * @throws OutOfStockException
     */
    public function createBookingWithHold(array $data, ?string $ipAddress = null, ?string $userAgent = null): Booking
    {
        $holdTtlMinutes = (int) EventSetting::get('hold_duration_minutes', 10);
        $holdExpiresAt = Carbon::now()->addMinutes($holdTtlMinutes);

        return DB::transaction(function () use ($data, $holdExpiresAt, $ipAddress, $userAgent) {
            $category = TicketCategory::where('id', $data['ticket_category_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $event = $category->event()->lockForUpdate()->firstOrFail();
            $requestedQuantity = (int) $data['quantity'];

            // 1. Verify category capacity under exclusive lock
            $categoryAvailable = $this->getAvailableCapacity($category);
            if ($categoryAvailable < $requestedQuantity) {
                throw new OutOfStockException(
                    "Only {$categoryAvailable} ticket(s) remaining in {$category->display_name}.",
                    $categoryAvailable
                );
            }

            // 2. Verify global shared event capacity (450 ceiling) under lock
            $globalAvailable = $this->getGlobalRemainingCapacity($event);
            if ($globalAvailable < $requestedQuantity) {
                throw new OutOfStockException(
                    "Only {$globalAvailable} ticket(s) remaining for the entire event.",
                    $globalAvailable
                );
            }

            // 3. Create or update contact record (do not merge bookings, only contact info)
            $contact = Contact::updateOrCreate(
                [
                    'email' => strtolower(trim($data['email'])),
                    'phone' => preg_replace('/[^0-9]/', '', $data['phone']),
                ],
                [
                    'full_name' => trim($data['full_name']),
                    'country_code' => $data['country_code'] ?? '+91',
                    'marketing_opt_in' => (bool) ($data['marketing_opt_in'] ?? false),
                    'utm_source' => $data['utm_source'] ?? null,
                    'utm_medium' => $data['utm_medium'] ?? null,
                    'utm_campaign' => $data['utm_campaign'] ?? null,
                    'utm_content' => $data['utm_content'] ?? null,
                ]
            );

            // 4. Server computes exact price in paise - CLIENT NEVER SUPPLIES PRICE
            $unitPricePaise = $category->price_paise;
            $totalAmountPaise = $unitPricePaise * $requestedQuantity;

            // 5. Generate collision-resistant unique booking reference
            $bookingReference = $this->generateUniqueBookingReference();

            // 6. Create booking
            $booking = Booking::create([
                'booking_reference' => $bookingReference,
                'event_id' => $event->id,
                'contact_id' => $contact->id,
                'total_amount_paise' => $totalAmountPaise,
                'currency' => 'INR',
                'status' => Booking::STATUS_PAYMENT_PENDING,
                'hold_expires_at' => $holdExpiresAt,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            // 7. Create booking item
            BookingItem::create([
                'booking_id' => $booking->id,
                'ticket_category_id' => $category->id,
                'quantity' => $requestedQuantity,
                'unit_price_paise' => $unitPricePaise,
                'subtotal_paise' => $totalAmountPaise,
            ]);

            // 8. Create inventory hold
            InventoryHold::create([
                'booking_id' => $booking->id,
                'ticket_category_id' => $category->id,
                'quantity' => $requestedQuantity,
                'expires_at' => $holdExpiresAt,
                'status' => InventoryHold::STATUS_ACTIVE,
            ]);

            // 9. Audit log the booking creation
            AuditLog::log(
                action: 'BOOKING_HOLD_CREATED',
                targetType: 'Booking',
                targetId: $booking->id,
                newValues: [
                    'booking_reference' => $bookingReference,
                    'category' => $category->name,
                    'quantity' => $requestedQuantity,
                    'total_amount_paise' => $totalAmountPaise,
                    'hold_expires_at' => $holdExpiresAt->toIso8601String(),
                ]
            );

            return $booking;
        });
    }

    /**
     * Sweep and release expired inventory holds, updating associated bookings.
     */
    public function releaseExpiredHolds(): int
    {
        $now = Carbon::now();
        $expiredHolds = InventoryHold::where('status', InventoryHold::STATUS_ACTIVE)
            ->where('expires_at', '<=', $now)
            ->get();

        $count = 0;
        foreach ($expiredHolds as $hold) {
            DB::transaction(function () use ($hold, &$count) {
                $hold->update(['status' => InventoryHold::STATUS_RELEASED]);

                $booking = $hold->booking()->lockForUpdate()->first();
                if ($booking && $booking->status === Booking::STATUS_PAYMENT_PENDING) {
                    $booking->update([
                        'status' => Booking::STATUS_HOLD_EXPIRED,
                        'cancellation_reason' => 'Inventory hold expired without verified payment',
                    ]);

                    AuditLog::log(
                        action: 'BOOKING_HOLD_EXPIRED',
                        targetType: 'Booking',
                        targetId: $booking->id,
                        oldValues: ['status' => Booking::STATUS_PAYMENT_PENDING],
                        newValues: ['status' => Booking::STATUS_HOLD_EXPIRED]
                    );
                }
                $count++;
            });
        }

        return $count;
    }

    protected function generateUniqueBookingReference(): string
    {
        $datePrefix = date('Ymd');
        do {
            $random = strtoupper(Str::random(5));
            $reference = "SK-{$datePrefix}-{$random}";
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }
}

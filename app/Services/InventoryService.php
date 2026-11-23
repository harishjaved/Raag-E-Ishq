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
use App\Models\PaymentAttempt;
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
        // 1. Issued/checked-in valid tickets for attending bookings
        $issuedCount = Ticket::where('ticket_category_id', $category->id)
            ->whereHas('booking', function ($q) {
                $q->where('is_attending', true);
            })
            ->whereIn('status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])
            ->count();

        // 2. Active, unexpired inventory holds for attending bookings
        $activeHoldsCount = InventoryHold::where('ticket_category_id', $category->id)
            ->whereHas('booking', function ($q) {
                $q->where('is_attending', true);
            })
            ->where('status', InventoryHold::STATUS_ACTIVE)
            ->where('expires_at', '>', Carbon::now())
            ->sum('quantity');

        // 3. Sponsor, complimentary, and organiser unissued reserve holds
        $allocationsCount = TicketAllocation::where('ticket_category_id', $category->id)
            ->whereNull('booking_id')
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
            ->whereHas('booking', function ($q) {
                $q->where('is_attending', true);
            })
            ->whereIn('status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])
            ->count();

        $activeHoldsCount = InventoryHold::whereIn('ticket_category_id', $categoryIds)
            ->whereHas('booking', function ($q) {
                $q->where('is_attending', true);
            })
            ->where('status', InventoryHold::STATUS_ACTIVE)
            ->where('expires_at', '>', Carbon::now())
            ->sum('quantity');

        $allocationsCount = TicketAllocation::whereIn('ticket_category_id', $categoryIds)
            ->whereNull('booking_id')
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
        $holdTtlMinutes = (int) EventSetting::get('hold_duration_minutes', 1440);
        $holdExpiresAt = Carbon::now()->addMinutes($holdTtlMinutes);

        $isAttending = !isset($data['attending']) || in_array(strtolower((string)$data['attending']), ['yes', 'true', '1'], true);

        return DB::transaction(function () use ($data, $isAttending, $holdExpiresAt, $ipAddress, $userAgent) {
            $category = TicketCategory::where('id', $data['ticket_category_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $event = $category->event()->lockForUpdate()->firstOrFail();
            $requestedQuantity = (int) $data['quantity'];

            // 1 & 2. Only check & lock physical capacity if attending
            if ($isAttending) {
                $categoryAvailable = $this->getAvailableCapacity($category);
                if ($categoryAvailable < $requestedQuantity) {
                    throw new OutOfStockException(
                        "Only {$categoryAvailable} ticket(s) remaining in {$category->display_name}.",
                        $categoryAvailable
                    );
                }

                $globalAvailable = $this->getGlobalRemainingCapacity($event);
                if ($globalAvailable < $requestedQuantity) {
                    throw new OutOfStockException(
                        "Only {$globalAvailable} ticket(s) remaining for the entire event.",
                        $globalAvailable
                    );
                }
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
                'is_attending' => $isAttending,
                'is_donation' => !$isAttending,
                'total_amount_paise' => $totalAmountPaise,
                'currency' => 'INR',
                'status' => Booking::STATUS_PAYMENT_PENDING,
                'hold_expires_at' => $isAttending ? $holdExpiresAt : null,
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

            // 8. Create inventory hold ONLY if attending
            if ($isAttending) {
                InventoryHold::create([
                    'booking_id' => $booking->id,
                    'ticket_category_id' => $category->id,
                    'quantity' => $requestedQuantity,
                    'expires_at' => $holdExpiresAt,
                    'status' => InventoryHold::STATUS_ACTIVE,
                ]);
            }

            // 9. Audit log the booking creation
            AuditLog::log(
                action: $isAttending ? 'BOOKING_HOLD_CREATED' : 'DONATION_BOOKING_CREATED',
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
     * Atomically cancel a booking and release its inventory holds back to the available pool.
     */
    public function cancelBooking(Booking $booking, string $reason = 'Cancelled by administrator', ?int $userId = null): Booking
    {
        return DB::transaction(function () use ($booking, $reason, $userId) {
            $booking = Booking::where('id', $booking->id)->lockForUpdate()->firstOrFail();

            if ($booking->status === Booking::STATUS_CONFIRMED) {
                throw new \InvalidArgumentException('Confirmed booking with issued tickets cannot be directly cancelled here.');
            }

            $oldStatus = $booking->status;

            // Release all active holds
            InventoryHold::where('booking_id', $booking->id)
                ->where('status', InventoryHold::STATUS_ACTIVE)
                ->update(['status' => InventoryHold::STATUS_RELEASED]);

            // Update booking
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => Carbon::now(),
                'cancellation_reason' => $reason,
            ]);

            // Expire any pending payment attempts
            PaymentAttempt::where('booking_id', $booking->id)
                ->whereIn('status', [PaymentAttempt::STATUS_CREATED, PaymentAttempt::STATUS_AWAITING_PAYMENT])
                ->update(['status' => PaymentAttempt::STATUS_EXPIRED]);

            AuditLog::log(
                action: 'BOOKING_CANCELLED',
                targetType: 'Booking',
                targetId: $booking->id,
                oldValues: ['status' => $oldStatus],
                newValues: [
                    'status' => Booking::STATUS_CANCELLED,
                    'booking_reference' => $booking->booking_reference,
                    'reason' => $reason,
                ],
                userId: $userId
            );

            return $booking;
        });
    }

    /**
     * Sweep and release expired inventory holds, updating associated bookings and payment attempts.
     */
    public function releaseExpiredHolds(): int
    {
        $now = Carbon::now();
        $count = 0;

        // 1. Release active inventory holds that passed expires_at
        $expiredHolds = InventoryHold::where('status', InventoryHold::STATUS_ACTIVE)
            ->where('expires_at', '<=', $now)
            ->get();

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
                        newValues: [
                            'status' => Booking::STATUS_HOLD_EXPIRED,
                            'booking_reference' => $booking->booking_reference,
                            'reason' => '10-minute hold window elapsed without payment',
                        ]
                    );
                }
                $count++;
            });
        }

        // 2. Mark any pending bookings past hold_expires_at as HOLD_EXPIRED
        $expiredPendingBookings = Booking::where('status', Booking::STATUS_PAYMENT_PENDING)
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<=', $now)
            ->get();

        foreach ($expiredPendingBookings as $booking) {
            DB::transaction(function () use ($booking, &$count) {
                $booking->update([
                    'status' => Booking::STATUS_HOLD_EXPIRED,
                    'cancellation_reason' => 'Inventory hold expired without verified payment',
                ]);

                AuditLog::log(
                    action: 'BOOKING_HOLD_EXPIRED',
                    targetType: 'Booking',
                    targetId: $booking->id,
                    oldValues: ['status' => Booking::STATUS_PAYMENT_PENDING],
                    newValues: [
                        'status' => Booking::STATUS_HOLD_EXPIRED,
                        'booking_reference' => $booking->booking_reference,
                        'reason' => '10-minute hold window elapsed without payment',
                    ]
                );
                $count++;
            });
        }

        // 3. Mark any awaiting payment attempts past expires_at or on resolved bookings as EXPIRED
        $expiredPaymentAttempts = PaymentAttempt::with('booking')
            ->where('status', PaymentAttempt::STATUS_AWAITING_PAYMENT)
            ->where(function ($q) use ($now) {
                $q->where(function ($sub) use ($now) {
                    $sub->whereNotNull('expires_at')->where('expires_at', '<=', $now);
                })->orWhereHas('booking', function ($sub) {
                    $sub->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CANCELLED, Booking::STATUS_HOLD_EXPIRED]);
                });
            })
            ->get();

        foreach ($expiredPaymentAttempts as $payment) {
            DB::transaction(function () use ($payment, &$count) {
                $payment->update(['status' => PaymentAttempt::STATUS_EXPIRED]);

                AuditLog::log(
                    action: 'PAYMENT_EXPIRED',
                    targetType: 'PaymentAttempt',
                    targetId: $payment->id,
                    oldValues: ['status' => PaymentAttempt::STATUS_AWAITING_PAYMENT],
                    newValues: [
                        'status' => PaymentAttempt::STATUS_EXPIRED,
                        'booking_reference' => $payment->booking?->booking_reference,
                        'amount_rupees' => number_format($payment->amount_paise / 100, 2),
                        'reason' => '10-minute payment session expired without payment confirmation',
                    ]
                );
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

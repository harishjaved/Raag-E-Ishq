<?php

namespace App\Services;

use App\Exceptions\OutOfStockException;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Contact;
use App\Models\Donation;
use App\Models\Event;
use App\Models\EventSetting;
use App\Models\InventoryHold;
use App\Models\PaymentAttempt;
use App\Models\Ticket;
use App\Models\TicketAllocation;
use App\Models\TicketCategory;
use App\Models\User;
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
        $issuedCount = Ticket::where('ticket_category_id', $category->id)
            ->whereHas('booking', fn ($q) => $q->where('is_attending', true))
            ->whereIn('status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])
            ->count();

        $activeHoldsCount = InventoryHold::where('ticket_category_id', $category->id)
            ->whereHas('booking', fn ($q) => $q->where('is_attending', true))
            ->where('status', InventoryHold::STATUS_ACTIVE)
            ->where('expires_at', '>', Carbon::now())
            ->sum('quantity');

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
            ->whereHas('booking', fn ($q) => $q->where('is_attending', true))
            ->whereIn('status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])
            ->count();

        $activeHoldsCount = InventoryHold::whereIn('ticket_category_id', $categoryIds)
            ->whereHas('booking', fn ($q) => $q->where('is_attending', true))
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
     * Fetch batched inventory counts per category and global remaining capacity.
     *
     * @return array{
     *     per_category: array<int, array{issued: int, active_holds: int, unlinked_allocations: int, available: int}>,
     *     global_remaining: int,
     *     total_allocated: int
     * }
     */
    public function getEventInventorySnapshot(Event $event): array
    {
        $categories = $event->relationLoaded('categories')
            ? $event->categories
            : $event->categories()->orderBy('display_order')->get();

        $allCategoryIds = $event->categories()->pluck('id');
        if ($allCategoryIds->isEmpty()) {
            return [
                'per_category' => [],
                'global_remaining' => (int) $event->total_capacity,
                'total_allocated' => 0,
            ];
        }

        $now = Carbon::now();

        $issuedByCat = Ticket::query()
            ->selectRaw('ticket_category_id, COUNT(*) as total')
            ->whereIn('ticket_category_id', $allCategoryIds)
            ->whereHas('booking', fn ($q) => $q->where('is_attending', true))
            ->whereIn('status', [Ticket::STATUS_ISSUED, Ticket::STATUS_CHECKED_IN])
            ->groupBy('ticket_category_id')
            ->pluck('total', 'ticket_category_id');

        $holdsByCat = InventoryHold::query()
            ->selectRaw('ticket_category_id, COALESCE(SUM(quantity), 0) as total')
            ->whereIn('ticket_category_id', $allCategoryIds)
            ->whereHas('booking', fn ($q) => $q->where('is_attending', true))
            ->where('status', InventoryHold::STATUS_ACTIVE)
            ->where('expires_at', '>', $now)
            ->groupBy('ticket_category_id')
            ->pluck('total', 'ticket_category_id');

        $allocsByCat = TicketAllocation::query()
            ->selectRaw('ticket_category_id, COALESCE(SUM(quantity), 0) as total')
            ->whereIn('ticket_category_id', $allCategoryIds)
            ->whereNull('booking_id')
            ->groupBy('ticket_category_id')
            ->pluck('total', 'ticket_category_id');

        $perCategory = [];
        foreach ($categories as $cat) {
            $issued = (int) ($issuedByCat[$cat->id] ?? 0);
            $holds = (int) ($holdsByCat[$cat->id] ?? 0);
            $allocs = (int) ($allocsByCat[$cat->id] ?? 0);
            $perCategory[$cat->id] = [
                'issued' => $issued,
                'active_holds' => $holds,
                'unlinked_allocations' => $allocs,
                'available' => max(0, (int) $cat->capacity - ($issued + $holds + $allocs)),
            ];
        }

        $totalAllocated = (int) $issuedByCat->sum() + (int) $holdsByCat->sum() + (int) $allocsByCat->sum();

        return [
            'per_category' => $perCategory,
            'global_remaining' => max(0, (int) $event->total_capacity - $totalAllocated),
            'total_allocated' => $totalAllocated,
        ];
    }

    /**
     * Atomically create a reservation request & temporary seat hold (default 30 min) under row-level locking.
     *
     * @throws OutOfStockException
     */
    public function createBookingWithHold(array $data, ?string $ipAddress = null, ?string $userAgent = null): Booking
    {
        $holdTtlMinutes = max(1, (int) EventSetting::get('hold_duration_minutes', 30));
        $holdExpiresAt = Carbon::now()->addMinutes($holdTtlMinutes);

        $isAttending = !isset($data['attending']) || in_array(strtolower((string) $data['attending']), ['yes', 'true', '1'], true);

        return DB::transaction(function () use ($data, $isAttending, $holdExpiresAt, $ipAddress, $userAgent) {
            $category = TicketCategory::where('id', $data['ticket_category_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $event = $category->event()->lockForUpdate()->firstOrFail();
            $requestedQuantity = (int) $data['quantity'];

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

            $contact = $this->upsertContact($data);

            $unitPricePaise = $category->price_paise;
            $totalAmountPaise = $unitPricePaise * $requestedQuantity;
            $bookingReference = $this->generateUniqueBookingReference();

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
                'payment_channel' => 'UPI_ONLINE',
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            BookingItem::create([
                'booking_id' => $booking->id,
                'ticket_category_id' => $category->id,
                'quantity' => $requestedQuantity,
                'unit_price_paise' => $unitPricePaise,
                'subtotal_paise' => $totalAmountPaise,
            ]);

            if ($isAttending) {
                InventoryHold::create([
                    'booking_id' => $booking->id,
                    'ticket_category_id' => $category->id,
                    'quantity' => $requestedQuantity,
                    'expires_at' => $holdExpiresAt,
                    'status' => InventoryHold::STATUS_ACTIVE,
                ]);
            }

            AuditLog::log(
                action: $isAttending ? 'BOOKING_HOLD_CREATED' : 'DONATION_BOOKING_CREATED',
                targetType: 'Booking',
                targetId: $booking->id,
                newValues: [
                    'stage' => Booking::STATUS_REQUEST_SUBMITTED . ' -> ' . Booking::STATUS_PAYMENT_PENDING,
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
     * Allow authorized admins to enter offline / cash / bank / VIP bookings into the live 450-seat pool.
     *
     * @throws OutOfStockException
     */
    public function createOfflineBooking(array $data, User $adminUser, TicketService $ticketService, ?string $ipAddress = null): Booking
    {
        $holdTtlMinutes = max(1, (int) EventSetting::get('hold_duration_minutes', 30));
        $holdExpiresAt = Carbon::now()->addMinutes($holdTtlMinutes);
        $markPaid = !empty($data['mark_paid']);
        $channel = strtoupper((string) ($data['payment_channel'] ?? 'CASH'));

        return DB::transaction(function () use ($data, $adminUser, $ticketService, $holdExpiresAt, $markPaid, $channel, $ipAddress) {
            $category = TicketCategory::where('id', $data['ticket_category_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $event = $category->event()->lockForUpdate()->firstOrFail();
            $requestedQuantity = (int) $data['quantity'];

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

            $contact = $this->upsertContact($data);
            $unitPricePaise = $channel === 'COMPLIMENTARY' ? 0 : $category->price_paise;
            $totalAmountPaise = $unitPricePaise * $requestedQuantity;
            $bookingReference = $this->generateUniqueBookingReference();
            $utr = !empty($data['utr_reference']) ? trim((string) $data['utr_reference']) : null;
            $now = Carbon::now();

            $booking = Booking::create([
                'booking_reference' => $bookingReference,
                'event_id' => $event->id,
                'contact_id' => $contact->id,
                'is_attending' => true,
                'is_donation' => false,
                'total_amount_paise' => $totalAmountPaise,
                'currency' => 'INR',
                'status' => $markPaid ? Booking::STATUS_CONFIRMED : Booking::STATUS_PAYMENT_PENDING,
                'hold_expires_at' => $holdExpiresAt,
                'payment_submitted_at' => $markPaid ? $now : null,
                'payment_verified_at' => $markPaid ? $now : null,
                'confirmed_at' => $markPaid ? $now : null,
                'utr_reference' => $utr,
                'is_offline_booking' => true,
                'payment_channel' => $channel,
                'created_by_user_id' => $adminUser->id,
                'verified_by_user_id' => $markPaid ? $adminUser->id : null,
                'ip_address' => $ipAddress,
                'user_agent' => 'Back Office Manual Entry',
            ]);

            BookingItem::create([
                'booking_id' => $booking->id,
                'ticket_category_id' => $category->id,
                'quantity' => $requestedQuantity,
                'unit_price_paise' => $unitPricePaise,
                'subtotal_paise' => $totalAmountPaise,
            ]);

            InventoryHold::create([
                'booking_id' => $booking->id,
                'ticket_category_id' => $category->id,
                'quantity' => $requestedQuantity,
                'expires_at' => $holdExpiresAt,
                'status' => $markPaid ? InventoryHold::STATUS_CONVERTED : InventoryHold::STATUS_ACTIVE,
            ]);

            if ($markPaid) {
                PaymentAttempt::create([
                    'booking_id' => $booking->id,
                    'provider' => 'offline_desk',
                    'provider_payment_id' => $utr ?: ('OFFLINE-' . $bookingReference),
                    'amount_paise' => $totalAmountPaise,
                    'currency' => 'INR',
                    'method' => strtolower($channel),
                    'status' => PaymentAttempt::STATUS_CAPTURED,
                    'captured_at' => $now,
                ]);

                $ticketService->issueTicketsForBooking($booking);
            }

            AuditLog::log(
                action: 'OFFLINE_BOOKING_CREATED',
                targetType: 'Booking',
                targetId: $booking->id,
                newValues: [
                    'booking_reference' => $bookingReference,
                    'category' => $category->name,
                    'quantity' => $requestedQuantity,
                    'channel' => $channel,
                    'mark_paid' => $markPaid,
                    'utr_reference' => $utr,
                    'created_by' => $adminUser->name,
                ],
                userId: $adminUser->id
            );

            return $booking;
        });
    }

    /**
     * Create a standalone Support-the-Cause donation without reserving or deducting hall seats.
     */
    public function createStandaloneDonation(array $data, ?string $ipAddress = null): Donation
    {
        return DB::transaction(function () use ($data, $ipAddress) {
            $event = Event::where('slug', 'sagar-kinare-2026')->firstOrFail();
            $contact = $this->upsertContact($data);

            $amountRupees = (int) round((float) $data['amount_rupees']);
            $amountPaise = $amountRupees * 100;
            $utr = !empty($data['utr_reference']) ? trim((string) $data['utr_reference']) : null;
            $hasProof = $utr !== null && $utr !== '';

            $donation = Donation::create([
                'donation_reference' => Donation::generateUniqueReference(),
                'event_id' => $event->id,
                'contact_id' => $contact->id,
                'amount_paise' => $amountPaise,
                'currency' => 'INR',
                'status' => $hasProof ? Donation::STATUS_PAYMENT_SUBMITTED : Donation::STATUS_PENDING_PAYMENT,
                'payment_method' => strtoupper((string) ($data['payment_method'] ?? 'UPI')),
                'utr_reference' => $utr,
                'donor_note' => !empty($data['donor_note']) ? trim((string) $data['donor_note']) : null,
                'ip_address' => $ipAddress,
            ]);

            AuditLog::log(
                action: 'CAUSE_DONATION_CREATED',
                targetType: 'Donation',
                targetId: $donation->id,
                newValues: [
                    'donation_reference' => $donation->donation_reference,
                    'amount_paise' => $amountPaise,
                    'status' => $donation->status,
                    'utr_reference' => $utr,
                ]
            );

            return $donation;
        });
    }

    protected function upsertContact(array $data): Contact
    {
        return Contact::updateOrCreate(
            [
                'email' => strtolower(trim((string) $data['email'])),
                'phone' => preg_replace('/[^0-9]/', '', (string) $data['phone']),
            ],
            [
                'full_name' => trim((string) $data['full_name']),
                'country_code' => $data['country_code'] ?? '+91',
                'marketing_opt_in' => (bool) ($data['marketing_opt_in'] ?? false),
                'utm_source' => $data['utm_source'] ?? null,
                'utm_medium' => $data['utm_medium'] ?? null,
                'utm_campaign' => $data['utm_campaign'] ?? null,
                'utm_content' => $data['utm_content'] ?? null,
            ]
        );
    }

    /**
     * Atomically cancel a booking and release its inventory holds back to the available pool.
     */
    public function cancelBooking(Booking $booking, string $reason = 'Cancelled by administrator', ?int $userId = null): Booking
    {
        return DB::transaction(function () use ($booking, $reason, $userId) {
            $booking = Booking::where('id', $booking->id)->lockForUpdate()->firstOrFail();

            if ($booking->isConfirmed()) {
                throw new \InvalidArgumentException('Confirmed booking with issued tickets cannot be directly cancelled here.');
            }

            $oldStatus = $booking->status;

            InventoryHold::where('booking_id', $booking->id)
                ->where('status', InventoryHold::STATUS_ACTIVE)
                ->update(['status' => InventoryHold::STATUS_RELEASED]);

            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => Carbon::now(),
                'cancellation_reason' => $reason,
            ]);

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
        $ttlMinutes = max(1, (int) EventSetting::get('hold_duration_minutes', 30));
        $count = 0;

        $expiredHolds = InventoryHold::where('status', InventoryHold::STATUS_ACTIVE)
            ->where('expires_at', '<=', $now)
            ->whereHas('booking', fn ($q) => $q->whereIn('status', [
                Booking::STATUS_REQUEST_SUBMITTED,
                Booking::STATUS_PAYMENT_PENDING,
                Booking::STATUS_PENDING,
            ]))
            ->get();

        foreach ($expiredHolds as $hold) {
            DB::transaction(function () use ($hold, $ttlMinutes, &$count) {
                $hold->update(['status' => InventoryHold::STATUS_RELEASED]);

                $booking = $hold->booking()->lockForUpdate()->first();
                if ($booking && in_array($booking->status, [Booking::STATUS_REQUEST_SUBMITTED, Booking::STATUS_PAYMENT_PENDING, Booking::STATUS_PENDING], true)) {
                    $oldStatus = $booking->status;
                    $booking->update([
                        'status' => Booking::STATUS_HOLD_EXPIRED,
                        'cancellation_reason' => "Inventory hold expired after {$ttlMinutes} minutes without verified payment",
                    ]);

                    AuditLog::log(
                        action: 'BOOKING_HOLD_EXPIRED',
                        targetType: 'Booking',
                        targetId: $booking->id,
                        oldValues: ['status' => $oldStatus],
                        newValues: [
                            'status' => Booking::STATUS_HOLD_EXPIRED,
                            'booking_reference' => $booking->booking_reference,
                            'reason' => "{$ttlMinutes}-minute hold window elapsed without payment",
                        ]
                    );
                }
                $count++;
            });
        }

        $expiredPendingBookings = Booking::whereIn('status', [
                Booking::STATUS_REQUEST_SUBMITTED,
                Booking::STATUS_PAYMENT_PENDING,
                Booking::STATUS_PENDING,
            ])
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<=', $now)
            ->get();

        foreach ($expiredPendingBookings as $booking) {
            DB::transaction(function () use ($booking, $ttlMinutes, &$count) {
                $oldStatus = $booking->status;
                $booking->update([
                    'status' => Booking::STATUS_HOLD_EXPIRED,
                    'cancellation_reason' => "Inventory hold expired after {$ttlMinutes} minutes without verified payment",
                ]);

                AuditLog::log(
                    action: 'BOOKING_HOLD_EXPIRED',
                    targetType: 'Booking',
                    targetId: $booking->id,
                    oldValues: ['status' => $oldStatus],
                    newValues: [
                        'status' => Booking::STATUS_HOLD_EXPIRED,
                        'booking_reference' => $booking->booking_reference,
                        'reason' => "{$ttlMinutes}-minute hold window elapsed without payment",
                    ]
                );
                $count++;
            });
        }

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
            DB::transaction(function () use ($payment, $ttlMinutes, &$count) {
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
                        'reason' => "{$ttlMinutes}-minute payment session expired without payment confirmation",
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
            $reference = "REI-{$datePrefix}-{$random}";
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }
}

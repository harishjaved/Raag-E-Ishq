<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_REQUEST_SUBMITTED = 'REQUEST_SUBMITTED';
    public const STATUS_PAYMENT_PENDING = 'PAYMENT_PENDING';
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_IN_PROCESS = 'IN_PROCESS';
    public const STATUS_PAYMENT_SUBMITTED = 'PAYMENT_SUBMITTED';
    public const STATUS_VERIFYING_PAYMENT = 'VERIFYING_PAYMENT';
    public const STATUS_PAYMENT_VERIFIED = 'PAYMENT_VERIFIED';
    public const STATUS_BOOKING_CONFIRMED = 'BOOKING_CONFIRMED';
    public const STATUS_CONFIRMED = 'CONFIRMED';
    public const STATUS_TICKETS_ISSUED = 'TICKETS_ISSUED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_REFUNDED = 'REFUNDED';
    public const STATUS_RECONCILIATION_REQUIRED = 'RECONCILIATION_REQUIRED';
    public const STATUS_HOLD_EXPIRED = 'HOLD_EXPIRED';
    public const STATUS_PAYMENT_FAILED = 'PAYMENT_FAILED';

    protected $fillable = [
        'booking_reference',
        'event_id',
        'contact_id',
        'is_attending',
        'is_donation',
        'total_amount_paise',
        'currency',
        'status',
        'hold_expires_at',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
        'utr_reference',
        'payment_screenshot_reference',
        'payment_submitted_at',
        'payment_verified_at',
        'is_offline_booking',
        'payment_channel',
        'created_by_user_id',
        'verified_by_user_id',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'is_attending' => 'boolean',
            'is_donation' => 'boolean',
            'is_offline_booking' => 'boolean',
            'total_amount_paise' => 'integer',
            'hold_expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'payment_submitted_at' => 'datetime',
            'payment_verified_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public static function generateUniqueReference(): string
    {
        $datePrefix = date('Ymd');
        do {
            $random = strtoupper(Str::random(5));
            $reference = "REI-{$datePrefix}-{$random}";
        } while (self::where('booking_reference', $reference)->exists());

        return $reference;
    }

    public function inventoryHolds(): HasMany
    {
        return $this->hasMany(InventoryHold::class);
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function latestPaymentAttempt(): HasOne
    {
        return $this->hasOne(PaymentAttempt::class)->latestOfMany();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function emailDeliveries(): HasMany
    {
        return $this->hasMany(EmailDelivery::class);
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            self::STATUS_REQUEST_SUBMITTED,
            self::STATUS_PAYMENT_PENDING,
            self::STATUS_PENDING,
        ], true);
    }

    public function isInProcess(): bool
    {
        return $this->status === self::STATUS_IN_PROCESS;
    }

    public function isPaymentSubmitted(): bool
    {
        return in_array($this->status, [
            self::STATUS_PAYMENT_SUBMITTED,
            self::STATUS_VERIFYING_PAYMENT,
        ], true);
    }

    public function isConfirmed(): bool
    {
        return in_array($this->status, [
            self::STATUS_CONFIRMED,
            self::STATUS_BOOKING_CONFIRMED,
            self::STATUS_TICKETS_ISSUED,
        ], true);
    }

    public function isCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_HOLD_EXPIRED], true);
    }

    public function isHoldValid(): bool
    {
        return ($this->isPending() || $this->isInProcess() || $this->isPaymentSubmitted())
            && ($this->hold_expires_at === null || $this->hold_expires_at->isFuture());
    }

    public function getFormattedTotalAttribute(): string
    {
        $rupees = $this->total_amount_paise / 100;
        return '₹' . ($this->total_amount_paise % 100 === 0 ? number_format($rupees, 0) : number_format($rupees, 2));
    }

    /**
     * Structured 6-stage reservation lifecycle state for public & admin views.
     *
     * @return list<array{key: string, label: string, state: string}>
     */
    public function getLifecycleStagesAttribute(): array
    {
        $hasTickets = $this->relationLoaded('tickets') ? $this->tickets->isNotEmpty() : $this->tickets()->exists();
        $isConfirmed = $this->isConfirmed();
        $isVerified = $this->payment_verified_at !== null || $this->status === self::STATUS_PAYMENT_VERIFIED || $isConfirmed;
        $isSubmitted = $this->payment_submitted_at !== null || !empty($this->utr_reference) || $this->isPaymentSubmitted() || $isVerified;

        $activeKey = match (true) {
            $hasTickets || $this->status === self::STATUS_TICKETS_ISSUED => self::STATUS_TICKETS_ISSUED,
            $isConfirmed => self::STATUS_BOOKING_CONFIRMED,
            $this->status === self::STATUS_PAYMENT_VERIFIED => self::STATUS_PAYMENT_VERIFIED,
            $this->isPaymentSubmitted() => self::STATUS_PAYMENT_SUBMITTED,
            $this->isInProcess() || $this->status === self::STATUS_PAYMENT_PENDING => self::STATUS_PAYMENT_PENDING,
            default => self::STATUS_REQUEST_SUBMITTED,
        };

        $order = [
            self::STATUS_REQUEST_SUBMITTED => 'Request Submitted',
            self::STATUS_PAYMENT_PENDING => 'Payment Pending (30m Hold)',
            self::STATUS_PAYMENT_SUBMITTED => 'Payment Submitted',
            self::STATUS_PAYMENT_VERIFIED => 'Payment Verified',
            self::STATUS_BOOKING_CONFIRMED => 'Booking Confirmed',
            self::STATUS_TICKETS_ISSUED => 'Tickets Issued',
        ];

        $keys = array_keys($order);
        $activeIdx = array_search($activeKey, $keys, true);

        $stages = [];
        foreach ($keys as $idx => $key) {
            $state = 'pending';
            if ($key === self::STATUS_TICKETS_ISSUED && ($hasTickets || $this->status === self::STATUS_TICKETS_ISSUED)) {
                $state = 'completed';
            } elseif ($idx < $activeIdx) {
                $state = 'completed';
            } elseif ($idx === $activeIdx) {
                $state = ($key === self::STATUS_TICKETS_ISSUED && ($hasTickets || ($this->is_donation && $isConfirmed))) ? 'completed' : 'active';
            }

            if ($key === self::STATUS_PAYMENT_SUBMITTED && $isSubmitted && $idx < $activeIdx) {
                $state = 'completed';
            }

            $stages[] = [
                'key' => $key,
                'label' => $order[$key],
                'state' => $state,
            ];
        }

        return $stages;
    }

    /**
     * Filter bookings by reference, UTR reference, or contact fields, ranking direct matches first.
     */
    public function scopeSearchTerm(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $like = "%{$term}%";
        $phoneDigits = Contact::normalizeSearchPhoneDigits($term);
        $phoneLike = $phoneDigits ? "%{$phoneDigits}%" : $like;
        $isPgsql = $query->getConnection()->getDriverName() === 'pgsql';
        $likeOp = $isPgsql ? 'ilike' : 'like';
        $sqlLike = $isPgsql ? 'ILIKE' : 'LIKE';

        $query->where(function (Builder $q) use ($likeOp, $like, $phoneDigits, $phoneLike) {
            $q->where('booking_reference', $likeOp, $like)
              ->orWhere('utr_reference', $likeOp, $like)
              ->orWhereHas('contact', function (Builder $cq) use ($likeOp, $like, $phoneDigits, $phoneLike) {
                  $cq->where('full_name', $likeOp, $like)
                     ->orWhere('phone', $likeOp, $like)
                     ->orWhere('email', $likeOp, $like);

                  if ($phoneDigits !== null) {
                      $cq->orWhere('phone', $likeOp, $phoneLike);
                  }
              });
        });

        return $query->orderByRaw(
            "CASE
                WHEN booking_reference {$sqlLike} ? OR utr_reference {$sqlLike} ? THEN 0
                WHEN EXISTS (
                    SELECT 1 FROM contacts c
                    WHERE c.id = bookings.contact_id
                      AND (c.full_name {$sqlLike} ? OR c.phone {$sqlLike} ? OR c.phone {$sqlLike} ?)
                ) THEN 1
                ELSE 2
            END ASC",
            [$like, $like, $like, $like, $phoneLike]
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_PAYMENT_PENDING = 'PAYMENT_PENDING';
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_IN_PROCESS = 'IN_PROCESS';
    public const STATUS_VERIFYING_PAYMENT = 'VERIFYING_PAYMENT';
    public const STATUS_CONFIRMED = 'CONFIRMED';
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
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'is_attending' => 'boolean',
            'is_donation' => 'boolean',
            'total_amount_paise' => 'integer',
            'hold_expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public static function generateUniqueReference(): string
    {
        $datePrefix = date('Ymd');
        do {
            $random = strtoupper(\Illuminate\Support\Str::random(5));
            $reference = "SK-{$datePrefix}-{$random}";
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
        return in_array($this->status, [self::STATUS_PAYMENT_PENDING, self::STATUS_PENDING], true);
    }

    public function isInProcess(): bool
    {
        return $this->status === self::STATUS_IN_PROCESS;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_HOLD_EXPIRED], true);
    }

    public function isHoldValid(): bool
    {
        return ($this->isPending() || $this->isInProcess())
            && ($this->hold_expires_at === null || $this->hold_expires_at->isFuture());
    }

    public function getFormattedTotalAttribute(): string
    {
        return '₹' . number_format($this->total_amount_paise / 100, 2);
    }

    /**
     * Filter bookings by reference or contact fields, ranking direct matches before email substrings.
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

        $query->where(function (Builder $q) use ($like, $phoneDigits, $phoneLike) {
            $q->where('booking_reference', 'like', $like)
              ->orWhereHas('contact', function (Builder $cq) use ($like, $phoneDigits, $phoneLike) {
                  $cq->where('full_name', 'like', $like)
                     ->orWhere('phone', 'like', $like)
                     ->orWhere('email', 'like', $like);

                  if ($phoneDigits !== null) {
                      $cq->orWhere('phone', 'like', $phoneLike);
                  }
              });
        });

        return $query->orderByRaw(
            'CASE
                WHEN booking_reference LIKE ? THEN 0
                WHEN EXISTS (
                    SELECT 1 FROM contacts c
                    WHERE c.id = bookings.contact_id
                      AND (c.full_name LIKE ? OR c.phone LIKE ? OR c.phone LIKE ?)
                ) THEN 1
                ELSE 2
            END ASC',
            [$like, $like, $like, $phoneLike]
        );
    }
}

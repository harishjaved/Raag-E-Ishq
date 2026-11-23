<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_PAYMENT_PENDING = 'PAYMENT_PENDING';
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

    public function isHoldValid(): bool
    {
        return $this->status === self::STATUS_PAYMENT_PENDING
            && $this->hold_expires_at !== null
            && $this->hold_expires_at->isFuture();
    }

    public function getFormattedTotalAttribute(): string
    {
        return '₹' . number_format($this->total_amount_paise / 100, 2);
    }
}

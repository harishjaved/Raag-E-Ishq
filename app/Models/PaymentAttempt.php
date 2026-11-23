<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends Model
{
    public const STATUS_CREATED = 'CREATED';
    public const STATUS_AWAITING_PAYMENT = 'AWAITING_PAYMENT';
    public const STATUS_CAPTURED = 'CAPTURED';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_RECONCILIATION_REQUIRED = 'RECONCILIATION_REQUIRED';

    protected $fillable = [
        'booking_id',
        'provider',
        'provider_qr_id',
        'provider_order_id',
        'provider_payment_id',
        'amount_paise',
        'currency',
        'method',
        'qr_image_url',
        'qr_payload',
        'status',
        'expires_at',
        'captured_at',
        'error_code',
        'error_description',
    ];

    protected function casts(): array
    {
        return [
            'amount_paise' => 'integer',
            'expires_at' => 'datetime',
            'captured_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isCaptured(): bool
    {
        return $this->status === self::STATUS_CAPTURED;
    }

    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount_paise / 100, 2);
    }
}

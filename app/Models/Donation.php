<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Donation extends Model
{
    public const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';
    public const STATUS_PAYMENT_SUBMITTED = 'PAYMENT_SUBMITTED';
    public const STATUS_ACKNOWLEDGED = 'ACKNOWLEDGED';

    protected $fillable = [
        'donation_reference',
        'event_id',
        'contact_id',
        'amount_paise',
        'currency',
        'status',
        'payment_method',
        'utr_reference',
        'donor_note',
        'acknowledged_at',
        'verified_by_user_id',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'amount_paise' => 'integer',
            'acknowledged_at' => 'datetime',
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

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        $rupees = $this->amount_paise / 100;
        return '₹' . ($this->amount_paise % 100 === 0 ? number_format($rupees, 0) : number_format($rupees, 2));
    }

    public static function generateUniqueReference(): string
    {
        do {
            $reference = 'REI-DON-' . strtoupper(Str::random(5));
        } while (self::where('donation_reference', $reference)->exists());

        return $reference;
    }
}

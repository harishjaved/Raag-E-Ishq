<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    public const STATUS_ISSUED = 'ISSUED';
    public const STATUS_CHECKED_IN = 'CHECKED_IN';
    public const STATUS_VOID = 'VOID';
    public const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'booking_id',
        'booking_item_id',
        'ticket_category_id',
        'ticket_number',
        'qr_uuid',
        'qr_nonce',
        'attendee_name',
        'status',
        'issued_at',
        'voided_at',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(BookingItem::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function checkin(): HasOne
    {
        return $this->hasOne(Checkin::class);
    }

    public function isValidForAdmission(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isCheckedIn(): bool
    {
        return $this->status === self::STATUS_CHECKED_IN;
    }
}

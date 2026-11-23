<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryHold extends Model
{
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_RELEASED = 'RELEASED';
    public const STATUS_CONVERTED = 'CONVERTED';

    protected $fillable = [
        'booking_id',
        'ticket_category_id',
        'quantity',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}

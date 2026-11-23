<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'ticket_category_id',
        'quantity',
        'unit_price_paise',
        'subtotal_paise',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_paise' => 'integer',
            'subtotal_paise' => 'integer',
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
}

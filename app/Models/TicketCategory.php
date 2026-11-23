<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'display_name',
        'description',
        'price_paise',
        'capacity',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_paise' => 'integer',
            'capacity' => 'integer',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function inventoryHolds(): HasMany
    {
        return $this->hasMany(InventoryHold::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(TicketAllocation::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '₹' . number_format($this->price_paise / 100, 2);
    }
}

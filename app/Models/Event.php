<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'venue_name',
        'venue_address',
        'event_start_at',
        'event_end_at',
        'total_capacity',
        'is_published',
        'live_payments_enabled',
        'hold_duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'event_start_at' => 'datetime',
            'event_end_at' => 'datetime',
            'total_capacity' => 'integer',
            'is_published' => 'boolean',
            'live_payments_enabled' => 'boolean',
            'hold_duration_minutes' => 'integer',
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(TicketCategory::class)->orderBy('display_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(TicketAllocation::class);
    }
}

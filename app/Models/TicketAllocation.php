<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAllocation extends Model
{
    public const TYPE_COMPLIMENTARY = 'COMPLIMENTARY';
    public const TYPE_SPONSOR = 'SPONSOR';
    public const TYPE_ORGANISER_HOLD = 'ORGANISER_HOLD';

    protected $fillable = [
        'event_id',
        'ticket_category_id',
        'allocation_type',
        'quantity',
        'beneficiary_name',
        'notes',
        'created_by_user_id',
        'booking_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

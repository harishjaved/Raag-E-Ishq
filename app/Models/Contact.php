<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'country_code',
        'marketing_opt_in',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'marketing_opt_in' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Extract local phone digits when searching with +91, spaces, or dashes.
     */
    public static function normalizeSearchPhoneDigits(string $term): ?string
    {
        if (!preg_match('/^[\+\d\s\-()]{5,}$/', $term)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $term);
        if (strlen($digits) >= 11 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }

        return $digits !== '' ? $digits : null;
    }

    /**
     * Filter contacts by name, email, or phone and rank direct name/phone matches first.
     */
    public function scopeSearchTerm(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $like = "%{$term}%";
        $phoneDigits = self::normalizeSearchPhoneDigits($term);
        $phoneLike = $phoneDigits ? "%{$phoneDigits}%" : $like;

        $query->where(function (Builder $q) use ($like, $phoneDigits, $phoneLike) {
            $q->where('full_name', 'like', $like)
              ->orWhere('email', 'like', $like)
              ->orWhere('phone', 'like', $like);

            if ($phoneDigits !== null) {
                $q->orWhere('phone', 'like', $phoneLike);
            }
        });

        return $query->orderByRaw(
            'CASE WHEN full_name LIKE ? THEN 0 WHEN phone LIKE ? OR phone LIKE ? THEN 1 ELSE 2 END ASC',
            [$like, $like, $phoneLike]
        );
    }
}

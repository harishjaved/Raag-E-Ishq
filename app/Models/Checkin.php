<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkin extends Model
{
    public const METHOD_QR_SCAN = 'QR_SCAN';
    public const METHOD_MANUAL_LOOKUP = 'MANUAL_LOOKUP';

    protected $fillable = [
        'ticket_id',
        'scanned_by_user_id',
        'checked_in_at',
        'gate_identifier',
        'verification_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by_user_id');
    }
}

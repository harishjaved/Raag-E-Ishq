<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatewayWebhookEvent extends Model
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PROCESSED = 'PROCESSED';
    public const STATUS_DUPLICATE = 'DUPLICATE';
    public const STATUS_FAILED = 'FAILED';

    protected $fillable = [
        'provider',
        'provider_event_id',
        'event_type',
        'raw_payload',
        'signature',
        'processing_status',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }
}

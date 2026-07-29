<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationChannel extends Model
{
    protected $fillable = [
        'subscriber_id',
        'type',
        'destination',
        'settings',
        'verified',
        'enabled',
    ];

    protected $casts = [
        'destination' => 'encrypted',
        'settings' => 'array',
        'verified' => 'boolean',
        'enabled' => 'boolean',
    ];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'subscriber_id',
        'device_id',
        'endpoint',
        'p256dh',
        'auth',
        'expiration_time',
        'last_success_at',
        'last_failure_at',
        'failure_count',
    ];

    protected $casts = [
        'p256dh' => 'encrypted',
        'auth' => 'encrypted',
        'expiration_time' => 'datetime',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
    ];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}

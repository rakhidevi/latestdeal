<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscriber extends Model
{
    protected $fillable = [
        'email',
        'push_token',
        'status',
        'preferences',
        'is_active',
    ];

    protected $casts = [
        'preferences' => 'array',
        'is_active' => 'boolean',
    ];

    public function priceAlerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function notificationChannels(): HasMany
    {
        return $this->hasMany(NotificationChannel::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Helper to check if a subscriber has enabled a specific preference
     */
    public function prefers(string $category): bool
    {
        if (empty($this->preferences)) {
            return true; // Default to true if not customized
        }
        return $this->preferences[$category] ?? true;
    }
}

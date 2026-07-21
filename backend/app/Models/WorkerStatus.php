<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerStatus extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'last_success' => 'datetime',
        'last_seen' => 'datetime',
        'cpu_usage' => 'float',
        'ram_usage' => 'float',
        'disk_usage' => 'float',
        'queue_length' => 'integer',
        'jobs_today' => 'integer',
        'success_today' => 'integer',
        'failed_today' => 'integer',
        'retry_today' => 'integer',
        'uptime_seconds' => 'integer'
    ];

    /**
     * Determine the health/online status based on last heartbeat.
     * Online: < 60 sec
     * Delayed: 60 - 180 sec
     * Offline: > 180 sec
     */
    public function getHealthStatusAttribute(): string
    {
        if (!$this->last_seen) {
            return 'offline';
        }

        $diffInSeconds = now()->diffInSeconds($this->last_seen);

        if ($diffInSeconds < 60) {
            return 'online';
        } elseif ($diffInSeconds <= 180) {
            return 'delayed';
        }

        return 'offline';
    }
}

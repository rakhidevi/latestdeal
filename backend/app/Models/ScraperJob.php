<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScraperJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'status',
        'logs',
        'payload',
        'worker_id',
        'claimed_at',
        'heartbeat_at',
        'attempts',
        'max_attempts',
        'priority',
        'duration_seconds',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'logs' => 'array',
        'payload' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'claimed_at' => 'datetime',
        'heartbeat_at' => 'datetime',
    ];
}

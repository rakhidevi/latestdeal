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
        'duration_seconds',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'logs' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}

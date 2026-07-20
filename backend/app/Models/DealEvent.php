<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealEvent extends Model
{
    use HasFactory;

    public $timestamps = false; // Only created_at is handled by DB

    protected $fillable = [
        'deal_id',
        'event',
        'correlation_id',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
}

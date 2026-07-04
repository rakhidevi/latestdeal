<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceAlert extends Model
{
    protected $fillable = [
        'subscriber_id',
        'keyword',
        'target_price',
        'is_fulfilled'
    ];

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }
}

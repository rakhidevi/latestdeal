<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $fillable = [
        'email',
        'push_token',
        'is_active'
    ];

    public function priceAlerts()
    {
        return $this->hasMany(PriceAlert::class);
    }
}

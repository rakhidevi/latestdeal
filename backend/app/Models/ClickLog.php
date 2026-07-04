<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClickLog extends Model
{
    protected $table = 'clicks';

    protected $fillable = [
        'deal_id',
        'ip_address',
        'user_agent',
        'publisher_id',
        'publisher_integration_id',
        'is_bot'
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }
}

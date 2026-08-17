<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Watchlist extends Model
{
    protected $fillable = [
        'user_id',
        'watchable_id',
        'watchable_type',
    ];

    public function watchable(): MorphTo
    {
        return $this->morphTo();
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

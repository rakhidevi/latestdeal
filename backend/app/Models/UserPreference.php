<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'preferred_price_range',
        'preferred_categories',
        'preferred_brands',
        'preferred_discount',
        'preferred_providers',
        'preferred_language',
        'notification_preferences',
    ];

    protected $casts = [
        'preferred_price_range' => 'array',
        'preferred_categories' => 'array',
        'preferred_brands' => 'array',
        'preferred_providers' => 'array',
        'notification_preferences' => 'array',
        'preferred_discount' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name', 
        'slug',
        'deal_count',
        'average_discount',
        'trending_score',
        'average_ctr',
        'top_merchant_id',
        'total_clicks',
    ];
    protected static function booted()
    {
        $clearCache = function ($model) {
            \Illuminate\Support\Facades\Cache::forget('navigation_tree');
            \Illuminate\Support\Facades\Cache::forget('navigation_tree_v1');
        };
        static::saved($clearCache);
    }

    public function getIconAttribute(): string
    {
        $name = strtolower($this->name ?? '');
        $slug = strtolower($this->slug ?? '');

        if (str_contains($name, 'electronic') || str_contains($name, 'gadget') || str_contains($name, 'mobile') || str_contains($slug, 'tech')) {
            return '💻';
        }
        if (str_contains($name, 'fashion') || str_contains($name, 'apparel') || str_contains($name, 'cloth') || str_contains($name, 'shoe')) {
            return '👕';
        }
        if (str_contains($name, 'book')) {
            return '📚';
        }
        if (str_contains($name, 'course') || str_contains($name, 'education') || str_contains($name, 'learn')) {
            return '🎓';
        }
        if (str_contains($name, 'beauty') || str_contains($name, 'care') || str_contains($name, 'cosmetic')) {
            return '💄';
        }
        if (str_contains($name, 'home') || str_contains($name, 'kitchen') || str_contains($name, 'furniture')) {
            return '🏠';
        }
        if (str_contains($name, 'game') || str_contains($name, 'gaming')) {
            return '🎮';
        }
        if (str_contains($name, 'sport') || str_contains($name, 'fitness')) {
            return '🏋️';
        }
        if (str_contains($name, 'auto') || str_contains($name, 'car')) {
            return '🚗';
        }
        if (str_contains($name, 'general')) {
            return '🏷️';
        }

        return '🛍️';
    }

    public function getAverageDiscountAttribute($value): float
    {
        if (!empty($value) && (float)$value > 0) {
            return (float) $value;
        }
        $avg = Deal::where('category_id', $this->id)
            ->where('status', 'active')
            ->where('discount_percentage', '>', 0)
            ->avg('discount_percentage');

        return round($avg ?: 0, 1);
    }

    public function getTrendingScoreAttribute($value): int
    {
        if (!empty($value) && (int)$value > 0) {
            return (int) $value;
        }
        $avgAi = Deal::where('category_id', $this->id)->where('status', 'active')->avg('ai_score');
        $count = Deal::where('category_id', $this->id)->where('status', 'active')->count();

        return (int) round(($avgAi ?: 75) + min(15, $count * 2));
    }

    public function topMerchant()
    {
        return $this->belongsTo(Merchant::class, 'top_merchant_id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}

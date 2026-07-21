<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'is_active',
        'deal_count',
        'merchant_count',
        'product_count',
        'average_discount',
        'trending_score',
        'average_ctr',
        'total_clicks',
    ];
    protected static function booted()
    {
        $clearCache = function ($model) {
            \Illuminate\Support\Facades\Cache::forget('navigation_tree');
            \Illuminate\Support\Facades\Cache::forget('navigation_tree_v1');
        };
        static::saved($clearCache);
        static::deleted($clearCache);
    }

    public function getAverageDiscountAttribute($value): float
    {
        if (!empty($value) && (float)$value > 0) {
            return (float) $value;
        }
        $avg = Deal::where('brand_id', $this->id)
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
        $avgAi = Deal::where('brand_id', $this->id)->where('status', 'active')->avg('ai_score');
        $count = Deal::where('brand_id', $this->id)->where('status', 'active')->count();

        return (int) round(($avgAi ?: 75) + min(15, $count * 2));
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}

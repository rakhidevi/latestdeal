<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    protected $fillable = ['name', 'domain', 'store_id', 'affiliate_param_key', 'status', 'deal_count'];
    protected static function booted()
    {
        $clearCache = function ($model) {
            \Illuminate\Support\Facades\Cache::forget('navigation_tree');
            \Illuminate\Support\Facades\Cache::forget('navigation_tree_v1');
        };
        static::saved($clearCache);
        static::deleted($clearCache);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('status', true)
              ->orWhere('status', 1)
              ->orWhere('status', 'active');
        });
    }

    public function getIconAttribute(): string
    {
        $name = strtolower($this->name ?? '');

        if (str_contains($name, 'amazon')) {
            return '🟨';
        }
        if (str_contains($name, 'flipkart')) {
            return '🛒';
        }
        if (str_contains($name, 'udemy')) {
            return '🎓';
        }
        if (str_contains($name, 'myntra')) {
            return '👗';
        }
        if (str_contains($name, 'ajio')) {
            return '👟';
        }
        if (str_contains($name, 'croma') || str_contains($name, 'reliance')) {
            return '⚡';
        }

        return '🏪';
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}

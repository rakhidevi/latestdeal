<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscoveryProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand_id',
        'category_id',
        'product_type',
        'merchant_id',
        'min_discount_percent',
        'max_discount_percent',
        'min_price',
        'max_price',
        'keywords',
        'status',
        'run_interval',
        'next_run_at',
        'last_run_at',
        'last_run_status',
        'last_run_count',
        'last_error',
    ];

    protected $casts = [
        'min_discount_percent' => 'float',
        'max_discount_percent' => 'float',
        'min_price' => 'float',
        'max_price' => 'float',
        'run_interval' => 'integer',
        'last_run_count' => 'integer',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

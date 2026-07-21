<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Merchant;
use App\Models\Deal;

class BrandCounter
{
    public function recountAll(): array
    {
        $catCount = 0;
        foreach (Category::all() as $cat) {
            $catDeals = Deal::where('category_id', $cat->id)->where('status', 'active');
            $cat->deal_count = (clone $catDeals)->count();
            $cat->average_discount = round((clone $catDeals)->where('discount_percentage', '>', 0)->avg('discount_percentage') ?: 0, 1);
            $cat->trending_score = (int) round((clone $catDeals)->avg('ai_score') ?: 85);
            $cat->saveQuietly();
            $catCount++;
        }

        $brandCount = 0;
        foreach (Brand::all() as $brand) {
            $brandDeals = Deal::where('brand_id', $brand->id)->where('status', 'active');
            $brand->deal_count = (clone $brandDeals)->count();
            $brand->average_discount = round((clone $brandDeals)->where('discount_percentage', '>', 0)->avg('discount_percentage') ?: 0, 1);
            $brand->trending_score = (int) round((clone $brandDeals)->avg('ai_score') ?: 85);
            $brand->saveQuietly();
            $brandCount++;
        }

        $merchantCount = 0;
        foreach (Merchant::all() as $merchant) {
            $mDeals = Deal::where('merchant_id', $merchant->id)->where('status', 'active');
            $merchant->deal_count = (clone $mDeals)->count();
            $merchant->saveQuietly();
            $merchantCount++;
        }

        return [
            'categories' => $catCount,
            'brands' => $brandCount,
            'merchants' => $merchantCount,
        ];
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Merchant;

class NavigationService
{
    protected NavigationVersionManager $versionManager;

    public function __construct(NavigationVersionManager $versionManager)
    {
        $this->versionManager = $versionManager;
    }

    public function getNavigationTree()
    {
        $cacheKey = $this->versionManager->getCacheKey();

        return Cache::rememberForever($cacheKey, function () {
            $hasCatCount = \Illuminate\Support\Facades\Schema::hasColumn('categories', 'deal_count');
            $hasBrandCount = \Illuminate\Support\Facades\Schema::hasColumn('brands', 'deal_count');
            $hasMercCount = \Illuminate\Support\Facades\Schema::hasColumn('merchants', 'deal_count');

            // Get user-facing categories (excluding raw 'General' fallback)
            $catQuery = Category::where('slug', '!=', 'general')->where('name', '!=', 'General');
            if ($hasCatCount) {
                $catQuery->where('deal_count', '>', 0)->orderBy('deal_count', 'desc');
            }
            $categories = $catQuery->get();

            // Get active brands
            $brandQuery = Brand::where('is_active', true);
            if ($hasBrandCount) {
                $brandQuery->where('deal_count', '>', 0)->orderBy('deal_count', 'desc');
            }
            $brands = $brandQuery->limit(20)->get();

            // Get active merchants
            $mercQuery = Merchant::active();
            if ($hasMercCount) {
                $mercQuery->where('deal_count', '>', 0)->orderBy('deal_count', 'desc');
            }
            $merchants = $mercQuery->get();

            return [
                'categories' => $categories,
                'brands' => $brands,
                'merchants' => $merchants,
            ];
        });
    }
}

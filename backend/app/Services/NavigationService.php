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
            // Get user-facing categories (excluding raw 'General' fallback)
            $categories = Category::where('deal_count', '>', 0)
               ->where('slug', '!=', 'general')
               ->where('name', '!=', 'General')
               ->orderBy('deal_count', 'desc')
               ->get();

            // Get active brands
            $brands = Brand::where('is_active', true)
               ->where('deal_count', '>', 0)
               ->orderBy('deal_count', 'desc')
               ->limit(20)
               ->get();

            // Get active merchants
            $merchants = Merchant::active()
               ->where('deal_count', '>', 0)
               ->orderBy('deal_count', 'desc')
               ->get();

            return [
                'categories' => $categories,
                'brands' => $brands,
                'merchants' => $merchants,
            ];
        });
    }
}

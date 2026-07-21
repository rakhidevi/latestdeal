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

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && isset($cached['categories']) && $cached['categories']->isNotEmpty() && isset($cached['brands']) && $cached['brands']->isNotEmpty()) {
            return $cached;
        }

        $hasCatCount = \Illuminate\Support\Facades\Schema::hasColumn('categories', 'deal_count');
        $hasBrandCount = \Illuminate\Support\Facades\Schema::hasColumn('brands', 'deal_count');
        $hasMercCount = \Illuminate\Support\Facades\Schema::hasColumn('merchants', 'deal_count');

        // Get user-facing categories (excluding raw 'General' fallback)
        $catQuery = Category::where('slug', '!=', 'general')->where('name', '!=', 'General');
        if ($hasCatCount) {
            $catQuery->where('deal_count', '>', 0)->orderBy('deal_count', 'desc');
        }
        $categories = $catQuery->get();
        if ($categories->isEmpty()) {
            $categories = Category::where('slug', '!=', 'general')->where('name', '!=', 'General')->get();
        }

        // Get active brands
        $brandQuery = Brand::where('is_active', true);
        if ($hasBrandCount) {
            $brandQuery->where('deal_count', '>', 0)->orderBy('deal_count', 'desc');
        }
        $brands = $brandQuery->limit(20)->get();
        if ($brands->isEmpty()) {
            $brands = Brand::limit(20)->get();
        }

        // Get active merchants
        $mercQuery = Merchant::active();
        if ($hasMercCount) {
            $mercQuery->where('deal_count', '>', 0)->orderBy('deal_count', 'desc');
        }
        $merchants = $mercQuery->get();
        if ($merchants->isEmpty()) {
            $merchants = Merchant::active()->get();
        }

        $tree = [
            'categories' => $categories,
            'brands' => $brands,
            'merchants' => $merchants,
        ];

        if ($categories->isNotEmpty() || $brands->isNotEmpty() || $merchants->isNotEmpty()) {
            Cache::forever($cacheKey, $tree);
        }

        return $tree;
    }
}

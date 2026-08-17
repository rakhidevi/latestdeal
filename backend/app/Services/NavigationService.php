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
        // The cache is valid if it's a structured array, even if some collections are empty.
        if (is_array($cached) && isset($cached['categories'], $cached['brands'], $cached['merchants'])) {
            return $cached;
        }

        try {
            $hasCategoriesTable = \Illuminate\Support\Facades\Schema::hasTable('categories');
            $hasBrandsTable = \Illuminate\Support\Facades\Schema::hasTable('brands');
            $hasMerchantsTable = \Illuminate\Support\Facades\Schema::hasTable('merchants');
        } catch (\Throwable $e) {
            return [
                'categories' => collect(),
                'brands' => collect(),
                'merchants' => collect(),
            ];
        }

        if (!$hasCategoriesTable && !$hasBrandsTable && !$hasMerchantsTable) {
            return [
                'categories' => collect(),
                'brands' => collect(),
                'merchants' => collect(),
            ];
        }

        $hasCatCount = $hasCategoriesTable && \Illuminate\Support\Facades\Schema::hasColumn('categories', 'deal_count');
        $hasBrandCount = $hasBrandsTable && \Illuminate\Support\Facades\Schema::hasColumn('brands', 'deal_count');
        $hasMercCount = $hasMerchantsTable && \Illuminate\Support\Facades\Schema::hasColumn('merchants', 'deal_count');

        $categories = collect();
        if ($hasCategoriesTable) {
            $catQuery = Category::where('slug', '!=', 'general')->where('name', '!=', 'General');
            if ($hasCatCount) {
                // Prioritize categories with deals, then order by deal count. Single query.
                $catQuery->orderByRaw('deal_count > 0 DESC, deal_count DESC');
            }
            $categories = $catQuery->get();
        }

        $brands = collect();
        if ($hasBrandsTable) {
            $brandQuery = Brand::where('is_active', true);
            if ($hasBrandCount) {
                $brandQuery->orderByRaw('deal_count > 0 DESC, deal_count DESC');
            }
            $brands = $brandQuery->limit(20)->get();
        }

        $merchants = collect();
        if ($hasMerchantsTable) {
            $mercQuery = Merchant::active();
            if ($hasMercCount) {
                $mercQuery->orderByRaw('deal_count > 0 DESC, deal_count DESC');
            }
            $merchants = $mercQuery->get();
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

<?php

namespace App\Services\Catalog;

use App\Models\Deal;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;
use App\Services\NavigationVersionManager;

class CatalogIntegrityService
{
    protected NavigationVersionManager $versionManager;

    public function __construct(NavigationVersionManager $versionManager)
    {
        $this->versionManager = $versionManager;
    }

    public function getHealthReport(): array
    {
        $totalDeals = Deal::count();
        $activeDeals = Deal::where('status', 'active')->count();
        $dealsMissingBrand = Deal::whereNull('brand_id')->orWhere('needs_brand_review', true)->count();
        $dealsMissingCategory = Deal::whereNull('category_id')->count();
        $dealsMissingMerchant = Deal::whereNull('merchant_id')->count();

        $generalCat = Category::where('slug', 'general')->orWhere('name', 'General')->first();
        $dealsInGeneralCategory = $generalCat ? Deal::where('category_id', $generalCat->id)->count() : 0;

        $totalBrands = Brand::count();
        $activeBrands = Brand::where('deal_count', '>', 0)->count();
        $totalCategories = Category::count();
        $totalMerchants = Merchant::count();

        // Check for duplicate brand slugs
        $duplicateBrands = Brand::select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(id) > 1')
            ->count();

        return [
            'timestamp' => now()->toIso8601String(),
            'deals' => [
                'total' => $totalDeals,
                'active' => $activeDeals,
                'missing_brand_review' => $dealsMissingBrand,
                'missing_category' => $dealsMissingCategory,
                'needs_category_review' => $dealsInGeneralCategory,
                'missing_merchant' => $dealsMissingMerchant,
            ],
            'catalog' => [
                'total_brands' => $totalBrands,
                'active_brands' => $activeBrands,
                'total_categories' => $totalCategories,
                'total_merchants' => $totalMerchants,
                'duplicate_brands' => $duplicateBrands,
            ],
            'navigation_cache' => [
                'current_version' => $this->versionManager->getCurrentVersion(),
                'cache_key' => $this->versionManager->getCacheKey(),
                'status' => 'healthy'
            ]
        ];
    }
}

<?php

namespace App\Services\Search;

use App\Models\Deal;

class DealSearchService
{
    protected $rankingService;

    public function __construct(SearchRankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    public function search(SearchQuery $query)
    {
        // 1. Base Query: Only PUBLISHED deals
        $q = Deal::with(['brand', 'categories', 'productTypes'])
            ->where('editorial_status', 'PUBLISHED');

        $hasStructuredFilters = false;

        // 2. Apply structured filters
        if (!empty($query->brandIds)) {
            $q->whereIn('brand_id', $query->brandIds);
            $hasStructuredFilters = true;
        }

        if (!empty($query->minDiscount)) {
            // We assume Deal table will have effective_discount_percent or we fallback to discount_percentage
            // In the DB it might be saved as calculated_discount_percent or just discount_percentage
            // We use 'discount_percentage' for now as a reliable fallback, or check if column exists
            $q->where('discount_percentage', '>=', $query->minDiscount);
            $hasStructuredFilters = true;
        }

        if (!empty($query->maxPrice)) {
            // Assuming discounted_price represents the selling price
            $q->where('discounted_price', '<=', $query->maxPrice);
            $hasStructuredFilters = true;
        }

        // Categories (OR logic for structured fetch, ranking handles relevance)
        if (!empty($query->categoryIds)) {
            $catIds = $query->categoryIds;
            $q->where(function ($sub) use ($catIds) {
                $sub->whereIn('category_id', $catIds)
                    ->orWhereHas('categories', function ($pSub) use ($catIds) {
                        $pSub->whereIn('categories.id', $catIds);
                    });
            });
            $hasStructuredFilters = true;
        }

        // Product Types
        if (!empty($query->productTypeIds)) {
            $ptIds = $query->productTypeIds;
            $q->whereHas('productTypes', function ($sub) use ($ptIds) {
                $sub->whereIn('product_types.id', $ptIds);
            });
            $hasStructuredFilters = true;
        }

        // 3. Fallback / Residual Keyword full text search
        if (!empty($query->keywords)) {
            $keywordString = implode(' ', $query->keywords);
            
            // If we have structured filters, keywords are an AND condition.
            // If we have NO structured filters, keywords are the only condition.
            $q->where(function($sub) use ($keywordString) {
                // Simplified LIKE search for this iteration. 
                // In production with FULLTEXT index: $sub->whereRaw("MATCH(title, features) AGAINST(? IN BOOLEAN MODE)", [$keywordString]);
                $sub->where('title', 'like', "%{$keywordString}%")
                    ->orWhere('features', 'like', "%{$keywordString}%");
            });
        } elseif (!$hasStructuredFilters) {
            // If literally nothing was parsed (empty query), just return latest
            // (or empty, depending on requirements)
        }

        // Fetch max 100 results for ranking
        $deals = $q->take(100)->get();

        // 4. Rank results
        $rankedDeals = $this->rankingService->rank($deals, $query);

        return $rankedDeals;
    }
}

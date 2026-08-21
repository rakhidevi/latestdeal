<?php

namespace App\Services\Search;

use App\Models\Deal;

class SearchRankingService
{
    /**
     * Ranks a collection of deals based on how well they match the SearchQuery DTO.
     */
    public function rank($deals, SearchQuery $query)
    {
        $scoredDeals = $deals->map(function ($deal) use ($query) {
            $score = 0;

            // Brand Match
            if (!empty($query->brandIds) && in_array($deal->brand_id, $query->brandIds)) {
                $score += 100;
            }

            // Product Type Match
            if (!empty($query->productTypeIds)) {
                $dealProductTypeIds = $deal->productTypes->pluck('id')->toArray();
                if (!empty(array_intersect($query->productTypeIds, $dealProductTypeIds))) {
                    $score += 100;
                }
            }

            // Category Match
            if (!empty($query->categoryIds)) {
                $dealCategoryIds = $deal->categories->pluck('id')->toArray();
                $dealCategoryIds[] = $deal->category_id; // Include primary
                
                if (in_array($deal->category_id, $query->categoryIds)) {
                    $score += 70; // Primary category match
                } elseif (!empty(array_intersect($query->categoryIds, $dealCategoryIds))) {
                    $score += 50; // Secondary category match
                }
            }
            
            // Discount Relevance (small boost for higher discounts)
            if ($deal->calculated_discount_percent > 0) {
                $score += min(20, $deal->calculated_discount_percent / 5);
            }
            
            // Editorial Quality (small boost)
            if ($deal->ai_score > 80) {
                // Conceptually this is an internal 'editorial_quality_signal'
                $score += 20;
            }

            $deal->search_score = $score;
            return $deal;
        });

        // Sort by search_score descending, then by created_at descending
        return $scoredDeals->sortByDesc(function ($deal) {
            return sprintf('%06d-%s', $deal->search_score, $deal->created_at->timestamp);
        })->values();
    }
}

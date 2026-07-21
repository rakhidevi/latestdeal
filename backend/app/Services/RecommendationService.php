<?php

namespace App\Services;

use App\Models\Deal;
use Illuminate\Support\Facades\Cache;

class RecommendationService
{
    /**
     * Get generally trending deals across the platform.
     */
    public function getTrending(int $limit = 5)
    {
        return Cache::remember("recommendations_trending_{$limit}", 3600, function () use ($limit) {
            return Deal::where('status', 'active')
                ->orderByRaw('(discounted_price / original_price) ASC')
                ->orderBy('ai_score', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get deals people also viewed (mock implementation based on same category).
     */
    public function getPeopleAlsoViewed(Deal $deal, int $limit = 5)
    {
        return Cache::remember("recommendations_also_viewed_{$deal->id}_{$limit}", 3600, function () use ($deal, $limit) {
            return Deal::where('category_id', $deal->category_id)
                ->where('id', '!=', $deal->id)
                ->where('status', 'active')
                ->orderBy('ai_score', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Find cheaper alternatives to a given deal.
     */
    public function getCheaperAlternatives(Deal $deal, int $limit = 3)
    {
        return Deal::where('category_id', $deal->category_id)
            ->where('id', '!=', $deal->id)
            ->where('status', 'active')
            ->where('discounted_price', '<', $deal->discounted_price)
            ->orderBy('discounted_price', 'asc')
            ->limit($limit)
            ->get();
    }
}

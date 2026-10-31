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
        return Cache::remember("recommendations_trending_{$limit}", 300, function () use ($limit) {
            return Deal::where('status', 'active')
                ->where('original_price', '>', 0)
                ->where('created_at', '>=', now()->subDays(30))
                ->orderByRaw('(CASE WHEN original_price > 0 THEN (discounted_price / original_price) ELSE 1 END) ASC')
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

    /**
     * Retrieve relevant similar deals with semantic keyword & tag ranking,
     * price proximity, and honest category fallback labeling.
     *
     * @return array{deals: \Illuminate\Support\Collection, heading: string, is_truly_similar: bool}
     */
    public function getSimilarDeals(Deal $deal, int $limit = 4): array
    {
        // 1. Extract significant product keywords from title
        $titleKeywords = $this->extractProductKeywords($deal->title);

        // 2. Extract deal tags if available
        $dealTagIds = $deal->tags ? $deal->tags->pluck('id')->toArray() : [];

        // 3. Find candidate active deals
        $candidates = Deal::where('status', 'active')
            ->where('id', '!=', $deal->id)
            ->where(function ($q) use ($deal, $titleKeywords, $dealTagIds) {
                if ($deal->category_id) {
                    $q->where('category_id', $deal->category_id);
                }
                if ($deal->brand_id) {
                    $q->orWhere('brand_id', $deal->brand_id);
                }
                if (!empty($dealTagIds)) {
                    $q->orWhereHas('tags', function ($tq) use ($dealTagIds) {
                        $tq->whereIn('tags.id', $dealTagIds);
                    });
                }
                foreach ($titleKeywords as $kw) {
                    $q->orWhere('title', 'like', "%{$kw}%");
                }
            })
            ->with(['merchant', 'category', 'tags'])
            ->limit(30)
            ->get();

        if ($candidates->isEmpty()) {
            $fallback = Deal::where('status', 'active')
                ->where('id', '!=', $deal->id)
                ->orderByDesc('ai_score')
                ->limit($limit)
                ->get();

            return [
                'deals' => $fallback,
                'heading' => 'Trending Offers',
                'is_truly_similar' => false,
            ];
        }

        $dealPrice = (float)($deal->discounted_price ?: 1);

        // 4. Score each candidate
        $scored = $candidates->map(function ($candidate) use ($deal, $titleKeywords, $dealTagIds, $dealPrice) {
            $score = 0;
            $hasDirectMatch = false;

            // Shared tags: high weight (+35 per tag)
            if (!empty($dealTagIds)) {
                $candidateTagIds = $candidate->tags->pluck('id')->toArray();
                $sharedTags = array_intersect($dealTagIds, $candidateTagIds);
                if (!empty($sharedTags)) {
                    $score += count($sharedTags) * 35;
                    $hasDirectMatch = true;
                }
            }

            // Keyword match in candidate title: high weight (+25)
            $candidateTitleLower = strtolower($candidate->title);
            foreach ($titleKeywords as $kw) {
                if (str_contains($candidateTitleLower, $kw)) {
                    $score += 25;
                    $hasDirectMatch = true;
                }
            }

            // Same brand (+20)
            if ($deal->brand_id && $candidate->brand_id === $deal->brand_id) {
                $score += 20;
                $hasDirectMatch = true;
            }

            // Same category (+10)
            if ($deal->category_id && $candidate->category_id === $deal->category_id) {
                $score += 10;
            }

            // Price proximity penalty / bonus
            $cPrice = (float)($candidate->discounted_price ?: 1);
            $ratio = $cPrice / $dealPrice;
            if ($ratio >= 0.3 && $ratio <= 3.0) {
                $score += 15; // Comparable budget tier
            } elseif ($ratio > 8.0 || $ratio < 0.12) {
                $score -= 30; // Massive budget disparity
            }

            // Deal quality booster
            $score += (($candidate->ai_score ?? 70) / 10);

            return [
                'deal' => $candidate,
                'score' => $score,
                'has_direct_match' => $hasDirectMatch,
            ];
        });

        // Sort by score descending
        $sorted = $scored->sortByDesc('score');
        $topScored = $sorted->take($limit);

        // Determine honest heading
        $directMatchCount = $topScored->where('has_direct_match', true)->count();
        $isTrulySimilar = ($directMatchCount >= min(2, $topScored->count()));

        $categoryName = $deal->category->name ?? 'This Department';
        $heading = $isTrulySimilar 
            ? 'Compare Similar Deals' 
            : "More Deals in {$categoryName}";

        return [
            'deals' => $topScored->pluck('deal'),
            'heading' => $heading,
            'is_truly_similar' => $isTrulySimilar,
        ];
    }

    /**
     * Extract significant product nouns/keywords from a deal title.
     */
    private function extractProductKeywords(string $title): array
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', strtolower($title));
        $tokens = array_filter(explode(' ', $clean), fn($t) => strlen($t) >= 3);

        $stopWords = [
            'with', 'for', 'and', 'the', 'pack', 'set', 'pro', 'max', 'plus',
            'cool', 'white', 'black', 'blue', 'grey', 'inch', 'display',
            'storage', 'ram', 'deal', 'best', 'sale', 'new', 'latest',
            'door', 'litre', 'watt', 'adjustable', 'multi', 'rounder',
            'free', 'edition', 'ultra', 'fast', 'premium', 'high', 'low'
        ];

        $keywords = [];
        foreach ($tokens as $token) {
            if (!in_array($token, $stopWords, true) && !is_numeric($token)) {
                $keywords[] = $token;
            }
        }

        return array_slice(array_unique($keywords), 0, 5);
    }
}

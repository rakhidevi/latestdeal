<?php

namespace App\Services\SearchPipeline\Pipes;

use Closure;
use App\Services\SearchPipeline\SearchPayload;

class ApplyFilters
{
    public function handle(SearchPayload $payload, Closure $next)
    {
        $query = $payload->query;
        $filters = $payload->filters;

        // Text Search
        if (!empty($filters['q'])) {
            $query->where('title', 'like', '%' . $filters['q'] . '%');
        }

        // Category Filter
        if (!empty($filters['category_slug']) && $filters['category_slug'] !== 'all') {
            $query->whereHas('category', function($q) use ($filters) {
                $q->where('slug', $filters['category_slug']);
            });
        }

        // Brand Filter
        if (!empty($filters['brand_slug'])) {
            $query->whereHas('brand', function($q) use ($filters) {
                $q->where('slug', $filters['brand_slug']);
            });
        }

        // Merchant Filter
        if (!empty($filters['merchant_id'])) {
            $query->where('merchant_id', $filters['merchant_id']);
        } elseif (!empty($filters['merchant_slug'])) {
            $query->whereHas('merchant', function($q) use ($filters) {
                $q->where('name', 'like', $filters['merchant_slug']); 
            });
        }

        // Price Filters
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $query->where('discounted_price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $query->where('discounted_price', '<=', $filters['max_price']);
        }

        // Discount Range / Percentage Filters
        if (isset($filters['discount_min']) || isset($filters['discount_max'])) {
            $hasCol = \Illuminate\Support\Facades\Schema::hasColumn('deals', 'discount_percentage');
            
            if (isset($filters['discount_min']) && isset($filters['discount_max'])) {
                if ($hasCol) {
                    $query->whereBetween('discount_percentage', [$filters['discount_min'], $filters['discount_max']]);
                } else {
                    $query->whereRaw('(CASE WHEN original_price > 0 THEN ((original_price - discounted_price) / original_price * 100) ELSE 0 END) BETWEEN ? AND ?', [(float)$filters['discount_min'], (float)$filters['discount_max']]);
                }
            } elseif (isset($filters['discount_min'])) {
                if ($hasCol) {
                    $query->where('discount_percentage', '>=', $filters['discount_min']);
                } else {
                    $query->whereRaw('(CASE WHEN original_price > 0 THEN ((original_price - discounted_price) / original_price * 100) ELSE 0 END) >= ?', [(float)$filters['discount_min']]);
                }
            }
        }
        
        // Quality / Verification
        if (!empty($filters['verified']) || (isset($filters['min_trust_score']) && $filters['min_trust_score'] > 0)) {
            $score = $filters['min_trust_score'] ?? 75; // Bronze or better
            $query->where('ai_score', '>=', $score);
        }

        // Tags (e.g., 'ai-picks', 'trending', 'price-drops')
        if (!empty($filters['tag'])) {
            if (!in_array($filters['tag'], ['ai-picks', 'trending'])) { // Special tags handled by ranking or dedicated routes
                $query->whereHas('tags', function($q) use ($filters) {
                    $q->where('slug', $filters['tag']);
                });
            }
        }

        return $next($payload);
    }
}

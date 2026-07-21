<?php

namespace App\Services;

use App\Models\Deal;

class DealSearchService
{
    public function search(array $filters)
    {
        $query = Deal::where('status', 'active');

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
                $q->where('name', 'like', $filters['merchant_slug']); // use name for now if slug doesn't exist
            });
        }

        // Price Filters
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $query->where('discounted_price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $query->where('discounted_price', '<=', $filters['max_price']);
        }

        // Discount Filter (min_discount)
        if (isset($filters['min_discount']) && is_numeric($filters['min_discount'])) {
            $query->where('original_price', '>', 0)
                  ->whereRaw('((original_price - discounted_price) * 100.0 / original_price) >= ?', [$filters['min_discount']]);
        }

        // Discount Range (e.g., 25-49)
        if (!empty($filters['discount_range'])) {
            if (preg_match('/^(\d+)-(\d+)$/', $filters['discount_range'], $matches)) {
                $min = $matches[1];
                $max = $matches[2];
                $query->where('original_price', '>', 0)
                      ->whereRaw('((original_price - discounted_price) * 100.0 / original_price) >= ?', [$min])
                      ->whereRaw('((original_price - discounted_price) * 100.0 / original_price) <= ?', [$max]);
            } elseif (preg_match('/^(\d+)\+$/', $filters['discount_range'], $matches)) {
                $min = $matches[1];
                $query->where('original_price', '>', 0)
                      ->whereRaw('((original_price - discounted_price) * 100.0 / original_price) >= ?', [$min]);
            }
        }
        
        // Quality / Verification
        if (!empty($filters['verified']) || (isset($filters['min_trust_score']) && $filters['min_trust_score'] > 0)) {
            $score = $filters['min_trust_score'] ?? 75; // Bronze or better
            $query->where('ai_score', '>=', $score);
        }

        // Tags (e.g., 'ai-picks', 'trending', 'price-drops')
        if (!empty($filters['tag'])) {
            if ($filters['tag'] === 'ai-picks') {
                $query->where('ai_score', '>=', 85)->orderByRaw('(original_price - discounted_price) DESC');
            } elseif ($filters['tag'] === 'trending') {
                // Simplified trending logic: high AI score + recent
                $query->where('ai_score', '>=', 80)->orderBy('created_at', 'desc');
            } else {
                $query->whereHas('tags', function($q) use ($filters) {
                    $q->where('slug', $filters['tag']);
                });
            }
        }

        // Sorting
        if (!empty($filters['sort'])) {
            if ($filters['sort'] === 'discount') {
                $query->orderByRaw('(original_price - discounted_price) DESC');
            } elseif ($filters['sort'] === 'price_asc') {
                $query->orderBy('discounted_price', 'asc');
            } elseif ($filters['sort'] === 'price_desc') {
                $query->orderBy('discounted_price', 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            // Default sort
            if (empty($filters['tag'])) {
                $query->orderBy('created_at', 'desc');
            }
        }

        return $query;
    }
}

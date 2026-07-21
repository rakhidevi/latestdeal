<?php

namespace App\Services\SearchPipeline;

use App\Models\Merchant;

class FilterNormalizer
{
    /**
     * Normalize raw HTTP request filter array into a canonical filter structure.
     */
    public function normalize(array $rawFilters): array
    {
        $normalized = [];

        // 1. Text Search Query
        if (!empty($rawFilters['q'])) {
            $normalized['q'] = trim((string)$rawFilters['q']);
        }

        // 2. Category Normalization
        $cat = $rawFilters['category_slug'] ?? $rawFilters['category'] ?? null;
        if (!empty($cat) && $cat !== 'all') {
            $normalized['category_slug'] = trim((string)$cat);
        }

        // 3. Brand Normalization
        $brand = $rawFilters['brand_slug'] ?? $rawFilters['brand'] ?? null;
        if (!empty($brand) && $brand !== 'all') {
            $normalized['brand_slug'] = trim((string)$brand);
        }

        // 4. Merchant Normalization
        if (!empty($rawFilters['merchant_id']) && is_numeric($rawFilters['merchant_id'])) {
            $normalized['merchant_id'] = (int)$rawFilters['merchant_id'];
        } else {
            $m = $rawFilters['merchant_slug'] ?? $rawFilters['merchant'] ?? null;
            if (!empty($m) && $m !== 'all') {
                $normalized['merchant_slug'] = trim((string)$m);
            }
        }

        // 5. Price Filters
        if (isset($rawFilters['min_price']) && is_numeric($rawFilters['min_price'])) {
            $normalized['min_price'] = (float)$rawFilters['min_price'];
        }
        if (isset($rawFilters['max_price']) && is_numeric($rawFilters['max_price'])) {
            $normalized['max_price'] = (float)$rawFilters['max_price'];
        }

        // 6. Discount Range / Percentage Normalization
        if (!empty($rawFilters['discount_range'])) {
            $dr = trim((string)$rawFilters['discount_range']);
            if (preg_match('/^(\d+)-(\d+)(?:-off)?$/i', $dr, $m)) {
                $normalized['discount_min'] = (float)$m[1];
                $normalized['discount_max'] = (float)$m[2];
            } elseif (preg_match('/^(\d+)\+?(?:-off)?$/i', $dr, $m)) {
                $normalized['discount_min'] = (float)$m[1];
            }
        } elseif (isset($rawFilters['min_discount']) && is_numeric($rawFilters['min_discount'])) {
            $normalized['discount_min'] = (float)$rawFilters['min_discount'];
        }

        // 7. Verification / Trust score
        if (!empty($rawFilters['verified'])) {
            $normalized['verified'] = true;
        }
        if (isset($rawFilters['min_trust_score']) && is_numeric($rawFilters['min_trust_score'])) {
            $normalized['min_trust_score'] = (int)$rawFilters['min_trust_score'];
        }

        // 8. Tags / Specials
        if (!empty($rawFilters['tag'])) {
            $normalized['tag'] = trim((string)$rawFilters['tag']);
        }

        return $normalized;
    }
}

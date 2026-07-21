<?php

namespace App\Services\SearchPipeline\Pipes;

use Closure;
use App\Services\SearchPipeline\SearchPayload;
use Illuminate\Support\Facades\DB;

class ApplyRanking
{
    public function handle(SearchPayload $payload, Closure $next)
    {
        $query = $payload->query;
        $filters = $payload->filters;

        // If a specific sort is requested, use it instead of the AI ranking engine
        if (!empty($filters['sort'])) {
            if ($filters['sort'] === 'discount') {
                $query->orderByRaw('(original_price - discounted_price) DESC');
            } elseif ($filters['sort'] === 'price_asc') {
                $query->orderBy('discounted_price', 'asc');
            } elseif ($filters['sort'] === 'price_desc') {
                $query->orderBy('discounted_price', 'desc');
            } elseif ($filters['sort'] === 'newest') {
                $query->orderBy('created_at', 'desc');
            }
            return $next($payload);
        }

        // Apply specialized AI / Trending rules
        if (!empty($filters['tag'])) {
            if ($filters['tag'] === 'ai-picks') {
                $query->where('ai_score', '>=', 85)->orderByRaw('(original_price - discounted_price) DESC');
                return $next($payload);
            } elseif ($filters['tag'] === 'trending') {
                $query->where('ai_score', '>=', 80)->orderBy('created_at', 'desc');
                return $next($payload);
            }
        }

        // AI Ranking Engine
        // Rank Score = Discount Percentage + AI Score + Freshness Decay + Price Bump Boost
        // price_bumped_at: adds a large temporary boost when deal price has recently dropped
        
        $discountExpr = \Illuminate\Support\Facades\Schema::hasColumn('deals', 'discount_percentage') 
            ? 'IFNULL(discount_percentage, 0)' 
            : '(CASE WHEN original_price > 0 THEN ((original_price - discounted_price) / original_price * 100) ELSE 0 END)';

        // Price bump boost: adds 200 points if price dropped within the last 48 hours, decays to 0 after 7 days
        $priceBumpBoost = "CASE 
            WHEN price_bumped_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, price_bumped_at, NOW()) <= 48 THEN 200
            WHEN price_bumped_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, price_bumped_at, NOW()) <= 168 THEN GREATEST(0, 200 - (TIMESTAMPDIFF(HOUR, price_bumped_at, NOW()) - 48) * 2)
            ELSE 0
        END";

        $rankFormula = "($discountExpr * 0.4 + IFNULL(ai_score, 50) * 0.3 + GREATEST(100 - (DATEDIFF(NOW(), created_at) * 5), 0) * 0.3 + ($priceBumpBoost))";

        $query->orderByRaw("$rankFormula DESC");

        return $next($payload);
    }
}

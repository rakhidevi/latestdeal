<?php

namespace App\Services\SearchPipeline\Pipes;

use Closure;
use App\Services\SearchPipeline\SearchPayload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApplyRanking
{
    public function handle(SearchPayload $payload, Closure $next)
    {
        $query   = $payload->query;
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

        // ── DB-agnostic Ranking Engine ──────────────────────────────────────
        // Supports SQLite (production) and MySQL (future) without code changes.
        //
        // Rank = discount_score * 0.4 + ai_score * 0.3 + freshness_score * 0.3 + price_bump_boost
        // ──────────────────────────────────────────────────────────────────────
        $driver = DB::getDriverName();

        $discountExpr = Schema::hasColumn('deals', 'discount_percentage')
            ? 'IFNULL(discount_percentage, 0)'
            : '(CASE WHEN original_price > 0 THEN ((original_price - discounted_price) / original_price * 100) ELSE 0 END)';

        $hasPriceBump = Schema::hasColumn('deals', 'price_bumped_at');

        if ($driver === 'sqlite') {
            // SQLite: no GREATEST/DATEDIFF/TIMESTAMPDIFF — use julianday() arithmetic + MAX(a,b)
            $freshness = "MAX(100 - (CAST((julianday('now') - julianday(created_at)) AS INTEGER) * 5), 0)";

            if ($hasPriceBump) {
                $hoursAgo = "CAST((julianday('now') - julianday(price_bumped_at)) * 24 AS INTEGER)";
                $priceBumpBoost = "CASE
                    WHEN price_bumped_at IS NOT NULL AND ({$hoursAgo}) <= 48 THEN 200
                    WHEN price_bumped_at IS NOT NULL AND ({$hoursAgo}) <= 168 THEN MAX(0, 200 - (({$hoursAgo}) - 48) * 2)
                    ELSE 0
                END";
            } else {
                $priceBumpBoost = '0';
            }
        } else {
            // MySQL / MariaDB
            $freshness = "GREATEST(100 - (DATEDIFF(NOW(), created_at) * 5), 0)";

            if ($hasPriceBump) {
                $priceBumpBoost = "CASE
                    WHEN price_bumped_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, price_bumped_at, NOW()) <= 48 THEN 200
                    WHEN price_bumped_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, price_bumped_at, NOW()) <= 168 THEN GREATEST(0, 200 - (TIMESTAMPDIFF(HOUR, price_bumped_at, NOW()) - 48) * 2)
                    ELSE 0
                END";
            } else {
                $priceBumpBoost = '0';
            }
        }

        $rankFormula = "({$discountExpr} * 0.4 + IFNULL(ai_score, 50) * 0.3 + {$freshness} * 0.3 + ({$priceBumpBoost}))";

        $query->orderByRaw("{$rankFormula} DESC");

        return $next($payload);
    }
}

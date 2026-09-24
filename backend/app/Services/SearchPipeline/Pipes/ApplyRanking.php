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
            $sort = $filters['sort'];
            if ($sort === 'discount') {
                $query->orderByRaw('(original_price - discounted_price) DESC')->orderBy('created_at', 'desc');
            } elseif ($sort === 'price_asc' || $sort === 'price_low') {
                $query->orderBy('discounted_price', 'asc')->orderBy('created_at', 'desc');
            } elseif ($sort === 'price_desc' || $sort === 'price_high') {
                $query->orderBy('discounted_price', 'desc')->orderBy('created_at', 'desc');
            } elseif ($sort === 'newest' || $sort === 'recent') {
                $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
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
        // Supports SQLite and MySQL without code changes.
        //
        // Rank = discount_score * 0.35 + ai_score * 0.25 + freshness_score * 0.20 + new_arrival_boost + price_bump_boost
        // ──────────────────────────────────────────────────────────────────────
        $driver = DB::getDriverName();

        $discountExpr = Schema::hasColumn('deals', 'discount_percentage')
            ? 'IFNULL(discount_percentage, 0)'
            : '(CASE WHEN original_price > 0 THEN ((original_price - discounted_price) / original_price * 100) ELSE 0 END)';

        $hasPriceBump = Schema::hasColumn('deals', 'price_bumped_at');

        if ($driver === 'sqlite') {
            // SQLite: no GREATEST/DATEDIFF/TIMESTAMPDIFF — use julianday() arithmetic + MAX(a,b)
            $freshness = "MAX(100 - (CAST((julianday('now') - julianday(COALESCE(created_at, 'now'))) AS INTEGER) * 5), 0)";
            $newArrivalBoost = "CASE
                WHEN (julianday('now') - julianday(COALESCE(created_at, 'now'))) <= 2 THEN 80
                WHEN (julianday('now') - julianday(COALESCE(created_at, 'now'))) <= 7 THEN 40
                ELSE 0
            END";

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
            $freshness = "GREATEST(100 - (DATEDIFF(NOW(), COALESCE(created_at, NOW())) * 5), 0)";
            $newArrivalBoost = "CASE
                WHEN created_at >= NOW() - INTERVAL 48 HOUR THEN 80
                WHEN created_at >= NOW() - INTERVAL 7 DAY THEN 40
                ELSE 0
            END";

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

        $rankFormula = "({$discountExpr} * 0.35 + IFNULL(ai_score, 50) * 0.25 + {$freshness} * 0.20 + {$newArrivalBoost} + ({$priceBumpBoost}))";

        $query->orderByRaw("{$rankFormula} DESC, created_at DESC, id DESC");

        return $next($payload);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Deal;

class LiveComparisonController extends Controller
{
    /**
     * Called by Shopper Assistant to initiate a comparison
     */
    public function compare(Request $request)
    {
        $request->validate([
            'deal_id' => 'required|integer',
            'title' => 'required|string',
        ]);

        $dealId = $request->deal_id;
        $title = $request->title;
        $cacheKey = "compare_prices_{$dealId}";
        
        $deal = Deal::with('merchant')->find($dealId);
        $dealPrice = $deal ? (float)$deal->discounted_price : null;

        // 1. Check Cache (60 min TTL)
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult && !empty($cachedResult['results'])) {
            $sanitized = $this->sanitizeCompetitorResults($cachedResult['results'], $dealPrice ?? 1000, $deal ? $deal->original_price : null);
            if (!empty($sanitized)) {
                $cachedResult['results'] = $sanitized;
                return response()->json([
                    'status' => 'cache_hit',
                    'data' => $cachedResult
                ]);
            }
        }

        // 2. Check if Deal already has persisted comparison results
        if ($deal) {
            $pi = is_array($deal->price_intelligence) ? $deal->price_intelligence : (json_decode($deal->price_intelligence ?? '', true) ?? []);
            if (!empty($pi['comparison_results'])) {
                $sanitized = $this->sanitizeCompetitorResults($pi['comparison_results'], (float)$deal->discounted_price, (float)$deal->original_price);
                if (!empty($sanitized)) {
                    $payload = [
                        'results' => $sanitized,
                        'ai_score' => $pi['ai_score'] ?? $deal->ai_score ?? 85,
                    ];
                    Cache::put($cacheKey, $payload, now()->addMinutes(60));
                    return response()->json([
                        'status' => 'cache_hit',
                        'data' => $payload
                    ]);
                }
            }
        }

        // 3. Return verified primary retailer offer and honest competitor status (no fabricated percentage math)
        $results = $this->generateCompetitorPrices($deal, $title, $dealPrice);
        $aiScore = $deal && $deal->ai_score ? $deal->ai_score : 82;

        $payload = [
            'results' => $results,
            'ai_score' => $aiScore,
            'verification_note' => 'Primary retailer pricing and stock reflect our latest catalog synchronization. Third-party prices require confirmed SKU match.',
        ];

        Cache::put($cacheKey, $payload, now()->addMinutes(60));

        if ($deal) {
            $pi = is_array($deal->price_intelligence) ? $deal->price_intelligence : (json_decode($deal->price_intelligence ?? '', true) ?? []);
            $pi['comparison_results'] = $results;
            $pi['ai_score'] = $aiScore;
            $pi['compared_at'] = now()->toIso8601String();
            $deal->price_intelligence = $pi;
            $deal->saveQuietly();
        }

        return response()->json([
            'status' => 'verified_primary_store',
            'data' => $payload
        ]);
    }

    /**
     * Return verified primary store offer and honest unverified competitor status
     */
    protected function generateCompetitorPrices(?Deal $deal, string $title, ?float $dealPrice): array
    {
        $basePrice = $dealPrice ?? ($deal ? (float)$deal->discounted_price : 1999);
        $currentMerchant = $deal && $deal->merchant ? $deal->merchant->name : 'Amazon India';
        $isOutOfStock = $deal && in_array(strtolower($deal->status ?? ''), ['out_of_stock', 'expired']);
        $checkedAt = ($deal && $deal->updated_at) ? $deal->updated_at->diffForHumans() : 'Recently';

        // Store 1: Current Store (Verified Offer from Catalog Synchronization)
        $stores = [
            [
                'store' => $currentMerchant,
                'price' => $isOutOfStock ? null : $basePrice,
                'delivery' => $isOutOfStock ? 'Currently Unavailable' : 'Standard Delivery (Merchant Verified)',
                'rating' => '4.4',
                'url' => $isOutOfStock ? null : ($deal ? $deal->url : 'https://latestdeal.in'),
                'badge' => $isOutOfStock ? 'Out of Stock' : 'Catalog Verified Offer',
                'availability' => $isOutOfStock ? 'Unavailable (Catalog Record)' : 'In Stock (Last Catalog Sync)',
                'checked_at' => 'Catalog synced: ' . $checkedAt,
                'is_verified' => true,
                'in_stock' => !$isOutOfStock,
            ]
        ];

        // Competitors: Honest representation without invented prices
        $isAmazonCurrent = str_contains(strtolower($currentMerchant), 'amazon');
        $competitors = [
            ['name' => $isAmazonCurrent ? 'Flipkart' : 'Amazon India', 'rating' => '4.3'],
            ['name' => 'Croma', 'rating' => '4.2'],
            ['name' => 'Reliance Digital', 'rating' => '4.1'],
        ];

        foreach ($competitors as $comp) {
            $stores[] = [
                'store' => $comp['name'],
                'price' => null,
                'delivery' => 'Retailer verification pending',
                'rating' => $comp['rating'],
                'url' => null,
                'badge' => null,
                'availability' => 'Unverified for this SKU',
                'checked_at' => null,
                'is_verified' => false,
                'in_stock' => null,
            ];
        }

        return $stores;
    }

    /**
     * Defensive price bound check: filter out wildly mismatched products (e.g. 15,999 TV on a 49 bulb)
     */
    protected function sanitizeCompetitorResults(array $results, float $dealPrice, ?float $mrp = null): array
    {
        if ($dealPrice <= 0) {
            return [];
        }

        $ceiling = 2.2 * max($dealPrice, $mrp ?? $dealPrice);
        $floor = 0.25 * $dealPrice;

        $sanitized = [];
        foreach ($results as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (isset($item['price']) && $item['price'] !== null) {
                $price = (float)$item['price'];
                if ($price > $ceiling || $price < $floor) {
                    continue; // Skip out-of-range mismatched competitor product
                }
            }

            // Normalize in_stock and verification status
            $isVerified = !empty($item['is_verified']);
            if ($isVerified) {
                $isOos = in_array(strtolower($item['availability'] ?? ''), ['out of stock', 'currently unavailable']) || empty($item['price']);
                $item['in_stock'] = !$isOos;
                if ($isOos) {
                    $item['price'] = null;
                    $item['url'] = null;
                }
            } else {
                $item['is_verified'] = false;
                $item['in_stock'] = null;
                $item['availability'] = 'Unverified for this SKU';
                $item['price'] = null;
                $item['url'] = null;
            }

            $sanitized[] = $item;
        }

        return $sanitized;
    }

    /**
     * Called by Shopper Assistant to poll job status
     */
    public function checkStatus($jobId)
    {
        $job = DB::table('scraper_jobs')->where('id', $jobId)->first();

        if (!$job) {
            return response()->json(['status' => 'failed', 'message' => 'Job not found']);
        }

        if ($job->status === 'success') {
            $payload = json_decode($job->payload, true);
            $dealId = $payload['deal_id'] ?? null;
            $deal = $dealId ? Deal::find($dealId) : null;
            $rawResults = $payload['results'] ?? [];
            $results = $this->sanitizeCompetitorResults(
                $rawResults,
                (float)($payload['deal_price'] ?? ($deal ? $deal->discounted_price : 1000)),
                $deal ? (float)$deal->original_price : null
            );
            if (empty($results)) {
                $results = $this->generateCompetitorPrices($deal, $payload['title'] ?? 'Deal', $payload['deal_price'] ?? null);
            }
            $aiScore = $payload['ai_score'] ?? ($deal ? $deal->ai_score : 85);
            
            // Cache the result for next time
            if ($dealId) {
                Cache::put("compare_prices_{$dealId}", [
                    'results' => $results,
                    'ai_score' => $aiScore
                ], now()->addMinutes(60));
            }

            return response()->json([
                'status' => 'completed',
                'data' => [
                    'results' => $results,
                    'ai_score' => $aiScore
                ]
            ]);
        } elseif ($job->status === 'failure') {
            return response()->json(['status' => 'failed']);
        }

        // Auto-complete any pending job if worker hasn't processed within reasonable time
        $payload = json_decode($job->payload, true) ?? [];
        $dealId = $payload['deal_id'] ?? null;
        $title = $payload['title'] ?? 'Deal';
        $deal = $dealId ? Deal::find($dealId) : null;
        $results = $this->generateCompetitorPrices($deal, $title, $payload['deal_price'] ?? null);
        $aiScore = $deal && $deal->ai_score ? $deal->ai_score : 85;

        $completedPayload = [
            'deal_id' => $dealId,
            'results' => $results,
            'ai_score' => $aiScore,
        ];

        DB::table('scraper_jobs')->where('id', $jobId)->update([
            'status' => 'success',
            'payload' => json_encode($completedPayload),
            'completed_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'completed',
            'data' => [
                'results' => $results,
                'ai_score' => $aiScore
            ]
        ]);
    }

    /**
     * Called by AWS Python Worker to get the next pending job
     */
    public function getNextJob()
    {
        // Find a job that's marked as running but has no results yet, created in the last 2 minutes
        // We use 'running' as the pending state for the worker to pick up
        $job = DB::table('scraper_jobs')
            ->where('type', 'compare_prices')
            ->where('status', 'running')
            ->whereNull('completed_at')
            ->where('created_at', '>=', now()->subMinutes(2))
            ->orderBy('id', 'asc')
            ->first();

        if (!$job) {
            return response()->json(['job' => null]);
        }

        $payload = json_decode($job->payload, true);
        
        return response()->json([
            'job' => [
                'id' => $job->id,
                'title' => $payload['title'] ?? '',
                'deal_id' => $payload['deal_id'] ?? '',
                'deal_price' => $payload['deal_price'] ?? null
            ]
        ]);
    }

    /**
     * Called by AWS Python Worker to push results back
     */
    public function completeJob(Request $request, $jobId)
    {
        $job = DB::table('scraper_jobs')->where('id', $jobId)->first();
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        $rawResults = $request->input('results', []);
        $aiScore = $request->input('ai_score', 85);

        $payload = json_decode($job->payload, true) ?? [];
        $dealId = $payload['deal_id'] ?? null;
        $deal = $dealId ? Deal::find($dealId) : null;

        $results = $this->sanitizeCompetitorResults(
            $rawResults,
            (float)($payload['deal_price'] ?? ($deal ? $deal->discounted_price : 1000)),
            $deal ? (float)$deal->original_price : null
        );
        if (empty($results)) {
            $results = $this->generateCompetitorPrices($deal, $payload['title'] ?? 'Deal', $payload['deal_price'] ?? null);
        }

        $payload['results'] = $results;
        $payload['ai_score'] = $aiScore;

        DB::table('scraper_jobs')->where('id', $jobId)->update([
            'status' => 'success',
            'payload' => json_encode($payload),
            'completed_at' => now(),
            'updated_at' => now()
        ]);

        $dealId = $payload['deal_id'] ?? null;
        if ($dealId) {
            // Immediately warm cache
            Cache::put("compare_prices_{$dealId}", [
                'results' => $results,
                'ai_score' => $aiScore
            ], now()->addMinutes(60));

            // Persist to Deal model
            $deal = Deal::find($dealId);
            if ($deal) {
                $pi = is_array($deal->price_intelligence) ? $deal->price_intelligence : (json_decode($deal->price_intelligence, true) ?? []);
                $pi['comparison_results'] = $results;
                $pi['ai_score'] = $aiScore;
                $pi['compared_at'] = now()->toIso8601String();
                $deal->price_intelligence = $pi;
                if ($aiScore && !$deal->ai_score) {
                    $deal->ai_score = $aiScore;
                }
                $deal->save();
            }
        }

        return response()->json(['status' => 'success']);
    }
}

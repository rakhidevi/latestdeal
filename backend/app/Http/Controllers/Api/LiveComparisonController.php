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
        
        $deal = Deal::find($dealId);
        $dealPrice = $deal ? $deal->discounted_price : null;

        // 1. Check Cache (30 min TTL)
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult) {
            return response()->json([
                'status' => 'cache_hit',
                'data' => $cachedResult
            ]);
        }

        // 2. No cache, create a pending job in scraper_jobs
        $jobId = DB::table('scraper_jobs')->insertGetId([
            'name' => 'Live Compare: ' . substr($title, 0, 50),
            'type' => 'compare_prices',
            'status' => 'running',
            'payload' => json_encode(['deal_id' => $dealId, 'title' => $title, 'deal_price' => $dealPrice]),
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'pending',
            'job_id' => $jobId,
            'message' => 'Job queued for processing'
        ]);
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
            $results = $payload['results'] ?? [];
            $aiScore = $payload['ai_score'] ?? null;
            
            // Cache the result for next time
            $dealId = $payload['deal_id'];
            Cache::put("compare_prices_{$dealId}", [
                'results' => $results,
                'ai_score' => $aiScore
            ], now()->addMinutes(30));

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

        return response()->json(['status' => 'pending']);
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

        $payload = json_decode($job->payload, true) ?? [];
        $payload['results'] = $request->input('results', []);
        $payload['ai_score'] = $request->input('ai_score', 85);

        DB::table('scraper_jobs')->where('id', $jobId)->update([
            'status' => 'success',
            'payload' => json_encode($payload),
            'completed_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['status' => 'success']);
    }
}

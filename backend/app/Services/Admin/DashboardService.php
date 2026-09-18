<?php

namespace App\Services\Admin;

use App\Models\Deal;
use App\Models\WorkerStatus;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getMetrics()
    {
        $workerStatuses = WorkerStatus::orderBy('last_seen', 'desc')->get();
        $workersOnline = $workerStatuses->filter(fn($w) => $w->health_status === 'online')->count();
        $totalWorkers = $workerStatuses->count();
        
        $queueCount = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        $diskPath = storage_path();
        $diskTotal = @disk_total_space($diskPath) ?: 1;
        $diskFree = @disk_free_space($diskPath) ?: 0;
        $storageUsedPct = round((($diskTotal - $diskFree) / $diskTotal) * 100);

        // Dashboard Alerts
        $alerts = [];
        $offlineWorkers = $workerStatuses->filter(fn($w) => $w->health_status === 'offline');
        foreach ($offlineWorkers as $worker) {
            $alerts[] = ['type' => 'error', 'message' => "{$worker->worker_name} offline", 'icon' => 'alert-circle'];
        }
        if ($queueCount > 50) {
            $alerts[] = ['type' => 'warning', 'message' => "Queue growing ({$queueCount})", 'icon' => 'list-plus'];
        }
        if ($failedJobs > 0) {
            $alerts[] = ['type' => 'warning', 'message' => "{$failedJobs} failed jobs", 'icon' => 'alert-triangle'];
        }
        if (empty($alerts)) {
            $alerts[] = ['type' => 'success', 'message' => "All systems operational", 'icon' => 'check-circle'];
        }

        $dealsToday = Deal::whereDate('created_at', today())->count();
        $publishedToday = Deal::whereDate('created_at', today())->where('status', 'active')->count();
        $pendingReview = Deal::where('status', 'pending')->count();
        $totalClicks = DB::table('clicks')->count();

        $metricsDaily = DB::table('clicks')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $dealImpressions = DB::table('uic_page_visits')->where('url', 'like', '%/deal/%')->count();
        $calculatedCtr = $dealImpressions > 0 
            ? round(($totalClicks / $dealImpressions) * 100, 2) 
            : ($totalClicks > 0 ? 100.0 : 0.0);

        $metrics = [
            'total_clicks' => $totalClicks,
            'ctr' => $calculatedCtr . '%',
            'chart_labels' => $metricsDaily->pluck('date'),
            'chart_data' => $metricsDaily->pluck('count')
        ];
        
        $clickStats = DB::table('clicks')
            ->join('deals', 'clicks.deal_id', '=', 'deals.id')
            ->join('merchants', 'deals.merchant_id', '=', 'merchants.id')
            ->select(
                'merchants.name',
                'merchants.domain',
                DB::raw('count(*) as click_count')
            )
            ->groupBy('merchants.id', 'merchants.name', 'merchants.domain')
            ->get();

        $topProducts = DB::table('clicks')
            ->join('deals', 'clicks.deal_id', '=', 'deals.id')
            ->select(
                'deals.id',
                'deals.title',
                'deals.image_path',
                DB::raw('count(*) as click_count')
            )
            ->groupBy('deals.id', 'deals.title', 'deals.image_path')
            ->orderByDesc('click_count')
            ->limit(10)
            ->get();

        $sourceCounts = DB::table('deals')
            ->join('merchants', 'deals.merchant_id', '=', 'merchants.id')
            ->select('merchants.name', 'merchants.id as merchant_id', DB::raw('count(*) as total'))
            ->groupBy('merchants.id', 'merchants.name')
            ->get();

        $scraperStats = [
            'source_counts' => $sourceCounts
        ];

        $categoryStats = DB::table('clicks')
            ->join('deals', 'clicks.deal_id', '=', 'deals.id')
            ->join('categories', 'deals.category_id', '=', 'categories.id')
            ->select(
                'categories.name',
                'categories.commission_rate',
                DB::raw('count(*) as click_count'),
                DB::raw('SUM(deals.discounted_price * (categories.commission_rate / 100.0) * 0.03) as estimated_revenue')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.commission_rate')
            ->get();

        $estimatedEarnings = $categoryStats->sum('estimated_revenue') ?? 0;
        $ctr = $calculatedCtr;

        $rejectedDeals = Deal::where('status', 'rejected')->count();
        $missingImages = Deal::whereNull('image_path')->orWhere('image_path', '')->count();

        // Optimized Review Queue Aging using SQL bounds
        $oneHourAgo = now()->subHour();
        $sixHoursAgo = now()->subHours(6);
        $twentyFourHoursAgo = now()->subDay();

        $under1h = Deal::where('editorial_status', 'IN_REVIEW')->where('updated_at', '>=', $oneHourAgo)->count();
        $h1to6 = Deal::where('editorial_status', 'IN_REVIEW')->whereBetween('updated_at', [$sixHoursAgo, $oneHourAgo])->count();
        $h6to24 = Deal::where('editorial_status', 'IN_REVIEW')->whereBetween('updated_at', [$twentyFourHoursAgo, $sixHoursAgo])->count();
        $over24h = Deal::where('editorial_status', 'IN_REVIEW')->where('updated_at', '<', $twentyFourHoursAgo)->count();
        $oldestTimestamp = Deal::where('editorial_status', 'IN_REVIEW')->min('updated_at');
        $oldestDays = $oldestTimestamp ? \Carbon\Carbon::parse($oldestTimestamp)->diffInDays(now()) : 0;

        $reviewQueueAging = [
            '<1h' => $under1h,
            '1-6h' => $h1to6,
            '6-24h' => $h6to24,
            '>24h' => $over24h,
            'oldest_days' => $oldestDays
        ];

        // Pipeline Metrics (Last 7 Days)
        $sevenDaysAgo = now()->subDays(7);
        $totalIngested = Deal::where('created_at', '>=', $sevenDaysAgo)->count();
        $qaAttempted = Deal::where('created_at', '>=', $sevenDaysAgo)
            ->whereNotIn('editorial_status', ['DRAFT', 'AI_GENERATING'])->count();
        $qaPassed = Deal::where('created_at', '>=', $sevenDaysAgo)
            ->whereIn('editorial_status', ['IN_REVIEW', 'PUBLISHED'])->count();
        $published = Deal::where('created_at', '>=', $sevenDaysAgo)
            ->where('editorial_status', 'PUBLISHED')->count();

        $pipelineMetrics = [
            'ingested' => $totalIngested,
            'ai_to_qa' => $qaAttempted > 0 && $totalIngested > 0 ? round(($qaAttempted / $totalIngested) * 100, 1) : 0,
            'qa_pass_rate' => $qaPassed > 0 && $qaAttempted > 0 ? round(($qaPassed / $qaAttempted) * 100, 1) : 0,
            'review_to_published' => $published > 0 && $qaPassed > 0 ? round(($published / $qaPassed) * 100, 1) : 0,
        ];

        // Alerts addition
        $inReviewCount = Deal::where('editorial_status', 'IN_REVIEW')->count();
        if ($inReviewCount > 100) {
            $alerts[] = ['type' => 'warning', 'message' => "Review Queue > 100", 'icon' => 'alert-circle'];
        }
        if ($reviewQueueAging['oldest_days'] > 1) {
            $alerts[] = ['type' => 'error', 'message' => "Oldest Review > 24h", 'icon' => 'clock'];
        }
        if ($totalIngested > 0 && $pipelineMetrics['qa_pass_rate'] < 50) {
            $alerts[] = ['type' => 'error', 'message' => "QA failure rate > 50%", 'icon' => 'shield-alert'];
        }

        $todayDateStr = now()->toDateString();
        $merchantStats = DB::table('deals')
            ->join('merchants', 'deals.merchant_id', '=', 'merchants.id')
            ->select(
                'merchants.name',
                DB::raw('count(*) as total_deals'),
                DB::raw("SUM(CASE WHEN DATE(deals.created_at) = '{$todayDateStr}' THEN 1 ELSE 0 END) as today_deals")
            )
            ->groupBy('merchants.id', 'merchants.name')
            ->get();
            
        foreach ($merchantStats as $stat) {
            $worker = $workerStatuses->first(function($w) use ($stat) {
                return $w->worker_type === 'scraper' && stripos($w->worker_name, $stat->name) !== false;
            });
            if ($worker) {
                $stat->success_pct = ($worker->success_today + $worker->failed_today) > 0 
                    ? round(($worker->success_today / ($worker->success_today + $worker->failed_today)) * 100) 
                    : 100;
                $stat->retries = $worker->retry_today;
                $stat->last_run = $worker->last_seen ? $worker->last_seen->diffForHumans() : 'N/A';
            } else {
                $stat->success_pct = null;
                $stat->retries = null;
                $stat->last_run = null;
            }
        }

        $feed = collect();
        foreach(Deal::with('merchant')->orderBy('created_at', 'desc')->limit(5)->get() as $d) {
            $feed->push((object)['type' => 'deal', 'title' => 'New Deal: ' . $d->title, 'subtitle' => $d->merchant->name ?? 'Unknown', 'time' => $d->created_at, 'bg' => 'bg-emerald-500']);
        }
        foreach(DB::table('failed_jobs')->orderBy('failed_at', 'desc')->limit(5)->get() as $e) {
            $feed->push((object)['type' => 'error', 'title' => 'Job Failed: ' . $e->queue, 'subtitle' => \Illuminate\Support\Str::limit($e->exception, 50), 'time' => \Carbon\Carbon::parse($e->failed_at), 'bg' => 'bg-red-500']);
        }
        foreach(DB::table('clicks')->orderBy('created_at', 'desc')->limit(5)->get() as $c) {
            $feed->push((object)['type' => 'click', 'title' => 'New Click', 'subtitle' => 'Deal ID: ' . $c->deal_id, 'time' => \Carbon\Carbon::parse($c->created_at), 'bg' => 'bg-indigo-500']);
        }
        foreach(WorkerStatus::orderBy('last_seen', 'desc')->limit(5)->get() as $w) {
            $feed->push((object)['type' => 'heartbeat', 'title' => 'Worker Check-in', 'subtitle' => $w->worker_name, 'time' => $w->last_seen, 'bg' => 'bg-blue-500']);
        }
        $activityFeed = $feed->sortByDesc('time')->take(8)->values();

        $thirtyDaysAgo = now()->subDays(30);
        try {
            $stats = [
                'total_visitors' => class_exists('\App\Models\UIC\UicVisitor') ? \App\Models\UIC\UicVisitor::where('created_at', '>=', $thirtyDaysAgo)->count() : 0,
                'total_sessions' => class_exists('\App\Models\UIC\UicVisitorSession') ? \App\Models\UIC\UicVisitorSession::where('created_at', '>=', $thirtyDaysAgo)->count() : 0,
                'total_affiliate_clicks' => class_exists('\App\Models\UIC\UicAffiliateClick') ? \App\Models\UIC\UicAffiliateClick::where('created_at', '>=', $thirtyDaysAgo)->count() : $totalClicks,
                'total_searches' => class_exists('\App\Models\UIC\UicSearchHistory') ? \App\Models\UIC\UicSearchHistory::where('created_at', '>=', $thirtyDaysAgo)->count() : 0,
            ];
        } catch (\Exception $e) {
            $stats = ['total_visitors' => 0, 'total_sessions' => 0, 'total_affiliate_clicks' => $totalClicks, 'total_searches' => 0];
        }

        try {
            $topSearches = class_exists('\App\Models\UIC\UicSearchHistory') 
                ? \App\Models\UIC\UicSearchHistory::selectRaw('search_term as search_query, COUNT(*) as count')
                    ->where('created_at', '>=', $thirtyDaysAgo)
                    ->groupBy('search_term')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get() 
                : collect();
        } catch (\Exception $e) {
            $topSearches = collect();
        }

        try {
            $recentClicks = class_exists('\App\Models\UIC\UicAffiliateClick') 
                ? \App\Models\UIC\UicAffiliateClick::with('deal.merchant')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get() 
                : collect();
        } catch (\Exception $e) {
            $recentClicks = collect();
        }

        $pipelineSetting = Setting::where('key', 'deal_approval_pipeline')->first();
        $pipelineEnabled = $pipelineSetting ? $pipelineSetting->value === 'enabled' : false;

        return compact(
            'stats', 'topSearches', 'recentClicks', 'metrics', 'topProducts', 'scraperStats', 'categoryStats',
            'workerStatuses', 'workersOnline', 'totalWorkers', 'queueCount', 'failedJobs', 'storageUsedPct', 'alerts',
            'dealsToday', 'publishedToday', 'pendingReview', 'totalClicks', 'estimatedEarnings', 'ctr', 'clickStats',
            'rejectedDeals', 'missingImages', 'merchantStats', 'activityFeed', 'pipelineEnabled', 'reviewQueueAging', 'pipelineMetrics'
        );
    }
}

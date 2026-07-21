<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Deal;

class SnapshotDailyMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take a daily snapshot of key metrics for historical trending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = today();
        
        // Deals published today
        $dealsPublished = Deal::whereDate('created_at', $today)
            ->where('status', 'active')
            ->count();
            
        // Total clicks today
        $totalClicks = DB::table('clicks')
            ->whereDate('created_at', $today)
            ->count();
            
        // Estimated Revenue Today
        $revenue = DB::table('clicks')
            ->whereDate('clicks.created_at', $today)
            ->join('deals', 'clicks.deal_id', '=', 'deals.id')
            ->join('categories', 'deals.category_id', '=', 'categories.id')
            ->sum(DB::raw('deals.discounted_price * (categories.commission_rate / 100) * 0.03'));
            
        // Failed jobs today
        $failedJobs = DB::table('failed_jobs')
            ->whereDate('failed_at', $today)
            ->count();
            
        // Aggregate scraper success % from Worker Status
        $workers = \App\Models\WorkerStatus::where('worker_type', 'scraper')->get();
        $successTotal = $workers->sum('success_today');
        $failTotal = $workers->sum('failed_today');
        $successPct = ($successTotal + $failTotal) > 0 
            ? round(($successTotal / ($successTotal + $failTotal)) * 100, 2) 
            : null;

        // Upsert the snapshot
        DB::table('daily_metrics')->updateOrInsert(
            ['date' => $today->toDateString()],
            [
                'deals_published' => $dealsPublished,
                'total_clicks' => $totalClicks,
                'estimated_revenue' => $revenue ?? 0,
                'failed_jobs' => $failedJobs,
                'scraper_success_pct' => $successPct,
                'updated_at' => now(),
            ]
        );

        $this->info("Successfully snapshot metrics for {$today->toDateString()}");
    }
}

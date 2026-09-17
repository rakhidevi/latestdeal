<?php

namespace App\Services\Admin;

use App\Models\ScraperJob;
use App\Models\Deal;
use Illuminate\Support\Facades\Artisan;

class ScraperService
{
    public function getActionsData()
    {
        $jobs = ScraperJob::orderBy('created_at', 'desc')->paginate(20);
        
        $metrics = [
            'total_scraped' => ScraperJob::whereIn('type', ['ingestion', 'URL_SCAN', 'CUSTOM_HUNT'])->count(),
            'accepted' => Deal::where('status', 'active')->count(),
            'rejected' => Deal::where('status', 'rejected')->count(),
            'expired' => Deal::where('status', 'expired')->count()
        ];

        return compact('jobs', 'metrics');
    }

    public function runArtisanCommand(string $command)
    {
        if ($command === 'migrate') {
            Artisan::call('migrate', ['--force' => true]);
        } else {
            Artisan::call($command);
        }
        
        return Artisan::output();
    }

    public function queueStartScraper()
    {
        return ScraperJob::create([
            'name' => 'Admin: Start Scraper',
            'type' => 'SYSTEM_COMMAND',
            'status' => 'PENDING',
            'payload' => ['command' => 'start'],
            'priority' => 'high',
            'started_at' => now(),
        ]);
    }

    public function queueStopScraper()
    {
        $job = ScraperJob::create([
            'name' => 'Admin: Stop Scraper',
            'type' => 'CANCELLATION',
            'status' => 'PENDING',
            'payload' => ['command' => 'stop_all'],
            'priority' => 'critical',
            'started_at' => now(),
        ]);
        
        ScraperJob::whereIn('status', ['PENDING', 'CLAIMED', 'PROCESSING'])
            ->update(['status' => 'CANCEL_REQUESTED']);

        return $job;
    }

    public function getScraperStatus()
    {
        $recentJobs = ScraperJob::orderBy('created_at', 'desc')->limit(5)->get();
        $recentWorker = \App\Models\WorkerStatus::orderBy('last_seen', 'desc')->first();
        
        $isOnline = false;
        $message = 'Worker idle or offline';
        
        if ($recentWorker) {
            $isOnline = $recentWorker->health_status === 'online' && $recentWorker->last_seen && \Carbon\Carbon::parse($recentWorker->last_seen)->greaterThan(now()->subMinutes(10));
            $message = $isOnline ? "Worker '{$recentWorker->worker_name}' active" : "Worker '{$recentWorker->worker_name}' offline";
        } elseif (ScraperJob::where('created_at', '>=', now()->subMinutes(15))->exists()) {
            $isOnline = true;
            $message = 'Worker active in last 15m';
        }

        return [
            'running' => $isOnline,
            'message' => $message,
            'recent_jobs' => $recentJobs
        ];
    }

    public function queueScrapeUrl(string $url, ?string $type)
    {
        return ScraperJob::create([
            'name' => 'Admin: Single URL Scan',
            'type' => 'URL_SCAN',
            'status' => 'PENDING',
            'payload' => [
                'url' => $url,
                'source' => 'admin'
            ],
            'started_at' => now(),
        ]);
    }

    public function queueCustomHunt(array $huntData)
    {
        return ScraperJob::create([
            'name' => 'Admin: Custom Hunt',
            'type' => 'CUSTOM_HUNT',
            'status' => 'PENDING',
            'payload' => [
                'category' => $huntData['category'] ?? null,
                'brand' => $huntData['brand'] ?? null,
                'discount' => $huntData['discount'] ?? null,
                'keyword' => $huntData['keyword'] ?? null,
                'mode' => $huntData['mode'] ?? null
            ],
            'started_at' => now(),
        ]);
    }
}

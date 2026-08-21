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
            'total_scraped' => ScraperJob::where('type', 'ingestion')->count(),
            'accepted' => Deal::where('status', 'active')->count(),
            'rejected' => Deal::where('status', 'rejected')->count(),
            'expired' => ScraperJob::where('type', 'expiry_check')->where('status', 'success')->count()
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
        return [
            'running' => true,
            'message' => 'Worker is operating in polling mode',
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

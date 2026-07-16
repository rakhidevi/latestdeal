<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutoHuntCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deals:auto-hunt {--category=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically trigger python worker to hunt for deals in various categories';

    // List of high-value keywords to hunt
    protected $categories = [
        "Smartphones",
        "Laptops",
        "Men's Shoes",
        "Women's Clothing",
        "Mobile Recharges",
        "Groceries",
        "Home Appliances",
        "Smart TVs",
        "Wireless Earbuds",
        "Beauty and Makeup",
        "Kitchen Appliances",
        "Watches"
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Automated Deal Hunt...");

        $targetCategory = $this->option('category');
        
        if (!$targetCategory) {
            // Pick a random category to ensure we get fresh deals across different niches
            // You can also use cache to round-robin through them
            $cacheKey = 'deals_last_hunt_index';
            $lastIndex = cache()->get($cacheKey, -1);
            $nextIndex = ($lastIndex + 1) % count($this->categories);
            $targetCategory = $this->categories[$nextIndex];
            cache()->put($cacheKey, $nextIndex);
        }

        $this->info("Hunting Category: {$targetCategory}");

        try {
            $workerIp = gethostbyname('worker');
            // If the worker is unreachable (e.g. running on desktop without tunnel port forward for /hunt)
            // It might fail, but let's try.
            // Wait, the python daemon runs on desktop. If Laravel is on a remote server, it CANNOT hit $workerIp:8001
            // But since the python worker is running a daemon, and Laravel wants to trigger a custom hunt...
            // Oh, we can just use the database `scraper_jobs` or directly send a websocket broadcast!
            
            // Wait, I will use HTTP if it's local, otherwise we need a fallback.
            // Actually, the user's Laravel backend might be local or they are using Cloudflare tunnel.
            // Let's just do the HTTP request as AdminController does.
            $payload = [
                'keyword' => $targetCategory,
                'mode' => 'ingestion'
            ];
            
            $response = Http::timeout(10)->post("http://{$workerIp}:8001/hunt", $payload);
            
            if ($response->successful()) {
                $this->info("Hunt triggered successfully: " . $response->json('status'));
                Log::info("AutoHunt triggered for category: {$targetCategory}");
            } else {
                $this->error("Worker responded with error: " . $response->status());
            }

        } catch (\Exception $e) {
            $this->error("Failed to connect to Python worker daemon: " . $e->getMessage());
            Log::error("AutoHunt failed: " . $e->getMessage());
            
            // Fallback: If HTTP fails because the worker is on desktop, we can broadcast a WebSocket event
            // that the worker can pick up!
            $this->info("Attempting WebSocket fallback...");
            try {
                // We broadcast a new event 'App\Events\HuntRequested'
                event(new \App\Events\HuntRequested($targetCategory));
                $this->info("WebSocket event dispatched.");
            } catch (\Exception $wsException) {
                $this->error("WebSocket fallback failed: " . $wsException->getMessage());
            }
        }
    }
}

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
            \App\Models\ScraperJob::create([
                'name' => "AutoHunt: {$targetCategory}",
                'type' => 'AUTO_HUNT',
                'status' => 'PENDING',
                'payload' => [
                    'category' => $targetCategory,
                    'mode' => 'ingestion',
                    'source' => 'AutoHuntCommand'
                ],
                'started_at' => now(),
            ]);

            $this->info("Hunt triggered successfully by creating a ScraperJob.");
            Log::info("AutoHunt triggered for category: {$targetCategory}");
            
            // Still dispatch WebSocket event as a notification to admins
            try {
                event(new \App\Events\HuntRequested($targetCategory));
                $this->info("WebSocket event dispatched.");
            } catch (\Exception $wsException) {
                // Ignore missing websocket dependencies
            }

        } catch (\Exception $e) {
            $this->error("Failed to insert ScraperJob: " . $e->getMessage());
            Log::error("AutoHunt failed: " . $e->getMessage());
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Deal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckDeadLinksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deals:check-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check active deals for dead links and mark them as expired.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deals = Deal::where('status', 'active')->get();
        $expiredCount = 0;

        $this->info("Checking " . $deals->count() . " active deals for dead links...");

        foreach ($deals as $deal) {
            try {
                // Using a common user agent to avoid basic blocks
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])->timeout(15)->get($deal->url);

                // If explicitly 404 or the page title indicates it's dead
                if ($response->status() === 404 || Str::contains($response->body(), 'Looking for something?')) {
                    $deal->update(['status' => 'expired']);
                    $expiredCount++;
                    $this->info("Marked deal {$deal->id} as expired (Dead Link).");
                    Log::info("CheckDeadLinksCommand: Deal {$deal->id} expired due to dead link on URL: {$deal->url}");
                }
            } catch (\Exception $e) {
                $this->warn("Could not check deal {$deal->id}: " . $e->getMessage());
            }

            // Sleep to avoid rate limiting
            sleep(rand(1, 3));
        }

        $this->info("Finished checking links. Expired {$expiredCount} deals.");
    }
}

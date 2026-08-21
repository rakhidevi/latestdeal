<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deal;
use Illuminate\Support\Facades\Http;

class PushDealsToProductionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deals:push-to-production {--url=} {--token=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pushes IN_REVIEW deals to the production server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiUrl = $this->option('url') ?: env('PRODUCTION_API_URL', 'https://www.latestdeal.in');
        $apiToken = $this->option('token') ?: env('PRODUCTION_API_TOKEN');

        if (empty($apiToken)) {
            $this->error("API Token is required.");
            return 1;
        }

        $deals = Deal::where('editorial_status', Deal::STATUS_IN_REVIEW)
            ->where('production_sync_status', Deal::SYNC_PENDING)
            ->get();

        if ($deals->isEmpty()) {
            $this->info("No IN_REVIEW deals pending sync.");
            return 0;
        }

        $this->info("Found {$deals->count()} deals to push to production...");

        foreach ($deals as $deal) {
            $this->info("Pushing Deal ID {$deal->id} ({$deal->title})...");

            $payload = [
                'asin' => $deal->asin,
                'title' => $deal->title,
                'brand' => $deal->brand,
                'category_id' => $deal->category_id,
                'original_price' => $deal->original_price,
                'discounted_price' => $deal->discounted_price,
                'calculated_discount_percent' => $deal->calculated_discount_percent ?? $deal->discount_percentage,
                'url' => $deal->url,
                'short_url' => $deal->short_url,
                'image_url' => $deal->image_path,
                'editorial_summary' => $deal->editorial_summary,
                'editorial_verdict' => $deal->editorial_verdict,
                'pros' => $deal->pros ?? [],
                'cons' => $deal->cons ?? [],
                'qa_status' => 'PASSED', // Because it reached IN_REVIEW locally
                'trace_id' => $deal->trace_id ?? uniqid(),
                'pipeline_run_id' => $deal->pipeline_run_id ?? uniqid()
            ];

            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$apiToken}",
                    'Accept' => 'application/json'
                ])->timeout(15)->post("{$apiUrl}/api/worker/production-sync", $payload);

                if ($response->successful()) {
                    $deal->production_sync_status = Deal::SYNC_PUSHED;
                    $deal->production_deal_id = $response->json('deal_id');
                    $deal->production_pushed_at = now();
                    $deal->save();
                    
                    if ($response->status() === 200) {
                        $this->info("✅ Deal ID {$deal->id} already synced -> Prod Deal ID {$deal->production_deal_id}");
                    } else {
                        $this->info("✅ Successfully pushed Deal ID {$deal->id} -> Prod Deal ID {$deal->production_deal_id}");
                    }
                } elseif ($response->status() === 409) {
                    $deal->production_sync_status = Deal::SYNC_ERROR;
                    $deal->production_push_error = $response->body();
                    $deal->save();
                    $this->error("❌ Conflict! Deal ID {$deal->id}: " . $response->body());
                } else {
                    $deal->production_sync_status = Deal::SYNC_ERROR;
                    $deal->production_push_error = $response->body();
                    $deal->save();
                    $this->error("❌ Failed to push Deal ID {$deal->id}: " . $response->body());
                }
            } catch (\Exception $e) {
                $deal->production_sync_status = Deal::SYNC_ERROR;
                $deal->production_push_error = $e->getMessage();
                $deal->save();
                $this->error("❌ Error pushing Deal ID {$deal->id}: " . $e->getMessage());
            }
        }

        $this->info("Production sync complete.");
        return 0;
    }
}

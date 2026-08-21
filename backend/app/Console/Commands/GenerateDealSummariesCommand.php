<?php

namespace App\Console\Commands;

use App\Models\Deal;
use App\Services\Editorial\EditorialGenerator;
use Illuminate\Console\Command;

class GenerateDealSummariesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deals:summarize {--limit=10} {--pilot} {--deal-ids=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate AI Editorial Summaries for DRAFT deals.';

    /**
     * Execute the console command.
     */
    public function handle(EditorialGenerator $generator)
    {
        $isPilot = $this->option('pilot');
        $dealIdsOpt = $this->option('deal-ids');

        if ($isPilot) {
            if (!$dealIdsOpt) {
                $this->error("Pilot mode requires explicit --deal-ids.");
                return 1;
            }

            $dealIds = array_map('trim', explode(',', $dealIdsOpt));
            $this->info("Running in PILOT mode for Deal IDs: " . implode(', ', $dealIds));

            $deals = Deal::whereIn('id', $dealIds)->get();

            if ($deals->count() !== count($dealIds)) {
                $this->error("One or more pilot deals do not exist.");
                return 1;
            }

            foreach ($deals as $deal) {
                if ($deal->editorial_status !== 'DRAFT') {
                    $this->error("Deal ID {$deal->id} is not in DRAFT status (Current: {$deal->editorial_status}).");
                    return 1;
                }
            }
        } else {
            // Non-pilot mode
            if ($dealIdsOpt) {
                $dealIds = array_map('trim', explode(',', $dealIdsOpt));
                $deals = Deal::whereIn('id', $dealIds)
                    ->where('editorial_status', 'DRAFT')
                    ->get();
            } else {
                $limit = $this->option('limit');
                $deals = Deal::where('editorial_status', 'DRAFT')
                    ->orderBy('id', 'asc')
                    ->limit($limit)
                    ->get();
            }
        }

        if ($deals->isEmpty()) {
            $this->info("No active deals currently need AI summarization.");
            return 0;
        }

        $this->info("Found {$deals->count()} deals needing AI summaries.");

        $processedCount = 0;
        $successCount = 0;

        foreach ($deals as $deal) {
            $this->info("Generating summary for Deal ID: {$deal->id} - {$deal->title}");
            $start = microtime(true);
            
            $success = $generator->generateForDeal($deal);
            
            $latency = round((microtime(true) - $start) * 1000);
            
            if ($success) {
                $this->line("✅ Successfully summarized deal {$deal->id} in {$latency}ms. Status: IN_REVIEW");
                $successCount++;
            } else {
                $this->error("❌ Failed to summarize deal {$deal->id} in {$latency}ms. Status rolled back to DRAFT");
            }
            $processedCount++;
        }

        $this->info("Completed. Processed: {$processedCount}, Success: {$successCount}");
        return 0;
    }
}

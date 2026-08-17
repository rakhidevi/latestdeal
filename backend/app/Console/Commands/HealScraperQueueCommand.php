<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HealScraperQueueCommand extends Command
{
    protected $signature = 'scraper:heal-queue';
    protected $description = 'Finds stuck scraper jobs and requeues them.';

    public function handle()
    {
        // 1. Any job stuck in CLAIMED for more than 5 minutes
        // 2. Any job stuck in PROCESSING where heartbeat_at is older than 5 minutes
        
        $stuckClaimed = DB::table('scraper_jobs')
            ->where('status', 'CLAIMED')
            ->where('claimed_at', '<', now()->subMinutes(5))
            ->update([
                'status' => 'PENDING',
                'worker_id' => null
            ]);
            
        $stuckProcessing = DB::table('scraper_jobs')
            ->where('status', 'PROCESSING')
            ->where('heartbeat_at', '<', now()->subMinutes(5))
            ->update([
                'status' => 'PENDING',
                'worker_id' => null
            ]);
            
        // Failsafe: max attempts logic
        DB::table('scraper_jobs')
            ->where('status', 'PENDING')
            ->where('attempts', '>=', DB::raw('max_attempts'))
            ->update([
                'status' => 'FAILED',
                'completed_at' => now(),
                'payload' => DB::raw("json_set(ifnull(payload, '{}'), '$.error', 'Max attempts exceeded due to worker crashes')")
            ]);

        $this->info("Healed {$stuckClaimed} claimed jobs and {$stuckProcessing} processing jobs.");
    }
}

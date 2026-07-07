<?php

namespace App\Console\Commands;

use App\Models\Deal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireDealsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deals:expire {--days=7 : The number of days after which a deal is expired}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically expire active or pending deals that are older than the threshold.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);
        
        $expiredCount = Deal::whereIn('status', ['active', 'pending'])
            ->where('created_at', '<', $cutoffDate)
            ->update(['status' => 'expired']);

        if ($expiredCount > 0) {
            Log::info("Auto-expired {$expiredCount} deals older than {$days} days.");
        }

        $this->info("Expired {$expiredCount} deals older than {$days} days.");
    }
}

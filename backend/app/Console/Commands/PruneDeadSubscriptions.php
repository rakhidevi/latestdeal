<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PushSubscription;
use App\Models\NotificationLog;
use Carbon\Carbon;

class PruneDeadSubscriptions extends Command
{
    protected $signature = 'push:prune {--days=30 : Days after which inactive logs are deleted}';

    protected $description = 'Clean up failed/expired push subscriptions and stale notification logs';

    public function handle()
    {
        // 1. Delete push subscriptions with 3+ consecutive failures or expired endpoints
        $deletedSubscriptions = PushSubscription::where('failure_count', '>=', 3)
            ->orWhere('expiration_time', '<=', Carbon::now())
            ->delete();

        // 2. Delete old notification logs
        $days = (int) $this->option('days');
        $deletedLogs = NotificationLog::where('created_at', '<=', Carbon::now()->subDays($days))->delete();

        $this->info("Pruned {$deletedSubscriptions} dead push subscriptions and {$deletedLogs} old notification logs.");
        return 0;
    }
}

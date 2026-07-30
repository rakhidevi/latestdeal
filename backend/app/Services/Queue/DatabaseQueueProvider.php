<?php

namespace App\Services\Queue;

use App\Contracts\QueueProviderInterface;
use Illuminate\Support\Facades\DB;

class DatabaseQueueProvider implements QueueProviderInterface
{
    public function getQueueSize(string  = \'default\'): int
    {
        return DB::table(\'jobs\')->where(\'queue\', )->count();
    }

    public function getFailedJobCount(): int
    {
        try {
            return DB::table(\'failed_jobs\')->count();
        } catch (\Exception ) {
            return 0;
        }
    }

    public function getThroughput(): int
    {
        // Throughput calculation would require tracking processed jobs.
        // For now, return 0 or rely on queue snapshots.
        return 0;
    }

    public function getLatency(): float
    {
        return 0.0;
    }

    public function getOldestPendingTimestamp(string  = \'default\'): ?int
    {
         = DB::table(\'jobs\')->where(\'queue\', )->orderBy(\'available_at\', \'asc\')->first();
        return  ? ->available_at : null;
    }
}

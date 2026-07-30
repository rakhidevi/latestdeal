<?php

namespace App\Services\Marketing;

use App\DTOs\Marketing\QueueMetricsDTO;
use App\Contracts\QueueProviderInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueueMonitorService
{
    public function __construct(
        protected QueueProviderInterface $queueProvider
    ) {}

    public function getMetrics(): QueueMetricsDTO
    {
        return Cache::remember('marketing.queue_metrics', 3, function () {
            
            $marketingQueueSize = $this->queueProvider->getQueueSize('marketing_emails');
            $defaultQueueSize = $this->queueProvider->getQueueSize('default');
            
            $failedJobs = $this->queueProvider->getFailedJobCount();
            
            // Get workers count (we'll query worker_heartbeats later in HealthService, 
            // but for now let's just query the table here if it exists or default to 0)
            $workers = 0;
            try {
                $workers = DB::table('worker_heartbeats')
                             ->where('last_heartbeat', '>=', now()->subMinutes(2))
                             ->count();
            } catch (\Exception $e) {
                // Table doesn't exist yet
            }
            
            return new QueueMetricsDTO(
                queues: [
                    'marketing_emails' => ['size' => $marketingQueueSize, 'status' => 'running'],
                    'default' => ['size' => $defaultQueueSize, 'status' => 'running']
                ],
                failedJobs: $failedJobs,
                workers: $workers,
                throughput: $this->queueProvider->getThroughput(),
                latency: $this->queueProvider->getLatency(),
                oldestPending: $this->queueProvider->getOldestPendingTimestamp('marketing_emails')
            );
        });
    }
}

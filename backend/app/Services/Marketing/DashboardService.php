<?php

namespace App\Services\Marketing;

use App\DTOs\Marketing\DashboardDTO;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    public function __construct(
        protected CampaignMetricsService $campaignMetrics,
        protected QueueMonitorService $queueMetrics,
        protected HealthService $healthMetrics,
        protected ActivityFeedService $activityFeed
    ) {}

    public function getDashboard(): DashboardDTO
    {
        return new DashboardDTO(
            campaignMetrics: $this->safeGet(fn() => $this->campaignMetrics->getMetrics(), new \App\DTOs\Marketing\CampaignMetricsDTO()),
            queueMetrics: $this->safeGet(fn() => $this->queueMetrics->getMetrics(), new \App\DTOs\Marketing\QueueMetricsDTO()),
            healthMetrics: $this->safeGet(fn() => $this->healthMetrics->getMetrics(), new \App\DTOs\Marketing\HealthMetricsDTO()),
            activityFeed: $this->safeGet(fn() => $this->activityFeed->getFeed(), [])
        );
    }

    /**
     * Safely execute a metric fetching closure, returning a fallback if it fails.
     */
    protected function safeGet(\Closure $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            Log::error('Marketing Dashboard Service Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return $fallback;
        }
    }
}

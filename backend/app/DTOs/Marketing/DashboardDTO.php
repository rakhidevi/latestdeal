<?php

namespace App\DTOs\Marketing;

class DashboardDTO
{
    public function __construct(
        public readonly CampaignMetricsDTO $campaignMetrics,
        public readonly QueueMetricsDTO $queueMetrics,
        public readonly HealthMetricsDTO $healthMetrics,
        public readonly array $activityFeed
    ) {}
}

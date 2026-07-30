import os

dtos_dir = "app/DTOs/Marketing"
os.makedirs(dtos_dir, exist_ok=True)

dtos = {
    "DashboardDTO": """<?php

namespace App\\DTOs\\Marketing;

class DashboardDTO
{
    public function __construct(
        public readonly CampaignMetricsDTO $campaignMetrics,
        public readonly QueueMetricsDTO $queueMetrics,
        public readonly HealthMetricsDTO $healthMetrics,
        public readonly array $activityFeed
    ) {}
}
""",
    "CampaignMetricsDTO": """<?php

namespace App\\DTOs\\Marketing;

class CampaignMetricsDTO
{
    public function __construct(
        public readonly int $activeCampaigns = 0,
        public readonly int $draftCampaigns = 0,
        public readonly int $scheduledCampaigns = 0,
        public readonly int $sendingCampaigns = 0,
        public readonly int $sentToday = 0,
        public readonly int $failedToday = 0,
        public readonly int $totalCampaigns = 0,
        public readonly float $averageSuccessRate = 0.0,
        public readonly int $totalRecipients = 0,
        public readonly int $emailsSentThisWeek = 0,
        public readonly int $emailsSentThisMonth = 0
    ) {}
}
""",
    "QueueMetricsDTO": """<?php

namespace App\\DTOs\\Marketing;

class QueueMetricsDTO
{
    public function __construct(
        public readonly array $queues = [],
        public readonly int $failedJobs = 0,
        public readonly int $workers = 0,
        public readonly int $throughput = 0,
        public readonly float $latency = 0.0,
        public readonly ?int $oldestPending = null
    ) {}
}
""",
    "HealthMetricsDTO": """<?php

namespace App\\DTOs\\Marketing;

class HealthMetricsDTO
{
    public function __construct(
        public readonly string $workerStatus = 'Unknown',
        public readonly string $mailProvider = 'None',
        public readonly string $rateLimit = 'N/A',
        public readonly string $databaseConnection = 'Unknown',
        public readonly string $cacheConnection = 'Unknown',
        public readonly string $queueConnection = 'Unknown',
        public readonly bool $storageWritable = false,
        public readonly bool $schedulerRunning = false,
        public readonly string $environment = 'production',
        public readonly string $applicationVersion = '1.0.0'
    ) {}
}
""",
    "ActivityItemDTO": """<?php

namespace App\\DTOs\\Marketing;

class ActivityItemDTO
{
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly string $description,
        public readonly string $icon,
        public readonly string $color,
        public readonly string $timestamp,
        public readonly ?string $link = null
    ) {}
}
"""
}

for name, content in dtos.items():
    with open(f"{dtos_dir}/{name}.php", "w") as f:
        f.write(content)

print("DTOs generated successfully!")

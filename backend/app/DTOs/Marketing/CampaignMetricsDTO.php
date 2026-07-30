<?php

namespace App\DTOs\Marketing;

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

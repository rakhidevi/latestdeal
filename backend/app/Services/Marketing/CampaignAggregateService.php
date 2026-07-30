<?php

namespace App\Services\Marketing;

use App\Models\EmailCampaign;
use App\Enums\CampaignState;

class CampaignAggregateService
{
    public function __construct(
        protected CampaignStateMachine $stateMachine
    ) {}

    /**
     * Change state of a campaign safely via the state machine.
     */
    public function transition(EmailCampaign $campaign, CampaignState $newState, ?string $reason = null): void
    {
        $this->stateMachine->transitionTo($campaign, $newState, $reason);
    }

    /**
     * Calculate completion progress based on immutable counters.
     * Progress = (sent + failed + cancelled) / total_recipients
     * 
     * @return float 0.0 to 100.0
     */
    public function getProgress(EmailCampaign $campaign): float
    {
        if ($campaign->total_recipients <= 0) {
            return 0.0;
        }

        $completedCount = $campaign->sent_count + $campaign->failed_count + $campaign->cancelled_count;
        $progress = ($completedCount / $campaign->total_recipients) * 100;
        
        return min(100.0, round($progress, 2));
    }

    /**
     * Calculate success rate (sent / total completed).
     * 
     * @return float 0.0 to 100.0
     */
    public function getSuccessRate(EmailCampaign $campaign): float
    {
        $completedCount = $campaign->sent_count + $campaign->failed_count;
        if ($completedCount <= 0) {
            return 0.0;
        }
        
        return round(($campaign->sent_count / $completedCount) * 100, 2);
    }

    /**
     * Estimate time remaining based on throughput and remaining recipients.
     * 
     * @return string Human readable ETA (e.g. "12m left")
     */
    public function getETA(EmailCampaign $campaign, int $throughputPerMinute): string
    {
        if ($campaign->status !== CampaignState::SENDING->value) {
            return 'N/A';
        }

        if ($throughputPerMinute <= 0) {
            return 'Calculating...';
        }

        $completedCount = $campaign->sent_count + $campaign->failed_count + $campaign->cancelled_count;
        $remaining = max(0, $campaign->total_recipients - $completedCount);

        if ($remaining === 0) {
            return 'Finishing...';
        }

        $minutesLeft = ceil($remaining / $throughputPerMinute);
        
        if ($minutesLeft < 60) {
            return "{$minutesLeft}m left";
        }
        
        $hours = floor($minutesLeft / 60);
        $mins = $minutesLeft % 60;
        return "{$hours}h {$mins}m left";
    }

    /**
     * Update internal counters when a job completes.
     */
    public function recordJobResult(EmailCampaign $campaign, string $type = 'sent'): void
    {
        // Decrement from processing/queued, increment final state
        if ($type === 'sent') {
            $campaign->increment('sent_count');
            $campaign->decrement('processing_count');
        } elseif ($type === 'failed') {
            $campaign->increment('failed_count');
            $campaign->decrement('processing_count');
        }
    }
}

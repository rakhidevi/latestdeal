<?php

namespace App\Listeners\Marketing;

use App\Events\Marketing\CampaignStateChanged;
use App\Services\AuditService;

class CampaignStateAuditListener
{
    public function handle(CampaignStateChanged $event): void
    {
        $severity = match ($event->newState->value) {
            'Completed' => 'success',
            'Failed', 'Cancelled' => 'error',
            'Paused', 'Retrying' => 'warning',
            default => 'info'
        };

        $message = "Campaign '{$event->campaign->name}' transitioned from {$event->oldState->value} to {$event->newState->value}";
        if ($event->reason) {
            $message .= " (Reason: {$event->reason})";
        }

        AuditService::log(
            action: 'Campaign State Changed',
            resource: $message,
            payload: [
                'campaign_id' => $event->campaign->id,
                'old_state' => $event->oldState->value,
                'new_state' => $event->newState->value,
                'reason' => $event->reason
            ],
            severity: $severity
        );
    }
}

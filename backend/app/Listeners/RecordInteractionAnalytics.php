<?php

namespace App\Listeners;

use App\Events\UserInteracted;
use App\Models\UserInteraction;

class RecordInteractionAnalytics
{
    /**
     * Handle the event.
     */
    public function handle(UserInteracted $event): void
    {
        UserInteraction::create([
            'user_id' => $event->userId,
            'session_id' => $event->sessionId,
            'deal_id' => $event->dealId,
            'interaction_type' => $event->interactionType,
            'source' => $event->source,
            'device' => $event->device,
            'platform' => $event->platform,
            'referrer' => $event->referrer,
            'ip_hash' => $event->ip ? hash('sha256', $event->ip) : null,
            'user_agent_hash' => $event->userAgent ? hash('sha256', $event->userAgent) : null,
            'metadata' => $event->metadata,
        ]);
    }
}

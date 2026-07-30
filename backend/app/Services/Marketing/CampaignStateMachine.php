<?php

namespace App\Services\Marketing;

use App\Models\EmailCampaign;
use App\Enums\CampaignState;
use App\Events\Marketing\CampaignStateChanged;
use Exception;

class CampaignStateMachine
{
    /**
     * Define valid state transitions.
     * key: current state, value: array of allowed next states.
     */
    protected const TRANSITIONS = [
        'Draft' => ['Validated', 'Cancelled'],
        'Validated' => ['Scheduled', 'Draft', 'Cancelled'],
        'Scheduled' => ['Queued', 'Draft', 'Cancelled', 'Paused'],
        'Queued' => ['Preparing', 'Paused', 'Cancelled', 'Failed'],
        'Preparing' => ['Sending', 'Paused', 'Cancelled', 'Failed'],
        'Sending' => ['Finishing', 'Paused', 'Cancelled', 'Failed'],
        'Finishing' => ['Completed', 'Failed'],
        'Paused' => ['Scheduled', 'Queued', 'Preparing', 'Sending', 'Cancelled'],
        'Retrying' => ['Sending', 'Failed', 'Cancelled'],
        'Failed' => ['Retrying', 'Archived'],
        'Completed' => ['Archived'],
        'Cancelled' => ['Archived'],
        'Archived' => [],
    ];

    public function transitionTo(EmailCampaign $campaign, CampaignState $newState, string $reason = null): void
    {
        $currentState = CampaignState::tryFrom($campaign->status) ?? CampaignState::DRAFT;
        
        if ($currentState === $newState) {
            return; // Already in this state
        }

        if (! $this->canTransition($currentState, $newState)) {
            throw new Exception("Invalid transition from {$currentState->value} to {$newState->value}");
        }

        $this->executeTransitionGuards($campaign, $currentState, $newState);

        // Update DB
        $campaign->status = $newState->value;
        if ($newState === CampaignState::COMPLETED) {
            $campaign->completed_at = now();
        }
        if ($newState === CampaignState::SENDING && !$campaign->started_at) {
            $campaign->started_at = now();
        }
        $campaign->save();

        // Dispatch Event for Audit and downstream processes
        CampaignStateChanged::dispatch($campaign, $currentState, $newState, $reason);
    }

    public function canTransition(CampaignState $from, CampaignState $to): bool
    {
        $allowed = self::TRANSITIONS[$from->value] ?? [];
        return in_array($to->value, $allowed);
    }

    protected function executeTransitionGuards(EmailCampaign $campaign, CampaignState $from, CampaignState $to): void
    {
        if ($to === CampaignState::SCHEDULED) {
            if (empty($campaign->subject) || empty($campaign->template)) {
                throw new Exception("Campaign must have a subject and template before scheduling.");
            }
        }

        // More complex guards (Queue health, worker checks) can be injected or checked via Services here.
    }
}

<?php

namespace App\Enums;

enum CampaignState: string
{
    case DRAFT = 'Draft';
    case VALIDATED = 'Validated';
    case SCHEDULED = 'Scheduled';
    case QUEUED = 'Queued';
    case PREPARING = 'Preparing';
    case SENDING = 'Sending';
    case FINISHING = 'Finishing';
    case COMPLETED = 'Completed';
    case CANCELLED = 'Cancelled';
    case ARCHIVED = 'Archived';
    case FAILED = 'Failed';
    case PAUSED = 'Paused';
    case RETRYING = 'Retrying';

    public function isTerminal(): bool
    {
        return match($this) {
            self::COMPLETED, self::CANCELLED, self::ARCHIVED, self::FAILED => true,
            default => false,
        };
    }
}

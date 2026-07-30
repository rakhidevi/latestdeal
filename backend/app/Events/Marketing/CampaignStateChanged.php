<?php

namespace App\Events\Marketing;

use App\Models\EmailCampaign;
use App\Enums\CampaignState;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignStateChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public EmailCampaign $campaign,
        public CampaignState $oldState,
        public CampaignState $newState,
        public ?string $reason = null
    ) {}
}

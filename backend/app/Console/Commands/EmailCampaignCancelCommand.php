<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use Illuminate\Console\Command;

class EmailCampaignCancelCommand extends Command
{
    protected $signature = 'email:campaign:cancel {campaign_id}';
    protected $description = 'Cancel an active email campaign and prevent further sending.';

    public function handle()
    {
        $campaignId = $this->argument('campaign_id');
        $campaign = EmailCampaign::findOrFail($campaignId);

        if ($campaign->status === 'Completed' || $campaign->status === 'Cancelled') {
            $this->error("Campaign is already {$campaign->status}.");
            return 1;
        }

        if ($this->confirm("Are you sure you want to cancel campaign '{$campaign->name}'? This will prevent queued jobs from sending.")) {
            $campaign->transitionTo('Cancelled');
            
            $this->info("Campaign {$campaignId} has been marked as Cancelled.");
            $this->info("Note: Any jobs currently in the queue will detect this status and skip sending automatically.");
        } else {
            $this->info("Cancellation aborted.");
        }

        return 0;
    }
}

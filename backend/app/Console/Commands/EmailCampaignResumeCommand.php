<?php

namespace App\Console\Commands;

use App\Jobs\SendCampaignEmailJob;
use App\Models\CampaignRecipient;
use App\Models\EmailCampaign;
use App\Services\Mail\RateLimiterService;
use Illuminate\Console\Command;

class EmailCampaignResumeCommand extends Command
{
    protected $signature = 'email:campaign:resume {campaign_id}';
    protected $description = 'Resume sending a paused or interrupted email campaign.';

    public function handle(RateLimiterService $rateLimiter)
    {
        $campaignId = $this->argument('campaign_id');
        $campaign = EmailCampaign::findOrFail($campaignId);

        if ($campaign->status === 'Completed' || $campaign->status === 'Cancelled') {
            $this->error("Cannot resume a campaign that is {$campaign->status}.");
            return 1;
        }

        if ($campaign->status === 'Paused') {
            $campaign->transitionTo('Sending');
        } elseif ($campaign->status !== 'Sending') {
            $campaign->status = 'Sending';
            $campaign->save();
        }

        $pendingCount = CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount === 0) {
            $this->info("No pending recipients found for campaign. Marking as completed.");
            $campaign->transitionTo('Completed');
            return 0;
        }

        $this->info("Resuming campaign: {$campaign->name}. Found {$pendingCount} pending recipients.");

        $bar = $this->output->createProgressBar($pendingCount);
        $bar->start();

        $queuedCount = 0;
        $chunkSize = config('mail.chunk_size', 100);
        $queueName = $campaign->priority;

        CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->chunkById($chunkSize, function ($recipients) use ($queueName, $rateLimiter, &$queuedCount, $bar) {
                foreach ($recipients as $recipient) {
                    $delay = $rateLimiter->getDelayForEmail($queuedCount);
                    
                    SendCampaignEmailJob::dispatch($recipient)
                        ->onQueue($queueName)
                        ->delay(now()->addSeconds($delay));
                    
                    $queuedCount++;
                    $bar->advance();
                }
            });

        $campaign->increment('queued_count', $queuedCount);
        $bar->finish();
        $this->newLine();

        $this->info("Successfully dispatched {$queuedCount} resumed emails to the '{$queueName}' queue.");
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\SendCampaignEmailJob;
use App\Models\CampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\User;
use App\Services\Mail\RateLimiterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EmailCampaignQueueCommand extends Command
{
    protected $signature = 'email:campaign:queue {campaign_id}';
    protected $description = 'Queue emails for a campaign by snapshotting recipients and dispatching jobs.';

    public function handle(RateLimiterService $rateLimiter)
    {
        $campaignId = $this->argument('campaign_id');
        $campaign = EmailCampaign::findOrFail($campaignId);

        if ($campaign->status !== 'Draft' && $campaign->status !== 'Queued') {
            $this->error("Campaign is in {$campaign->status} status. Only Draft or Queued campaigns can be started.");
            return 1;
        }

        $this->info("Initializing Campaign: {$campaign->name}");

        $campaign->transitionTo('Queued');

        // Determine recipient query based on campaign type
        $query = User::whereNotNull('email_verified_at')->whereNull('deleted_at');
        
        if ($campaign->type === 'newsletter') {
            $query->where('wants_newsletter', true);
        }

        $totalUsers = $query->count();
        $this->info("Found {$totalUsers} potential recipients. Snapshotting...");

        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        $queuedCount = 0;
        
        // Chunk configuration
        $chunkSize = config('mail.chunk_size', 100);
        $queueName = $campaign->priority; // e.g. 'low', 'critical'

        // 1. Snapshot recipients first
        $query->chunkById($chunkSize, function ($users) use ($campaign, $bar) {
            $inserts = [];
            $now = now();
            foreach ($users as $user) {
                $inserts[] = [
                    'campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'queued_at' => clone $now, // use clone to prevent reference issues if not string
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $bar->advance();
            }
            
            // Ignore duplicates via insertOrIgnore
            DB::table('campaign_recipients')->insertOrIgnore($inserts);
        });

        $bar->finish();
        $this->newLine();

        // Update campaign totals based on snapshot
        $actualRecipients = CampaignRecipient::where('campaign_id', $campaign->id)->count();
        $campaign->update([
            'total_recipients' => $actualRecipients,
        ]);

        $this->info("Snapshotted {$actualRecipients} unique recipients. Dispatching to queue [{$queueName}]...");

        $dispatchBar = $this->output->createProgressBar($actualRecipients);
        $dispatchBar->start();

        $campaign->transitionTo('Sending');

        // 2. Dispatch Jobs based on Rate Limits
        CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->chunkById($chunkSize, function ($recipients) use ($queueName, $rateLimiter, &$queuedCount, $dispatchBar) {
                foreach ($recipients as $recipient) {
                    $delay = $rateLimiter->getDelayForEmail($queuedCount);
                    
                    SendCampaignEmailJob::dispatch($recipient)
                        ->onQueue($queueName)
                        ->delay(now()->addSeconds($delay));
                    
                    $queuedCount++;
                    $dispatchBar->advance();
                }
            });

        $campaign->update(['queued_count' => $queuedCount]);
        $dispatchBar->finish();
        $this->newLine();

        $this->info("Successfully dispatched {$queuedCount} emails to the '{$queueName}' queue.");
        return 0;
    }
}

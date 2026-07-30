<?php

namespace App\Jobs;

use App\Contracts\MailProviderInterface;
use App\Models\CampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $recipient;

    /**
     * Create a new job instance.
     */
    public function __construct(CampaignRecipient $recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * Execute the job.
     */
    public function handle(MailProviderInterface $provider): void
    {
        $this->recipient->loadMissing(['campaign', 'user']);
        $campaign = $this->recipient->campaign;
        $user = $this->recipient->user;

        // Skip if campaign is cancelled
        if ($campaign->status === 'Cancelled') {
            $this->recipient->update(['status' => 'skipped']);
            $campaign->increment('skipped_count');
            return;
        }

        // Check unsubscribe/validity
        if (!$user->email_verified_at || $user->deleted_at || ($campaign->type === 'newsletter' && !$user->wants_newsletter)) {
            $this->recipient->update(['status' => 'skipped']);
            $campaign->increment('skipped_count');
            return;
        }

        try {
            $mailableClass = $campaign->mailable;
            
            // Build the mailable class dynamically
            // Variables from campaign could be passed to constructor
            $mailable = new $mailableClass($campaign->variables ?? []);

            $result = $provider->send($user, $mailable, $campaign);

            $this->recipient->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            EmailLog::create([
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'email' => $user->email,
                'status' => 'Sent',
                'provider' => $provider->getName(),
                'queue' => $this->queue,
                'attempt' => $this->attempts(),
                'message_id' => $result['message_id'] ?? null,
                'sent_at' => now(),
            ]);

            $campaign->increment('sent_count');

        } catch (\Exception $e) {
            $this->recipient->update(['status' => 'failed']);
            
            EmailLog::create([
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'email' => $user->email,
                'status' => 'Failed',
                'provider' => $provider->getName(),
                'queue' => $this->queue,
                'attempt' => $this->attempts(),
                'failed_at' => now(),
                'error' => $e->getMessage(),
            ]);

            $campaign->increment('failed_count');
            
            // Re-throw to trigger Laravel's job retry logic if necessary
            throw $e;
        }
    }
}

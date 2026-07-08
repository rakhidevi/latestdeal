<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\SocialAccount;
use App\Services\InstagramGraphService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class PublishDealToInstagramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deal;
    public $accountId;

    public function __construct(Deal $deal, $accountId)
    {
        $this->deal = $deal;
        $this->accountId = $accountId;
    }

    public function handle(): void
    {
        $account = SocialAccount::find($this->accountId);
        if (!$account || !$account->is_active) {
            return;
        }

        $instagramService = new InstagramGraphService($account);

        // API Rate Limiting for Graph API
        $executed = \Illuminate\Support\Facades\RateLimiter::attempt(
            'instagram-publish',
            5,
            function () use ($instagramService, $account) {
                try {
                    $instagramService->publishStory($this->deal);
                } catch (\Exception $e) {
                    if (str_contains($e->getMessage(), 'limit')) {
                        if ($account) {
                            $account->update(['is_active' => false]);
                        }
                    }
                }
            },
            60
        );

        if (! $executed) {
            $this->release(30);
        }
    }
}

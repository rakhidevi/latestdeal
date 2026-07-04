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
        Redis::throttle('instagram-publish')
            ->allow(5)->every(60)
            ->then(function () use ($instagramService) {
                $instagramService->publishStory($this->deal);
            }, function () {
                $this->release(30);
            });
    }
}

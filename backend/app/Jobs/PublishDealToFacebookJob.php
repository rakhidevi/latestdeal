<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\SocialAccount;
use App\Services\FacebookGraphService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class PublishDealToFacebookJob implements ShouldQueue
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

        $service = new FacebookGraphService($account);
        $caption = "🚨 {$this->deal->title}\n\nOnly {$this->deal->discounted_price}! Grab it here: " . route('deal.redirect', $this->deal->id);

        // API Rate Limiting for Graph API
        Redis::throttle('facebook-publish')
            ->allow(10)->every(60)
            ->then(function () use ($service, $caption) {
                $service->publishPost($this->deal, $caption);
            }, function () {
                $this->release(30);
            });
    }
}

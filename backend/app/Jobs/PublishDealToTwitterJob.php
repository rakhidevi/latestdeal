<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\SocialAccount;
use App\Services\TwitterApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class PublishDealToTwitterJob implements ShouldQueue
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

        $service = new TwitterApiService($account);
        $tweet = "🚨 {$this->deal->title}\n\nOnly {$this->deal->discounted_price}! Grab it here: " . route('deal.redirect', $this->deal->id);

        // API Rate Limiting
        $executed = \Illuminate\Support\Facades\RateLimiter::attempt(
            'twitter-publish',
            5,
            function () use ($service, $account, $tweet) {
                try {
                    $service->publishTweet($this->deal, $tweet);
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

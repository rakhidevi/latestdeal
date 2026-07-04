<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Services\TelegramBotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class PublishDealToTelegramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deal;
    public $accountId;

    /**
     * Create a new job instance.
     */
    public function __construct(Deal $deal, $accountId = null)
    {
        $this->deal = $deal;
        $this->accountId = $accountId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $account = \App\Models\SocialAccount::find($this->accountId);
        $telegramService = new \App\Services\TelegramBotService($account);

        // Rate Limiting: Telegram allows approx 20 messages per minute to a single group
        // We use Redis throttling to ensure this job waits if we are hitting limits.
        Redis::throttle('telegram-publish')
            ->allow(15)->every(60)
            ->then(function () use ($telegramService, $account) {
                try {
                    $telegramService->publishDeal($this->deal);
                } catch (\Exception $e) {
                    if (str_contains($e->getMessage(), '(429)') || str_contains($e->getMessage(), '(403)')) {
                        \Illuminate\Support\Facades\Log::warning("Deactivating Telegram account {$account->id} due to API Ban/Rate Limit.");
                        $account->update(['is_active' => false]);
                    }
                }
            }, function () {
                $this->release(10);
            });
    }
}

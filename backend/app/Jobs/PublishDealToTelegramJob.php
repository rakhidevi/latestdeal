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
        // We use RateLimiter which works with any cache driver (unlike Redis which crashes on shared hosting)
        $executed = \Illuminate\Support\Facades\RateLimiter::attempt(
            'telegram-publish',
            15,
            function () use ($telegramService, $account) {
                try {
                    return $telegramService->publishDeal($this->deal);
                } catch (\Throwable $e) {
                    if (str_contains($e->getMessage(), '(429)') || str_contains($e->getMessage(), '(403)')) {
                        if ($account) {
                            \Illuminate\Support\Facades\Log::warning("Deactivating Telegram account {$account->id} due to API Ban/Rate Limit.");
                            $account->update(['is_active' => false]);
                        } else {
                            \Illuminate\Support\Facades\Log::warning("Telegram API Ban/Rate Limit hit for default account.");
                        }
                    }
                    \Illuminate\Support\Facades\Log::error("Telegram Job Error: " . $e->getMessage());
                    return false;
                }
            },
            60
        );

        if ($executed === false || $executed === null) {
            $this->release(10);
        }
    }
}

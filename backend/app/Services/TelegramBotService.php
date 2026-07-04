<?php

namespace App\Services;

use App\Models\Deal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected string $botToken;
    protected string $chatId;

    public function __construct(\App\Models\SocialAccount $account = null)
    {
        if ($account) {
            $this->botToken = $account->access_token;
            $this->chatId = $account->target_id;
        } else {
            $this->botToken = env('TELEGRAM_BOT_TOKEN', 'dummy-token');
            $this->chatId = env('TELEGRAM_CHAT_ID', '@your_channel_name');
        }
    }

    /**
     * Pushes a deal to a Telegram Channel/Group.
     */
    public function publishDeal(Deal $deal): bool
    {
        // 1. Construct the Tracking URL (Redirect Engine)
        $trackingUrl = route('deal.redirect', ['deal' => $deal->id]);

        // 2. Format the message with MarkdownV2 or HTML
        // Note: The AI agent should theoretically generate a caption, but we must have a robust fallback
        // as required by REQ-AI-003
        $caption = "🚨 {$deal->title} – " . round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) . "% OFF 🖱️💙\n\n" .
                   "💸 M.R.P.: ₹{$deal->original_price}\n" .
                   "🔥 Deal Price: ₹{$deal->discounted_price}\n\n" .
                   "⭐ 4.0/5 Rated\n\n" .
                   "👉🏻 Buy Now: {$trackingUrl}\n\n" .
                   "✔️ High Quality\n✔️ Limited Time Offer\n\n" .
                   "💎 LatestDeal.in Best Value – Don't miss out on this discount!";

        // 3. Send Photo with Caption to Telegram API
        $endpoint = "https://api.telegram.org/bot{$this->botToken}/sendPhoto";

        try {
            $response = Http::attach(
                'photo', file_get_contents(public_path($deal->image_path)), 'deal.jpg'
            )->post($endpoint, [
                'chat_id' => $this->chatId,
                'caption' => $caption,
                'parse_mode' => 'Markdown',
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API Error: ' . $response->body());
                if ($response->status() === 429) {
                    throw new \Exception('Telegram Rate Limit Exceeded (429)');
                }
                if ($response->status() === 403) {
                    throw new \Exception('Telegram Bot Blocked (403)');
                }
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram Publish Exception: ' . $e->getMessage());
            return false;
        }
    }
}

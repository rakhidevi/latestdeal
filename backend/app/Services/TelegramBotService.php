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
            $this->botToken = preg_replace('/^bot/i', '', trim($account->access_token));
            
            $chatId = trim($account->target_id);
            // Defensively handle if user pasted full t.me URL
            if (preg_match('/t\.me\/([a-zA-Z0-9_]+)/', $chatId, $matches)) {
                $chatId = $matches[1];
            }
            
            if (!str_starts_with($chatId, '@') && !str_starts_with($chatId, '-')) {
                $chatId = '@' . $chatId;
            }
            $this->chatId = $chatId;
        } else {
            $this->botToken = preg_replace('/^bot/i', '', env('TELEGRAM_BOT_TOKEN', 'dummy-token'));
            $this->chatId = env('TELEGRAM_CHAT_ID', '@latestdealin');
        }
    }

    /**
     * Pushes a deal to a Telegram Channel/Group.
     */
    public function publishDeal(Deal $deal): bool
    {
        // 1. Construct the Tracking URL (Redirect Engine)
        $trackingUrl = route('deal.redirect', ['deal' => $deal->id]);

        // 2. Format the message similar to WhatsApp style
        $cleanTitle = str_replace(['*', '_', '`', '['], '', $deal->title);
        $caption = "🔥 *NEW DEAL ALERT* 🔥\n\n";
        $caption .= "🚨 *" . $cleanTitle . "*\n\n";
        $caption .= "💰 *Price:* ₹" . $deal->discounted_price . " (was ₹" . $deal->original_price . ")\n\n";
        
        if (!empty($deal->ai_caption)) {
            $cleanAiCaption = str_replace(['*', '_', '`', '['], '', $deal->ai_caption);
            $caption .= $cleanAiCaption . "\n\n";
        } else {
            $caption .= "✔️ High Quality\n✔️ Limited Time Offer\n\n";
        }
        
        $caption .= "👉 *Buy Here:* " . $trackingUrl . "\n\n";
        $caption .= "🌐 *View on LatestDeal:*\n" . route('deal.show', $deal->id);

        // Truncate to Telegram's 1024 character limit for media captions
        if (mb_strlen($caption) > 1024) {
            $caption = mb_substr($caption, 0, 1020) . '...';
        }

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

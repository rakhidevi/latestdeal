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
        
        // Calculate discount safely to avoid division by zero
        $discountPercent = 0;
        if ($deal->original_price > 0) {
            $discountPercent = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
        }

        $caption = "🔥 *NEW DEAL ALERT* 🔥\n\n";
        $caption .= "🚨 *" . $cleanTitle . "*\n\n";
        
        if ($discountPercent > 0) {
            $caption .= "💰 *Price:* ₹" . $deal->discounted_price . " (was ₹" . $deal->original_price . ")  ✅ *" . $discountPercent . "% OFF!*\n\n";
        } else {
            $caption .= "💰 *Price:* ₹" . $deal->discounted_price . "\n\n";
        }
        
        if (!empty($deal->ai_caption)) {
            $cleanAiCaption = str_replace(['*', '_', '`', '['], '', $deal->ai_caption);
            // Keep AI caption much shorter so it's not a wall of text
            if (mb_strlen($cleanAiCaption) > 250) {
                $cleanAiCaption = mb_substr($cleanAiCaption, 0, 247) . '...';
            }
            $caption .= "✨ " . $cleanAiCaption . "\n\n";
        } else {
            $caption .= "✔️ Premium Quality\n✔️ Limited Time Offer\n\n";
        }
        
        $caption .= "🏃‍♂️ *Hurry! Grab it here:* " . $trackingUrl . "\n\n";
        $caption .= "🌐 *For all LatestDeal: Visit*\nhttps://latestdeal.in/";

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

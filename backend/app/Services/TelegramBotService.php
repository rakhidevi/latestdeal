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
            $this->botToken = preg_replace('/^bot/i', '', trim($account->access_token ?? ''));
            
            $chatId = trim($account->target_id ?? '');
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

        // 2. Format the message (HTML mode)
        $cleanTitle = htmlspecialchars($deal->title);
        
        // Calculate discount safely to avoid division by zero
        $discountPercent = 0;
        if ($deal->original_price > 0) {
            $discountPercent = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
        }

        $caption = "🔥 <b>NEW DEAL ALERT</b> 🔥\n\n";
        $caption .= "🚨 <b>" . $cleanTitle . "</b>\n\n";
        
        if ($discountPercent > 0) {
            $caption .= "💰 <b>Price:</b> ₹" . $deal->discounted_price . " (was ₹" . $deal->original_price . ")  ✅ <b>" . $discountPercent . "% OFF!</b>\n\n";
        } else {
            $caption .= "💰 <b>Price:</b> ₹" . $deal->discounted_price . "\n\n";
        }
        
        if (!empty($deal->ai_caption)) {
            $cleanAiCaption = htmlspecialchars($deal->ai_caption);
            // Keep AI caption much shorter so it's not a wall of text
            if (mb_strlen($cleanAiCaption) > 250) {
                $cleanAiCaption = mb_substr($cleanAiCaption, 0, 247) . '...';
            }
            $caption .= "✨ " . $cleanAiCaption . "\n\n";
        } else {
            $caption .= "✔️ Premium Quality\n✔️ Limited Time Offer\n\n";
        }
        
        $caption .= "🏃‍♂️ <b>Hurry! Grab it here:</b> " . $trackingUrl . "\n\n";
        $caption .= "🌐 <b>For all LatestDeal: Visit</b>\nhttps://latestdeal.in/";

        // 3. Send Photo with Caption to Telegram API
        $endpoint = "https://api.telegram.org/bot{$this->botToken}/sendPhoto";

        $imagePath = $deal->image_path;
        $isUrl = filter_var($imagePath, FILTER_VALIDATE_URL) !== false;
        
        try {
            if ($isUrl) {
                $imgResponse = Http::timeout(10)->get($imagePath);
                if (!$imgResponse->successful()) {
                    throw new \Exception("HTTP status " . $imgResponse->status());
                }
                $imageContents = $imgResponse->body();
            } else {
                $imageContents = file_get_contents(public_path($imagePath));
            }
        } catch (\Throwable $e) {
            Log::error('Telegram Publish Error: Could not fetch image from ' . $imagePath . '. Error: ' . $e->getMessage());
            return false;
        }

        try {
            $response = Http::attach(
                'photo', $imageContents, 'deal.jpg'
            )->post($endpoint, [
                'chat_id' => $this->chatId,
                'caption' => $caption,
                'parse_mode' => 'HTML',
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
        } catch (\Throwable $e) {
            Log::error('Telegram Publish Exception: ' . $e->getMessage());
            return false;
        }
    }
}

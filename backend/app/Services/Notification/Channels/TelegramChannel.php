<?php

namespace App\Services\Notification\Channels;

use App\Contracts\NotificationChannelInterface;
use App\Services\Notification\ImmutableNotificationPayload;
use App\Models\Subscriber;
use App\Models\NotificationChannel;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramChannel implements NotificationChannelInterface
{
    public function getName(): string
    {
        return 'telegram';
    }

    public function send(Subscriber $subscriber, ImmutableNotificationPayload $payload, string $traceId): bool
    {
        $channelRecord = NotificationChannel::where('subscriber_id', $subscriber->id)
            ->where('type', 'telegram')
            ->where('enabled', true)
            ->first();

        $botToken = config('services.telegram.bot_token') ?? env('TELEGRAM_BOT_TOKEN');
        $chatId = $channelRecord ? $channelRecord->destination : env('TELEGRAM_CHANNEL_ID');

        if (empty($botToken) || empty($chatId)) {
            return false;
        }

        try {
            $text = "*{$payload->getTitle()}*\n\n{$payload->getBody()}\n\n[👉 View Deal]({$payload->getUrl()})";

            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => false,
            ]);

            if ($response->successful()) {
                NotificationLog::create([
                    'trace_id' => $traceId,
                    'subscriber_id' => $subscriber->id,
                    'channel' => $this->getName(),
                    'notification_type' => $payload->getTag(),
                    'status' => 'sent',
                    'attempts' => 1,
                    'response' => 'Telegram Bot message sent',
                ]);
                return true;
            }

            NotificationLog::create([
                'trace_id' => $traceId,
                'subscriber_id' => $subscriber->id,
                'channel' => $this->getName(),
                'notification_type' => $payload->getTag(),
                'status' => 'failed',
                'attempts' => 1,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("TelegramChannel Error: " . $e->getMessage());
            return false;
        }
    }
}

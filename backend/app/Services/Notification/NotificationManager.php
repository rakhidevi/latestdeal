<?php

namespace App\Services\Notification;

use App\Contracts\NotificationInterface;
use App\Contracts\NotificationChannelInterface;
use App\Services\Notification\Channels\WebPushChannel;
use App\Services\Notification\Channels\EmailChannel;
use App\Services\Notification\Channels\TelegramChannel;
use App\Models\Subscriber;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class NotificationManager
{
    /** @var NotificationChannelInterface[] */
    private array $channels = [];

    public function __construct()
    {
        // Register active delivery channels
        $this->registerChannel(new WebPushChannel());
        $this->registerChannel(new EmailChannel());
        $this->registerChannel(new TelegramChannel());
    }

    public function registerChannel(NotificationChannelInterface $channel): self
    {
        $this->channels[$channel->getName()] = $channel;
        return $this;
    }

    public function send(Subscriber $subscriber, NotificationInterface $notification, ?string $traceId = null): array
    {
        $traceId = $traceId ?? Str::uuid()->toString();

        // 1. Check Subscriber Preference
        if (!$subscriber->prefers($notification->getType())) {
            Log::info("Notification {$notification->getType()} skipped: User disabled category preference.");
            return ['status' => 'skipped_by_user_preference', 'trace_id' => $traceId];
        }

        // 2. Build Immutable Payload
        $payload = $notification->toPayload($subscriber);

        $results = [];

        // 3. Dispatch across registered channels
        foreach ($this->channels as $name => $channel) {
            try {
                $success = $channel->send($subscriber, $payload, $traceId);
                $results[$name] = $success ? 'sent' : 'failed';
            } catch (\Exception $e) {
                Log::error("NotificationManager Channel '{$name}' Error: " . $e->getMessage());
                $results[$name] = 'error';
            }
        }

        return [
            'trace_id' => $traceId,
            'type' => $notification->getType(),
            'priority' => $notification->getPriority(),
            'channels' => $results,
        ];
    }
}

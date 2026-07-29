<?php

namespace App\Services\Notification\Channels;

use App\Contracts\NotificationChannelInterface;
use App\Services\Notification\ImmutableNotificationPayload;
use App\Models\Subscriber;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailChannel implements NotificationChannelInterface
{
    public function getName(): string
    {
        return 'email';
    }

    public function send(Subscriber $subscriber, ImmutableNotificationPayload $payload, string $traceId): bool
    {
        if (empty($subscriber->email)) {
            return false;
        }

        try {
            Mail::raw(
                "{$payload->getTitle()}\n\n{$payload->getBody()}\n\nView Deal: {$payload->getUrl()}",
                function ($message) use ($subscriber, $payload) {
                    $message->to($subscriber->email)
                            ->subject($payload->getTitle());
                }
            );

            NotificationLog::create([
                'trace_id' => $traceId,
                'subscriber_id' => $subscriber->id,
                'channel' => $this->getName(),
                'notification_type' => $payload->getTag(),
                'status' => 'sent',
                'attempts' => 1,
                'response' => 'Email sent via Laravel Mailer',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("EmailChannel Error: " . $e->getMessage());

            NotificationLog::create([
                'trace_id' => $traceId,
                'subscriber_id' => $subscriber->id,
                'channel' => $this->getName(),
                'notification_type' => $payload->getTag(),
                'status' => 'failed',
                'attempts' => 1,
                'response' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

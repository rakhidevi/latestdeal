<?php

namespace App\Contracts;

use App\Services\Notification\ImmutableNotificationPayload;
use App\Models\Subscriber;

interface NotificationChannelInterface
{
    public function getName(): string; // web_push, email, telegram, whatsapp

    public function send(Subscriber $subscriber, ImmutableNotificationPayload $payload, string $traceId): bool;
}

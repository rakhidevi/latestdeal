<?php

namespace App\Contracts;

use App\Services\Notification\ImmutableNotificationPayload;
use App\Models\Subscriber;

interface NotificationInterface
{
    public function getType(): string;

    public function getPriority(): string; // low, normal, high, critical

    public function toPayload(Subscriber $subscriber): ImmutableNotificationPayload;
}

<?php

namespace App\Services\Studio;

class NotificationService
{
    public function pushAlert(string $level, string $message, array $context = []): void
    {
        // Level: 'INFO', 'WARNING', 'CRITICAL'
        // Persist to Studio DB and broadcast via WebSockets.
    }

    public function getActiveAlerts(): array
    {
        return [];
    }
}

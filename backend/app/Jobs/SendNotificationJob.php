<?php

namespace App\Jobs;

use App\Contracts\NotificationInterface;
use App\Models\Subscriber;
use App\Services\Notification\NotificationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 1800]; // 1 min, 5 min, 30 min retry backoff

    private int $subscriberId;
    private NotificationInterface $notification;
    private string $traceId;

    public function __construct(int $subscriberId, NotificationInterface $notification, ?string $traceId = null)
    {
        $this->subscriberId = $subscriberId;
        $this->notification = $notification;
        $this->traceId = $traceId ?? Str::uuid()->toString();

        // Assign queue based on notification priority
        if ($notification->getPriority() === 'high' || $notification->getPriority() === 'critical') {
            $this->onQueue('high');
        } else {
            $this->onQueue('default');
        }
    }

    public function handle(NotificationManager $manager): void
    {
        $subscriber = Subscriber::find($this->subscriberId);

        if (!$subscriber || $subscriber->status !== 'active') {
            return;
        }

        $manager->send($subscriber, $this->notification, $this->traceId);
    }
}

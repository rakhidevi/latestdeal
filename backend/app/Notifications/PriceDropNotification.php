<?php

namespace App\Notifications;

use App\Contracts\NotificationInterface;
use App\Services\Notification\ImmutableNotificationPayload;
use App\Models\Subscriber;
use App\Models\Deal;

class PriceDropNotification implements NotificationInterface
{
    private Deal $deal;

    public function __construct(Deal $deal)
    {
        $this->deal = $deal;
    }

    public function getType(): string
    {
        return 'price_drop';
    }

    public function getPriority(): string
    {
        return 'high';
    }

    public function toPayload(Subscriber $subscriber): ImmutableNotificationPayload
    {
        return ImmutableNotificationPayload::builder()
            ->title("🔥 Price Drop Alert: {$this->deal->title}")
            ->body("Price dropped to ₹" . number_format($this->deal->discounted_price) . "! Grab it before it sells out.")
            ->url(url('/go/' . $this->deal->id))
            ->image($this->deal->image_url ?? null)
            ->tag("deal-{$this->deal->id}")
            ->addAction('view_deal', 'View Deal')
            ->addAction('dismiss', 'Dismiss')
            ->extra([
                'deal_id' => $this->deal->id,
                'discounted_price' => $this->deal->discounted_price,
            ])
            ->build();
    }
}

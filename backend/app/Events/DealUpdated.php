<?php

namespace App\Events;

use App\Models\Deal;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Deal $deal;
    public float $new_price;
    public ?float $original_price;

    public function __construct(Deal $deal)
    {
        $this->deal = $deal;
        $this->new_price = (float)$deal->discounted_price;
        $this->original_price = $deal->original_price ? (float)$deal->original_price : null;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('deals.' . $this->deal->id),
        ];
    }
}


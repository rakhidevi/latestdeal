<?php

namespace App\Events;

use App\Models\Deal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealIngested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $deal;

    public function __construct(Deal $deal)
    {
        $this->deal = $deal;
    }
}

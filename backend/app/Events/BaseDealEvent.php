<?php
namespace App\Events;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Deal;

abstract class BaseDealEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $deal;
    public $correlationId;
    public $provider;
    public $payloadVersion;
    public $metadata;

    public function __construct(Deal $deal, string $correlationId, string $provider = 'unknown', string $payloadVersion = '1.0', array $metadata = [])
    {
        $this->deal = $deal;
        $this->correlationId = $correlationId;
        $this->provider = $provider;
        $this->payloadVersion = $payloadVersion;
        $this->metadata = $metadata;
        $this->logEvent();
    }
    
    protected function logEvent()
    {
        $event = class_basename($this);
        \App\Models\DealEvent::create([
            'deal_id' => $this->deal->id,
            'event' => $event,
            'correlation_id' => $this->correlationId,
        ]);
    }
}

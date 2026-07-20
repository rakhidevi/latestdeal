<?php
namespace App\Events;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Deal;

abstract class BaseDealEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public \;
    public \;
    public \;
    public \;
    public \;

    public function __construct(Deal \, string \, string \ = 'unknown', string \ = '1.0', array \ = [])
    {
        \->deal = \;
        \->correlationId = \;
        \->provider = \;
        \->payloadVersion = \;
        \->metadata = \;
        \->logEvent();
    }
    
    protected function logEvent()
    {
        \ = class_basename(\);
        \App\Models\DealEvent::create([
            'deal_id' => \->deal->id,
            'event' => \,
            'correlation_id' => \->correlationId,
        ]);
    }
}

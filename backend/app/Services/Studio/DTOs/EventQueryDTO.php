<?php

namespace App\Services\Studio\DTOs;

class EventQueryDTO
{
    public int $limit = 50;
    public ?string $cursor = null;
    public ?string $from = null;
    public ?string $to = null;
    public ?string $provider = null;
    public ?string $strategy = null;
    public ?string $worker = null;
    public ?string $severity = null;
    public ?string $eventType = null;
    public ?string $traceId = null;
    public ?string $correlationId = null;

    public function __construct(array $data = [])
    {
        $this->limit = $data['limit'] ?? 50;
        $this->cursor = $data['cursor'] ?? null;
        $this->from = $data['from'] ?? null;
        $this->to = $data['to'] ?? null;
        $this->provider = $data['provider'] ?? null;
        $this->strategy = $data['strategy'] ?? null;
        $this->worker = $data['worker'] ?? null;
        $this->severity = $data['severity'] ?? null;
        $this->eventType = $data['eventType'] ?? null;
        $this->traceId = $data['traceId'] ?? null;
        $this->correlationId = $data['correlationId'] ?? null;
    }
}

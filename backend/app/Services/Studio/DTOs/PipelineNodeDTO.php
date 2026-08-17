<?php

namespace App\Services\Studio\DTOs;

class PipelineNodeDTO
{
    public string $stage;
    public string $display_name;
    public string $status; // HEALTHY, WARNING, CRITICAL, OFFLINE
    public string $success_rate;
    public string $failure_rate;
    public int $queue_depth;
    public int $average_latency_ms;
    public int $active_workers;
    public int $events_processed;
    public int $events_failed;
    public string $last_updated;
    public string $trend; // improving, stable, degrading
    public ?string $last_failed_trace_id;

    public function __construct(array $data)
    {
        $this->stage = $data['stage'];
        $this->display_name = $data['display_name'];
        $this->status = $data['status'] ?? 'OFFLINE';
        $this->success_rate = $data['success_rate'] ?? '0%';
        $this->failure_rate = $data['failure_rate'] ?? '0%';
        $this->queue_depth = $data['queue_depth'] ?? 0;
        $this->average_latency_ms = $data['average_latency_ms'] ?? 0;
        $this->active_workers = $data['active_workers'] ?? 0;
        $this->events_processed = $data['events_processed'] ?? 0;
        $this->events_failed = $data['events_failed'] ?? 0;
        $this->last_updated = $data['last_updated'] ?? now()->toIsoString();
        $this->trend = $data['trend'] ?? 'stable';
        $this->last_failed_trace_id = $data['last_failed_trace_id'] ?? null;
    }
}

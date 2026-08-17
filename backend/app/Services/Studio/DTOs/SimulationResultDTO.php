<?php

namespace App\Services\Studio\DTOs;

class SimulationResultDTO
{
    public int $total_payloads_processed;
    public int $passed_count;
    public int $failed_count;
    public float $pass_rate_percentage;
    public int $average_latency_ms;
    public array $sample_failures;
    public array $sample_passes;

    public function __construct(array $data)
    {
        $this->total_payloads_processed = $data['total_payloads_processed'] ?? 0;
        $this->passed_count = $data['passed_count'] ?? 0;
        $this->failed_count = $data['failed_count'] ?? 0;
        $this->pass_rate_percentage = $data['pass_rate_percentage'] ?? 0.0;
        $this->average_latency_ms = $data['average_latency_ms'] ?? 0;
        $this->sample_failures = $data['sample_failures'] ?? [];
        $this->sample_passes = $data['sample_passes'] ?? [];
    }
}

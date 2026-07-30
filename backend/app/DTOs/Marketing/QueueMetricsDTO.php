<?php

namespace App\DTOs\Marketing;

class QueueMetricsDTO
{
    public function __construct(
        public readonly array $queues = [],
        public readonly int $failedJobs = 0,
        public readonly int $workers = 0,
        public readonly int $throughput = 0,
        public readonly float $latency = 0.0,
        public readonly ?int $oldestPending = null
    ) {}
}

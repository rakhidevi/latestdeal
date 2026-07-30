<?php

namespace App\Contracts;

interface QueueProviderInterface
{
    public function getQueueSize(string  = \'default\'): int;
    public function getFailedJobCount(): int;
    public function getThroughput(): int;
    public function getLatency(): float;
    public function getOldestPendingTimestamp(string  = \'default\'): ?int;
}

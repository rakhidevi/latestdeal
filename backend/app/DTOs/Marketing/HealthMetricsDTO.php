<?php

namespace App\DTOs\Marketing;

class HealthMetricsDTO
{
    public function __construct(
        public readonly string $workerStatus = 'Unknown',
        public readonly string $mailProvider = 'None',
        public readonly string $rateLimit = 'N/A',
        public readonly string $databaseConnection = 'Unknown',
        public readonly string $cacheConnection = 'Unknown',
        public readonly string $queueConnection = 'Unknown',
        public readonly bool $storageWritable = false,
        public readonly bool $schedulerRunning = false,
        public readonly string $schedulerLastRun = 'Never',
        public readonly string $mailLastSuccess = 'Never',
        public readonly string $mailLastFailure = 'Never',
        public readonly int $mailConsecutiveFailures = 0,
        public readonly string $phpVersion = 'Unknown',
        public readonly string $laravelVersion = 'Unknown',
        public readonly string $diskUsage = 'Unknown',
        public readonly string $memoryUsage = 'Unknown',
        public readonly string $environment = 'production',
        public readonly string $applicationVersion = '1.0.0'
    ) {}
}

<?php

namespace App\Services\Studio;

class OperationsService
{
    protected PipelineService $pipelineService;
    protected EventService $eventService;
    
    // In a real system, these might be individual services (e.g. EconomicsService, WorkerService)
    // Here we inject the ones we have and simulate the rest to provide composition logic.

    public function __construct(PipelineService $pipelineService, EventService $eventService)
    {
        $this->pipelineService = $pipelineService;
        $this->eventService = $eventService;
    }

    public function getExecutiveHealth(): array
    {
        return [
            'platform_status' => 'HEALTHY',
            'active_workers' => 45,
            'queue_depth' => 1250,
            'revenue_today' => 12450.00,
            'rollout_percent' => 25,
            'active_alerts' => 2,
        ];
    }

    public function getProviderHealth(): array
    {
        return [
            [
                'provider' => 'Amazon',
                'availability' => '99.8%',
                'selector_health' => '98%',
                'captcha_rate' => '0.2%',
                'latency' => '1.2s',
                'status' => 'HEALTHY'
            ],
            [
                'provider' => 'Flipkart',
                'availability' => '99.5%',
                'selector_health' => '95%',
                'captcha_rate' => '1.5%',
                'latency' => '2.1s',
                'status' => 'WARNING'
            ]
        ];
    }

    public function getQueueHealth(): array
    {
        return [
            'discovery' => ['waiting' => 450, 'processing' => 20, 'failed' => 2, 'retries' => 5],
            'extraction' => ['waiting' => 1250, 'processing' => 45, 'failed' => 12, 'retries' => 24],
            'validation' => ['waiting' => 300, 'processing' => 15, 'failed' => 0, 'retries' => 0],
            'publishing' => ['waiting' => 85, 'processing' => 5, 'failed' => 1, 'retries' => 1],
        ];
    }

    public function getWorkerMetrics(): array
    {
        return [
            ['id' => 'worker-ext-1', 'type' => 'Extraction', 'cpu' => '45%', 'ram' => '1.2GB', 'current_trace' => 'trace-abc', 'queue' => 'extraction'],
            ['id' => 'worker-ext-2', 'type' => 'Extraction', 'cpu' => '92%', 'ram' => '3.8GB', 'current_trace' => 'trace-xyz', 'queue' => 'extraction'],
            ['id' => 'worker-disc-1', 'type' => 'Discovery', 'cpu' => '15%', 'ram' => '800MB', 'current_trace' => 'trace-123', 'queue' => 'discovery'],
        ];
    }

    public function getRevenueMetrics(): array
    {
        return [
            'today' => '₹12,450',
            'this_week' => '₹84,200',
            'roi' => '425%',
            'cost_per_crawl' => '₹0.04',
            'yield' => '2.8%'
        ];
    }

    public function getRolloutStatus(): array
    {
        return [
            'traffic' => '25%',
            'mode' => 'Canary',
            'automatic_rollback' => 'Enabled',
            'last_rollback' => 'None'
        ];
    }

    public function getActiveAlerts(): array
    {
        return [
            ['id' => 'alert-1', 'type' => 'WARNING', 'message' => 'Selector degraded on Flipkart', 'related_trace' => 'trace-xyz', 'timestamp' => now()->subMinutes(10)->diffForHumans()],
            ['id' => 'alert-2', 'type' => 'CRITICAL', 'message' => 'Memory warning on worker-ext-2', 'related_trace' => 'worker-ext-2', 'timestamp' => now()->subMinutes(2)->diffForHumans()]
        ];
    }
}

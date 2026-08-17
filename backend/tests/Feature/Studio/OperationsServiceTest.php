<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\OperationsService;
use App\Services\Studio\PipelineService;
use App\Services\Studio\EventService;

class OperationsServiceTest extends TestCase
{
    protected OperationsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // In reality these would be mocked or hit test databases
        $eventService = new EventService();
        $pipelineService = new PipelineService($eventService);
        
        $this->service = new OperationsService($pipelineService, $eventService);
    }

    public function test_it_returns_executive_health_metrics()
    {
        $health = $this->service->getExecutiveHealth();
        
        $this->assertArrayHasKey('platform_status', $health);
        $this->assertArrayHasKey('queue_depth', $health);
        $this->assertArrayHasKey('active_alerts', $health);
        
        $this->assertContains($health['platform_status'], ['HEALTHY', 'WARNING', 'CRITICAL']);
    }

    public function test_it_returns_provider_health()
    {
        $providers = $this->service->getProviderHealth();
        
        $this->assertIsArray($providers);
        $this->assertGreaterThan(0, count($providers));
        
        $this->assertArrayHasKey('provider', $providers[0]);
        $this->assertArrayHasKey('status', $providers[0]);
    }

    public function test_it_returns_queue_health()
    {
        $queues = $this->service->getQueueHealth();
        
        $this->assertArrayHasKey('discovery', $queues);
        $this->assertArrayHasKey('extraction', $queues);
        
        $this->assertArrayHasKey('waiting', $queues['discovery']);
        $this->assertArrayHasKey('processing', $queues['discovery']);
    }

    public function test_it_returns_active_alerts()
    {
        $alerts = $this->service->getActiveAlerts();
        
        $this->assertIsArray($alerts);
        if (count($alerts) > 0) {
            $this->assertArrayHasKey('type', $alerts[0]);
            $this->assertArrayHasKey('message', $alerts[0]);
            $this->assertArrayHasKey('related_trace', $alerts[0]);
        }
    }
}

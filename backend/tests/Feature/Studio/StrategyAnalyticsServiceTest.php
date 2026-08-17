<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\StrategyAnalyticsService;

class StrategyAnalyticsServiceTest extends TestCase
{
    protected StrategyAnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StrategyAnalyticsService();
    }

    public function test_it_returns_automated_insights()
    {
        $insights = $this->service->getAutomatedInsights();
        
        $this->assertIsArray($insights);
        $this->assertNotEmpty($insights);
        
        $this->assertArrayHasKey('title', $insights[0]);
        $this->assertArrayHasKey('value', $insights[0]);
        $this->assertArrayHasKey('trace_id', $insights[0]);
    }

    public function test_it_returns_funnel_metrics()
    {
        $metrics = $this->service->getFunnelMetrics('strategy');
        
        $this->assertIsArray($metrics);
        $this->assertNotEmpty($metrics);
        
        $row = $metrics[0];
        
        $this->assertArrayHasKey('dimension', $row);
        $this->assertArrayHasKey('generated', $row);
        $this->assertArrayHasKey('published', $row);
        $this->assertArrayHasKey('revenue', $row);
        $this->assertArrayHasKey('roi', $row);
        $this->assertArrayHasKey('dimension_id', $row);
    }
}

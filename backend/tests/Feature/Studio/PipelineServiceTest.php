<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\PipelineService;
use App\Services\Studio\EventService;

class PipelineServiceTest extends TestCase
{
    protected PipelineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // EventService is a mock dependency if we wanted to mock it,
        // but here it returns simulated deterministic data suitable for testing aggregation.
        $this->service = new PipelineService(new EventService());
    }

    public function test_it_aggregates_into_nine_stages()
    {
        $nodes = $this->service->getPipelineNodes('1h');
        
        $this->assertCount(9, $nodes);
        
        // Assert stages exist
        $stageIds = array_map(fn($n) => $n->stage, $nodes);
        $this->assertContains('DISCOVERY', $stageIds);
        $this->assertContains('EXTRACTION', $stageIds);
        $this->assertContains('VALIDATION', $stageIds);
        $this->assertContains('DECISION', $stageIds);
        $this->assertContains('PUBLISHING', $stageIds);
        $this->assertContains('REVENUE', $stageIds);
    }

    public function test_it_calculates_status_and_metrics_correctly()
    {
        $nodes = $this->service->getPipelineNodes('1h');
        
        // Find EXTRACTION node (mock data typically has 1 success, 1 failure for EXTRACTION)
        $extractionNode = collect($nodes)->firstWhere('stage', 'EXTRACTION');
        
        $this->assertNotNull($extractionNode);
        $this->assertEquals(2, $extractionNode->events_processed);
        $this->assertEquals(1, $extractionNode->events_failed);
        $this->assertEquals('50.0%', $extractionNode->success_rate);
        
        // 50% success rate should map to CRITICAL based on threshold rules
        $this->assertEquals('CRITICAL', $extractionNode->status);
        $this->assertNotNull($extractionNode->last_failed_trace_id);
    }

    public function test_it_handles_missing_stages()
    {
        $nodes = $this->service->getPipelineNodes('1h');
        
        // Find COMPATIBILITY node which typically has 0 events in the mock
        $compatNode = collect($nodes)->firstWhere('stage', 'COMPATIBILITY');
        
        $this->assertNotNull($compatNode);
        $this->assertEquals(0, $compatNode->events_processed);
        $this->assertEquals('OFFLINE', $compatNode->status);
    }
}

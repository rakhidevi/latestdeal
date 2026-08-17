<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\TimelineService;

class TimelineServiceTest extends TestCase
{
    protected TimelineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TimelineService();
    }

    public function test_it_reconstructs_timeline_graph()
    {
        $traceId = 'trace-test-123';
        $graph = $this->service->reconstruct($traceId);

        $this->assertEquals($traceId, $graph['trace_id']);
        
        // Assert nodes
        $this->assertCount(4, $graph['nodes']);
        
        // Verify specific node structure
        $discoveryNode = collect($graph['nodes'])->firstWhere('type', 'DISCOVERY');
        $this->assertNotNull($discoveryNode);
        $this->assertEquals('success', $discoveryNode['status']);
        $this->assertArrayHasKey('metrics', $discoveryNode);
        
        // Assert edges
        $this->assertCount(3, $graph['edges']);
        
        // Verify branching
        $replayEdge = collect($graph['edges'])->firstWhere('target', 'node-4-replay');
        $this->assertNotNull($replayEdge);
        $this->assertEquals('node-3', $replayEdge['source']);
        $this->assertEquals('Manual Replay', $replayEdge['label']);
    }
}

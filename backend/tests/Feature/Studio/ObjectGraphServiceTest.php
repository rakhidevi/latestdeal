<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\ObjectGraphService;

class ObjectGraphServiceTest extends TestCase
{
    protected ObjectGraphService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ObjectGraphService();
    }

    public function test_it_resolves_an_arbitrary_query_into_a_root_node()
    {
        $graph = $this->service->resolveGraph('target-1234');
        
        $this->assertIsArray($graph);
        $this->assertEquals('Trace', $graph['type']);
        $this->assertArrayHasKey('id', $graph);
        $this->assertArrayHasKey('children', $graph);
    }

    public function test_it_builds_a_recursive_relationship_tree()
    {
        $graph = $this->service->resolveGraph('B08F7PTF54'); // Simulated ASIN query
        
        // Assert Root is Trace
        $this->assertEquals('Trace', $graph['type']);
        
        // Assert it has SearchTarget child
        $this->assertNotEmpty($graph['children']);
        $searchTarget = $graph['children'][0];
        $this->assertEquals('SearchTargetDTO', $searchTarget['type']);
        
        // Assert SearchTarget has UniversalProduct child
        $this->assertNotEmpty($searchTarget['children']);
        $product = $searchTarget['children'][0];
        $this->assertEquals('UniversalProductDTO', $product['type']);
        $this->assertEquals('B08F7PTF54', $product['payload']['asin']);
    }
}

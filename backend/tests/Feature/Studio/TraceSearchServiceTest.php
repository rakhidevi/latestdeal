<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\TraceSearchService;

class TraceSearchServiceTest extends TestCase
{
    protected TraceSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TraceSearchService();
    }

    public function test_it_resolves_uuids_correctly()
    {
        // Target UUID
        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $results = $this->service->resolve($uuid);

        $this->assertCount(1, $results);
        $this->assertEquals('Exact UUID', $results[0]['match_type']);
        $this->assertEquals($uuid, $results[0]['entity']);
        $this->assertStringStartsWith('trace-', $results[0]['trace_id']);
    }

    public function test_it_resolves_asins_correctly()
    {
        // Amazon ASIN
        $asin = 'B08F7PTF54';
        $results = $this->service->resolve($asin);

        // Simulated resolver returns 2 traces for an ASIN
        $this->assertCount(2, $results);
        $this->assertEquals('ASIN / FSN', $results[0]['match_type']);
        $this->assertEquals($asin, $results[0]['entity']);
    }

    public function test_it_resolves_metadata_correctly()
    {
        // Provider or Strategy name
        $query = 'Amazon Premium Strategy';
        $results = $this->service->resolve($query);

        $this->assertCount(1, $results);
        $this->assertEquals('Metadata Search', $results[0]['match_type']);
        $this->assertEquals($query, $results[0]['entity']);
    }
}

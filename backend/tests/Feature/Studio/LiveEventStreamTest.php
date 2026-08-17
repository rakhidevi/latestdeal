<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\EventService;
use App\Services\Studio\DTOs\EventQueryDTO;

class LiveEventStreamTest extends TestCase
{
    protected EventService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EventService();
    }

    public function test_it_fetches_events_with_limit()
    {
        $query = new EventQueryDTO(['limit' => 5]);
        $events = $this->service->getEvents($query);

        $this->assertCount(5, $events);
        $this->assertArrayHasKey('id', $events[0]);
        $this->assertArrayHasKey('timestamp', $events[0]);
        $this->assertArrayHasKey('type', $events[0]);
    }

    public function test_it_filters_by_provider()
    {
        $query = new EventQueryDTO(['provider' => 'Flipkart']);
        $events = $this->service->getEvents($query);

        $this->assertGreaterThan(0, count($events));
        foreach ($events as $event) {
            $this->assertEquals('Flipkart', $event['provider']);
        }
    }

    public function test_it_filters_by_severity()
    {
        $query = new EventQueryDTO(['severity' => 'CRITICAL']);
        $events = $this->service->getEvents($query);

        $this->assertGreaterThan(0, count($events));
        foreach ($events as $event) {
            $this->assertEquals('CRITICAL', $event['level']);
        }
    }

    public function test_it_filters_by_multiple_criteria()
    {
        $query = new EventQueryDTO([
            'provider' => 'Amazon',
            'severity' => 'SUCCESS'
        ]);
        $events = $this->service->getEvents($query);

        $this->assertGreaterThan(0, count($events));
        foreach ($events as $event) {
            $this->assertEquals('Amazon', $event['provider']);
            $this->assertEquals('SUCCESS', $event['level']);
        }
    }
}

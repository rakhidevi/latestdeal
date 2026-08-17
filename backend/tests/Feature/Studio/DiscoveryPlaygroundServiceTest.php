<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\DiscoveryPlaygroundService;

class DiscoveryPlaygroundServiceTest extends TestCase
{
    protected DiscoveryPlaygroundService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DiscoveryPlaygroundService();
    }

    public function test_it_generates_amazon_urls_correctly()
    {
        $constraints = [
            'brand' => 'Nike',
            'min_discount' => 30
        ];

        $result = $this->service->simulateDiscovery('Amazon', $constraints);

        $this->assertEquals('Amazon', $result['provider']);
        $this->assertArrayHasKey('rh', $result['parameters']);
        $this->assertStringContainsString('p_89:Nike', $result['parameters']['rh']);
        $this->assertStringContainsString('p_8:30-100', $result['parameters']['rh']);
        
        $this->assertNotEmpty($result['generated_urls']);
        $this->assertStringContainsString('amazon.in/s?rh=', $result['generated_urls'][0]);
    }

    public function test_it_generates_flipkart_urls_correctly()
    {
        $constraints = [
            'node_id' => 'abc',
            'min_discount' => 50
        ];

        $result = $this->service->simulateDiscovery('Flipkart', $constraints);

        $this->assertEquals('Flipkart', $result['provider']);
        
        $this->assertNotEmpty($result['generated_urls']);
        $this->assertStringContainsString('flipkart.com/search', $result['generated_urls'][0]);
        $this->assertStringContainsString('facets.category[]%253Dabc', $result['generated_urls'][0]);
        $this->assertStringContainsString('facets.discount%253D50%2525%2B', $result['generated_urls'][0]);
    }
}

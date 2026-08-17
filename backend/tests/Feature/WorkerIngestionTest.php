<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Deal;

class WorkerIngestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set the worker API key for testing
        putenv('WORKER_API_KEY=test-secret-key');
        config(['services.worker.api_key' => 'test-secret-key']);
    }

    public function test_worker_ingestion_requires_valid_api_key()
    {
        // 1. Missing API Key
        $response = $this->postJson('/api/worker/ingest', []);
        $response->assertStatus(401);

        // 2. Invalid API Key
        $response = $this->postJson('/api/worker/ingest', [], ['Authorization' => 'Bearer wrong-key']);
        $response->assertStatus(401);

        // 3. Valid API Key (but missing payload -> 422)
        $response = $this->postJson('/api/worker/ingest', [], ['Authorization' => 'Bearer test-secret-key']);
        $response->assertStatus(422);
    }

    public function test_worker_ingestion_payload_validation_and_persistence()
    {
        $payload = [
            'title' => 'Test iPhone 15',
            'original_price' => 79900,
            'discounted_price' => 74900,
            'url' => 'https://amazon.in/dp/B0CHX1W1XY',
            'observation_id' => 'obs_12345',
            'editorial_status' => 'PUBLISHED' // Attempting to hack status
        ];

        // Ensure category and merchant exist or let controller auto-resolve
        
        $response = $this->postJson('/api/worker/ingest', $payload, ['Authorization' => 'Bearer test-secret-key']);
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'deal_id', 'correlation_id']);

        // Assert deal was saved
        $deal = Deal::where('observation_id', 'obs_12345')->first();
        $this->assertNotNull($deal);

        // Assert that the worker could NOT set it to PUBLISHED
        $this->assertEquals('AUTO', $deal->editorial_status);
        $this->assertEquals('raw', $deal->status);
    }

    public function test_worker_ingestion_idempotency()
    {
        $payload = [
            'title' => 'Test MacBook',
            'original_price' => 99000,
            'discounted_price' => 89000,
            'url' => 'https://amazon.in/dp/MACBOOK',
            'observation_id' => 'obs_macbook',
        ];

        // 1. First request
        $response1 = $this->postJson('/api/worker/ingest', $payload, ['Authorization' => 'Bearer test-secret-key']);
        $response1->assertStatus(200);
        $dealId = $response1->json('deal_id');

        // 2. Exact Duplicate request
        $response2 = $this->postJson('/api/worker/ingest', $payload, ['Authorization' => 'Bearer test-secret-key']);
        $response2->assertStatus(200);
        $this->assertEquals('Duplicate observation_id. No changes made.', $response2->json('message'));
        $this->assertEquals($dealId, $response2->json('deal_id'));
        $this->assertNull($response2->json('correlation_id'));

        // Assert only one deal exists
        $this->assertEquals(1, Deal::where('observation_id', 'obs_macbook')->count());
    }
}

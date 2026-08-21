<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->puma = Brand::create(['name' => 'Puma', 'slug' => 'puma', 'is_active' => true]);
        $this->shoes = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true]);
    }

    public function test_hallucination_rejection()
    {
        $deal = Deal::create([
            'title' => "Test Puma Shoes",
            'brand_id' => $this->puma->id,
            'category_id' => $this->shoes->id,
            'original_price' => 1000,
            'discounted_price' => 500,
            'editorial_status' => 'QUALITY_CHECK',
        ]);

        // Simulate Python QA Firewall rejecting the content for hallucination
        // Instead of calling the actual python script, we mock the POST request from Python to Laravel
        $submitPayload = [
            'status' => 'rejected',
            'feedback' => 'Factuality failed: AI mentioned 9000mAh battery but source is 5000mAh'
        ];

        // This assumes we have a feedback/QA API, let's use the generation submit endpoint
        $response = $this->postJson('/api/worker/generations/' . $deal->id, $submitPayload, [
            'Authorization' => 'Bearer local_worker_secret_token_123'
        ]);
        
        $response->assertStatus(200);

        $deal->refresh();
        $this->assertEquals('DRAFT', $deal->editorial_status, "Deal should transition back to DRAFT on QA failure");
        $this->assertStringContainsString('Factuality failed', $deal->ai_caption ?? $deal->confidence_reasons ?? $deal->features); // Assumes we log it somewhere, e.g. features/caption
    }

    public function test_granular_regeneration()
    {
        $deal = Deal::create([
            'title' => "Test Puma Shoes",
            'brand_id' => $this->puma->id,
            'category_id' => $this->shoes->id,
            'original_price' => 1000,
            'discounted_price' => 500,
            'editorial_status' => 'IN_REVIEW',
            'features' => 'Original summary',
            'verdict' => 'Original verdict'
        ]);

        // Trigger regeneration (simulating Admin clicking "Regenerate Verdict")
        // Assumes a route exists or just change the DB state directly for E2E logic validation
        $deal->update([
            'editorial_status' => 'AI_GENERATING',
            'trace_id' => 'target=verdict' // Mocking target parameter
        ]);

        // Python submits new verdict
        $submitPayload = [
            'status' => 'success',
            'editorial_verdict' => 'New regenerated verdict',
            // No summary provided since it's just verdict regeneration
        ];

        $response = $this->postJson('/api/worker/generations/' . $deal->id, $submitPayload, [
            'Authorization' => 'Bearer local_worker_secret_token_123'
        ]);
        
        $response->assertStatus(200);

        $deal->refresh();
        
        // Assert the original summary wasn't overwritten by the regeneration
        $this->assertEquals('Original summary', $deal->features);
        $this->assertEquals('New regenerated verdict', $deal->verdict);
        $this->assertEquals('QUALITY_CHECK', $deal->editorial_status);
    }
}

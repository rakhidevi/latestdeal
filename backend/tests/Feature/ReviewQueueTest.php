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
        $this->merchant = \App\Models\Merchant::create(['id' => 1, 'name' => 'Test', 'domain' => 'test.com', 'store_id' => '1', 'affiliate_param_key' => 'tag']);
    }

    public function test_hallucination_rejection()
    {
        $deal = Deal::create([
            'title' => "Test Puma Shoes",
            'url' => 'https://example.com/puma',
            'image_path' => 'dummy.jpg',
            'brand_id' => $this->puma->id,
            'category_id' => $this->shoes->id,
            'original_price' => 1000,
            'discounted_price' => 500,
            'merchant_id' => 1,
            'editorial_status' => 'AI_GENERATING',
        ]);

        // Simulate Python QA Firewall rejecting the content for hallucination
        // Instead of calling the actual python script, we mock the POST request from Python to Laravel
        $submitPayload = [
            'content' => ['features' => 'some summary'],
            'source_facts' => ['battery' => '5000mAh'],
            'qa_result' => 'FAIL',
            'qa_feedback' => 'Factuality failed: AI mentioned 9000mAh battery but source is 5000mAh',
            'generation_target' => 'all'
        ];

        // This assumes we have a feedback/QA API, let's use the generation submit endpoint
        $response = $this->postJson('/api/worker/generations/' . $deal->id, $submitPayload, [
            'Authorization' => 'Bearer test-secret-key'
        ]);
        
        $response->assertStatus(200);

        $deal->refresh();
        $this->assertEquals('DRAFT', $deal->editorial_status, "Deal should transition back to DRAFT on QA failure");
        // QA feedback is persisted on the generation record (deal_ai_generations), not the deal
        $this->assertDatabaseHas('deal_ai_generations', [
            'deal_id'     => $deal->id,
            'qa_result'   => 'FAIL',
            'qa_feedback' => 'Factuality failed: AI mentioned 9000mAh battery but source is 5000mAh',
        ]);
    }

    public function test_granular_regeneration()
    {
        $deal = Deal::create([
            'title' => "Test Puma Shoes",
            'url' => 'https://example.com/puma2',
            'image_path' => 'dummy.jpg',
            'brand_id' => $this->puma->id,
            'category_id' => $this->shoes->id,
            'original_price' => 1000,
            'discounted_price' => 500,
            'merchant_id' => 1,
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
            'content' => ['verdict' => 'New regenerated verdict'],
            'source_facts' => ['battery' => '5000mAh'],
            'qa_result' => 'PASS',
            'generation_target' => 'verdict'
        ];

        $response = $this->postJson('/api/worker/generations/' . $deal->id, $submitPayload, [
            'Authorization' => 'Bearer test-secret-key'
        ]);
        
        $response->assertStatus(200);

        $deal->refresh();
        
        // Assert the original summary wasn't overwritten by the regeneration
        $this->assertEquals('Original summary', $deal->features);
        $this->assertEquals('New regenerated verdict', $deal->verdict);
        $this->assertEquals('IN_REVIEW', $deal->editorial_status);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Deal;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;

class QualityCheckApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Brand::create(['id' => 1, 'name' => 'Test Brand', 'slug' => 'test-brand', 'is_active' => true]);
        Category::create(['id' => 1, 'name' => 'Test Category', 'slug' => 'test-category', 'is_active' => true]);
        Merchant::create(['id' => 1, 'name' => 'Test Merchant', 'domain' => 'test.com', 'store_id' => 'test', 'affiliate_param_key' => 'tag']);
    }

    protected function createDeal($status = Deal::STATUS_AI_GENERATING)
    {
        return Deal::create([
            'title' => 'Test Deal',
            'url' => 'https://example.com/test',
            'image_path' => 'dummy.jpg',
            'brand_id' => 1,
            'original_price' => 1000,
            'discounted_price' => 500,
            'observation_id' => 'obs_' . uniqid(),
            'editorial_status' => $status,
            'category_id' => 1,
            'merchant_id' => 1,
        ]);
    }

    public function test_worker_can_claim_quality_check()
    {
        $this->withoutMiddleware(\App\Http\Middleware\WorkerAuthMiddleware::class);
        $deal = $this->createDeal(Deal::STATUS_AI_GENERATING);

        $response = $this->getJson('/api/worker/generations/claim');
        
        $response->assertStatus(200);
        $response->assertJsonPath('deal.id', $deal->id);
    }

    public function test_worker_can_submit_pass_result()
    {
        $this->withoutMiddleware(\App\Http\Middleware\WorkerAuthMiddleware::class);
        $deal = $this->createDeal(Deal::STATUS_AI_GENERATING);

        $response = $this->postJson("/api/worker/generations/{$deal->id}", [
            'content' => ['editorial_summary' => 'Looks good'],
            'source_facts' => ['price' => 500],
            'qa_result' => 'PASS',
            'qa_feedback' => 'OK',
            'generation_target' => 'all'
        ]);
        
        $response->assertStatus(200);
        
        // Assert state changed to IN_REVIEW
        $this->assertEquals(Deal::STATUS_IN_REVIEW, $deal->fresh()->editorial_status);
        
        $this->assertDatabaseHas('deal_ai_generations', [
            'deal_id' => $deal->id,
            'qa_result' => 'PASS',
        ]);
    }
    
    public function test_worker_can_submit_fail_result()
    {
        $this->withoutMiddleware(\App\Http\Middleware\WorkerAuthMiddleware::class);
        $deal = $this->createDeal(Deal::STATUS_AI_GENERATING);

        $response = $this->postJson("/api/worker/generations/{$deal->id}", [
            'content' => ['editorial_summary' => 'Bad'],
            'source_facts' => ['price' => 500],
            'qa_result' => 'FAIL',
            'qa_feedback' => 'Too similar to original',
            'generation_target' => 'all'
        ]);
        
        $response->assertStatus(200);
        
        // Assert state changed back to DRAFT
        $this->assertEquals(Deal::STATUS_DRAFT, $deal->fresh()->editorial_status);
        
        $this->assertDatabaseHas('deal_ai_generations', [
            'deal_id' => $deal->id,
            'qa_result' => 'FAIL',
        ]);
    }
}

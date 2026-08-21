<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QualityCheckApiTest extends TestCase
{
    use RefreshDatabase;

    protected $workerToken = 'test-worker-token-123'; // matches QualityEvaluator

    public function test_worker_can_claim_quality_check()
    {
        // Add a mock for WorkerAuthMiddleware since we don't have the exact key, or bypass it if it's disabled in test.
        // For demonstration, assuming middleware allows this request in testing or we use WithoutMiddleware
        $this->withoutMiddleware(\App\Http\Middleware\WorkerAuthMiddleware::class);

        $deal = Deal::factory()->create(['editorial_status' => Deal::STATUS_QUALITY_CHECK]);

        $response = $this->getJson('/api/worker/quality-checks/claim');
        
        $response->assertStatus(200);
        $response->assertJsonPath('deal.id', $deal->id);
    }

    public function test_worker_can_submit_pass_result()
    {
        $this->withoutMiddleware(\App\Http\Middleware\WorkerAuthMiddleware::class);

        $deal = Deal::factory()->create(['editorial_status' => Deal::STATUS_QUALITY_CHECK]);

        $response = $this->postJson("/api/worker/quality-checks/{$deal->id}", [
            'result' => 'PASS',
            'feedback' => 'Looks good',
            'similarity_score' => 0.4
        ]);
        
        $response->assertStatus(200);
        
        // Assert state changed to IN_REVIEW
        $this->assertEquals(Deal::STATUS_IN_REVIEW, $deal->fresh()->editorial_status);
        
        // Assert DealQualityCheck was created
        $this->assertDatabaseHas('deal_quality_checks', [
            'deal_id' => $deal->id,
            'result' => 'PASS',
        ]);
    }
    
    public function test_worker_can_submit_fail_result()
    {
        $this->withoutMiddleware(\App\Http\Middleware\WorkerAuthMiddleware::class);

        $deal = Deal::factory()->create(['editorial_status' => Deal::STATUS_QUALITY_CHECK]);

        $response = $this->postJson("/api/worker/quality-checks/{$deal->id}", [
            'result' => 'FAIL',
            'feedback' => 'Too similar to original',
            'similarity_score' => 0.9
        ]);
        
        $response->assertStatus(200);
        
        // Assert state changed back to DRAFT
        $this->assertEquals(Deal::STATUS_DRAFT, $deal->fresh()->editorial_status);
        
        // Assert DealQualityCheck was created
        $this->assertDatabaseHas('deal_quality_checks', [
            'deal_id' => $deal->id,
            'result' => 'FAIL',
        ]);
    }
}

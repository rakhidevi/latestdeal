<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DealStateTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_state_transitions()
    {
        $deal = Deal::factory()->create(['editorial_status' => Deal::STATUS_DRAFT]);
        
        // Draft -> Quality Check
        $deal->editorial_status = Deal::STATUS_QUALITY_CHECK;
        $deal->save();
        $this->assertEquals(Deal::STATUS_QUALITY_CHECK, $deal->fresh()->editorial_status);
        
        // Quality Check -> In Review
        $deal->editorial_status = Deal::STATUS_IN_REVIEW;
        $deal->save();
        $this->assertEquals(Deal::STATUS_IN_REVIEW, $deal->fresh()->editorial_status);
        
        // In Review -> Published
        $deal->editorial_status = Deal::STATUS_PUBLISHED;
        $deal->save();
        $this->assertEquals(Deal::STATUS_PUBLISHED, $deal->fresh()->editorial_status);
    }

    public function test_invalid_state_transition_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid editorial status transition from DRAFT to PUBLISHED");

        $deal = Deal::factory()->create(['editorial_status' => Deal::STATUS_DRAFT]);
        
        // Direct jump from Draft -> Published is illegal
        $deal->editorial_status = Deal::STATUS_PUBLISHED;
        $deal->save();
    }
}

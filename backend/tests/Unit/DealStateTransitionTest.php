<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DealStateTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );
        $this->actingAs($user);
        \App\Models\Category::create(['id' => 1, 'name' => 'Test Category', 'slug' => 'test-category']);
        \App\Models\Merchant::create(['id' => 1, 'name' => 'Test Merchant', 'domain' => 'test-merchant.com', 'store_id' => '1', 'affiliate_param_key' => 'tag']);
        \App\Models\Brand::create(['id' => 1, 'name' => 'Test Brand', 'slug' => 'test-brand']);
    }

    public function test_valid_state_transitions()
    {
        $deal = Deal::create([
            'title' => 'Test Deal',
            'url' => 'https://example.com',
            'editorial_status' => Deal::STATUS_DRAFT,
            'category_id' => 1,
            'merchant_id' => 1,
            'brand_id' => 1,
            'original_price' => 100,
            'discounted_price' => 50,
            'image_path' => 'dummy.jpg',
            'hash_id' => \Illuminate\Support\Str::random(6)
        ]);
        
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

        $deal = Deal::create([
            'title' => 'Test Deal',
            'url' => 'https://example.com',
            'editorial_status' => Deal::STATUS_DRAFT,
            'category_id' => 1,
            'merchant_id' => 1,
            'brand_id' => 1,
            'original_price' => 100,
            'discounted_price' => 50,
            'image_path' => 'dummy.jpg',
            'hash_id' => \Illuminate\Support\Str::random(6)
        ]);
        
        // Direct jump from Draft -> Published is illegal
        $deal->editorial_status = Deal::STATUS_PUBLISHED;
        $deal->save();
    }
}

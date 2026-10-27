<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Deal;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DealsAndAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::firstOrCreate(
            ['id' => 1],
            ['name' => 'Editorial Team', 'email' => 'editorial@latestdeal.in', 'password' => bcrypt('secret')]
        );

        $merchant = Merchant::firstOrCreate(
            ['name' => 'Amazon India'],
            ['domain' => 'amazon.in', 'store_id' => 'amazon', 'affiliate_param_key' => 'tag', 'status' => 'active']
        );

        $category = Category::firstOrCreate(
            ['slug' => 'electronics'],
            ['name' => 'Electronics']
        );

        $brand = Brand::firstOrCreate(
            ['slug' => 'asus'],
            ['name' => 'Asus', 'is_active' => true]
        );

        Deal::firstOrCreate(
            ['asin' => 'B0TESTASIN1'],
            [
                'title' => 'Test Gaming Laptop 16GB RAM',
                'slug' => 'test-gaming-laptop-16gb-ram',
                'original_price' => 79990,
                'discounted_price' => 44990,
                'calculated_discount_percent' => 44,
                'status' => 'active',
                'merchant_id' => $merchant->id,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'url' => 'https://www.amazon.in/dp/B0TESTASIN1',
                'trace_id' => 'trace_test_1',
                'pipeline_run_id' => 'pipeline_test_1',
                'observation_id' => 'obs_test_1',
                'image_path' => 'deals/default.png',
            ]
        );
    }

    public function test_deals_all_route_returns_200()
    {
        $response = $this->get('/deals/all');
        $response->assertStatus(200);
        $response->assertSee('All Deals');
    }

    public function test_deals_discount_range_route_returns_200()
    {
        $response = $this->get('/deals/40-to-50-percent');
        $response->assertStatus(200);
        $response->assertSee('40% - 50% Off Deals');
    }

    public function test_deals_base_route_returns_200()
    {
        $response = $this->get('/deals');
        $response->assertStatus(200);
    }

    public function test_predict_price_api_returns_200_with_valid_prediction()
    {
        $deal = Deal::where('asin', 'B0TESTASIN1')->first();

        $response = $this->postJson('/api/predict-price', [
            'deal_id' => $deal->id
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'prediction',
                'confidence_score',
                'buy_now'
            ]
        ]);
        $response->assertJson([
            'status' => 'success'
        ]);
    }

    public function test_assistant_chat_api_returns_200_with_reply()
    {
        $deal = Deal::where('asin', 'B0TESTASIN1')->first();

        $response = $this->postJson('/api/assistant/chat', [
            'message' => 'Best laptop deals',
            'deal_ids' => [$deal->id]
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'reply',
            'source'
        ]);
    }

    public function test_live_compare_api_returns_200_with_competitor_stores()
    {
        $deal = Deal::where('asin', 'B0TESTASIN1')->first();

        $response = $this->postJson('/api/compare-prices', [
            'deal_id' => $deal->id,
            'title' => $deal->title
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'cache_hit'
        ]);
        $response->assertJsonStructure([
            'data' => [
                'results' => [
                    '*' => ['store', 'price', 'delivery', 'rating', 'url']
                ],
                'ai_score'
            ]
        ]);
    }

    public function test_storage_deals_default_png_fallback_returns_image()
    {
        $response = $this->get('/storage/deals/default_png');
        $response->assertStatus(200);
    }
}

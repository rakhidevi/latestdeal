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
            'image_path' => 'deals/default.png',
                'category_id' => $category->id,
            'image_path' => 'deals/default.png',
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
            'status' => 'verified_primary_store'
        ]);
        $response->assertJsonStructure([
            'data' => [
                'results' => [
                    '*' => ['store', 'delivery', 'rating']
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

    public function test_smart_search_excludes_accessories_for_smartphone_query()
    {
        $merchant = Merchant::first();
        $category = Category::first();

        // 1. Legitimate realme smartphone under 30000
        Deal::create([
            'asin' => 'B0PHONE123',
            'title' => 'realme Narzo 70 Pro 5G (Glass Green, 8GB RAM, 128GB Storage)',
            'slug' => 'realme-narzo-70-pro-5g',
            'original_price' => 24999,
            'discounted_price' => 18999,
            'status' => 'active',
            'merchant_id' => $merchant->id,
            'category_id' => $category->id,
            'image_path' => 'deals/default.png',
            'url' => 'https://www.amazon.in/dp/B0PHONE123',
        ]);

        // 2. Misleading phone charger mentioning smartphone in title
        Deal::create([
            'asin' => 'B0CHARGER1',
            'title' => '65W Super Fast Charger for Smartphone and Tablets',
            'slug' => '65w-fast-charger-smartphone',
            'original_price' => 1999,
            'discounted_price' => 499,
            'status' => 'active',
            'merchant_id' => $merchant->id,
            'category_id' => $category->id,
            'image_path' => 'deals/default.png',
            'url' => 'https://www.amazon.in/dp/B0CHARGER1',
        ]);

        // 3. Misleading phone case
        Deal::create([
            'asin' => 'B0CASE1234',
            'title' => 'Shockproof Slim Silicone Case for 5G Smartphone',
            'slug' => 'shockproof-slim-case-smartphone',
            'original_price' => 999,
            'discounted_price' => 199,
            'status' => 'active',
            'merchant_id' => $merchant->id,
            'category_id' => $category->id,
            'image_path' => 'deals/default.png',
            'url' => 'https://www.amazon.in/dp/B0CASE1234',
        ]);

        // 4. Headphones mentioning 'headphones' which contain the word 'phone'
        Deal::create([
            'asin' => 'B0SONYXM5T',
            'title' => 'Sony WH-1000XM5 Wireless Noise Cancelling Headphones',
            'slug' => 'sony-wh-1000xm5-test',
            'original_price' => 34990,
            'discounted_price' => 26990,
            'status' => 'active',
            'merchant_id' => $merchant->id,
            'category_id' => $category->id,
            'image_path' => 'deals/default.png',
            'url' => 'https://www.amazon.in/dp/B0SONYXM5T',
        ]);

        $response = $this->getJson('/api/smart-search?q=' . urlencode('Best smartphone under ₹30000'));
        $response->assertStatus(200);

        $data = $response->json();
        $titles = collect($data['deals'])->pluck('title')->toArray();

        // Must include the actual smartphone
        $this->assertTrue(collect($titles)->contains(fn($t) => str_contains($t, 'realme Narzo 70 Pro')));

        // Must strictly exclude the charger, case, and headphones
        $this->assertFalse(collect($titles)->contains(fn($t) => str_contains($t, '65W Super Fast Charger')));
        $this->assertFalse(collect($titles)->contains(fn($t) => str_contains($t, 'Shockproof Slim Silicone Case')));
        $this->assertFalse(collect($titles)->contains(fn($t) => str_contains($t, 'Sony WH-1000XM5')));

        // AI Best Pick should be the phone, NOT the charger or headphones
        $this->assertEquals('realme Narzo 70 Pro 5G (Glass Green, 8GB RAM, 128GB Storage)', $data['deals'][0]['title']);
    }

    public function test_smart_search_excludes_cleaning_kit_for_airpods_query()
    {
        $merchant = Merchant::first();
        $category = Category::first();

        // 1. Genuine Apple AirPods
        Deal::create([
            'asin' => 'B0AIRPODS2',
            'title' => 'Apple AirPods Pro (2nd Gen) with MagSafe Case USB-C',
            'slug' => 'apple-airpods-pro-2nd-gen',
            'original_price' => 24900,
            'discounted_price' => 18999,
            'status' => 'active',
            'merchant_id' => $merchant->id,
            'category_id' => $category->id,
            'image_path' => 'deals/default.png',
            'url' => 'https://www.amazon.in/dp/B0AIRPODS2',
        ]);

        // 2. Cheap AirPods cleaning kit
        Deal::create([
            'asin' => 'B0CLEANKIT',
            'title' => 'AirPods Cleaning Kit Multi-Function Cleaner Pen for Earbuds',
            'slug' => 'airpods-cleaning-kit-pen',
            'original_price' => 499,
            'discounted_price' => 149,
            'status' => 'active',
            'merchant_id' => $merchant->id,
            'category_id' => $category->id,
            'image_path' => 'deals/default.png',
            'url' => 'https://www.amazon.in/dp/B0CLEANKIT',
        ]);

        $response = $this->getJson('/api/smart-search?q=' . urlencode('Cheapest AirPods today'));
        $response->assertStatus(200);

        $data = $response->json();
        $titles = collect($data['deals'])->pluck('title')->toArray();

        // Must include genuine AirPods
        $this->assertTrue(collect($titles)->contains(fn($t) => str_contains($t, 'Apple AirPods Pro')));

        // Must strictly exclude the cleaning kit
        $this->assertFalse(collect($titles)->contains(fn($t) => str_contains($t, 'Cleaning Kit')));
    }

    public function test_smart_search_enforces_hard_budget_constraint()
    {
        $merchant = Merchant::first();
        $category = Category::first();

        // Within budget
        Deal::create([
            'asin' => 'B0PHONE_UNDER',
            'title' => 'OnePlus Nord CE 4 5G (8GB RAM, 128GB)',
            'slug' => 'oneplus-nord-ce-4-5g',
            'original_price' => 26999,
            'discounted_price' => 24999,
            'status' => 'active',
            'merchant_id' => $merchant->id,
            'category_id' => $category->id,
            'image_path' => 'deals/default.png',
            'url' => 'https://www.amazon.in/dp/B0PHONE_UNDER',
        ]);

        // Exceeds budget
        Deal::create([
            'asin' => 'B0PHONE_OVER',
            'title' => 'OnePlus 12R 5G (16GB RAM, 256GB)',
            'slug' => 'oneplus-12r-5g',
            'original_price' => 45999,
            'discounted_price' => 39999,
            'status' => 'active',
            'merchant_id' => $merchant->id,
            'category_id' => $category->id,
            'image_path' => 'deals/default.png',
            'url' => 'https://www.amazon.in/dp/B0PHONE_OVER',
        ]);

        $response = $this->getJson('/api/smart-search?q=' . urlencode('Best smartphone under ₹30000'));
        $response->assertStatus(200);

        $titles = collect($response->json()['deals'])->pluck('title')->toArray();
        $this->assertTrue(collect($titles)->contains(fn($t) => str_contains($t, 'OnePlus Nord CE 4')));
        $this->assertFalse(collect($titles)->contains(fn($t) => str_contains($t, 'OnePlus 12R')));
    }

    public function test_assistant_chat_accepts_conversation_history()
    {
        $deal = Deal::where('asin', 'B0TESTASIN1')->first();

        $response = $this->postJson('/api/assistant/chat', [
            'message' => 'Show me laptops with 16GB RAM',
            'deal_ids' => [$deal->id],
            'history' => [
                ['role' => 'user', 'content' => 'I need a good laptop for work'],
                ['role' => 'assistant', 'content' => 'Here are recommended options.']
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['reply', 'source']);
    }

    public function test_assistant_chat_handles_zero_matching_deals_gracefully()
    {
        $response = $this->postJson('/api/assistant/chat', [
            'message' => 'Nonexistent product XYZ under 50',
            'deal_ids' => []
        ]);

        $response->assertStatus(200);
        $reply = $response->json()['reply'];
        $lowReply = strtolower($reply);
        $this->assertTrue(
            str_contains($lowReply, "couldn't") || 
            str_contains($lowReply, 'no ') || 
            str_contains($lowReply, 'not find') || 
            str_contains($lowReply, 'sorry') ||
            str_contains($lowReply, 'unfortunately')
        );
    }
}

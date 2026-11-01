<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Deal;
use App\Models\Category;
use App\Models\Merchant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryPrecedenceAndOffersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('WORKER_API_KEY=test-secret-key');
        config(['services.worker.api_key' => 'test-secret-key']);

        // Create standard categories
        Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics']);
        Category::firstOrCreate(['slug' => 'mobiles'], ['name' => 'Mobiles']);
        Category::firstOrCreate(['slug' => 'fashion'], ['name' => 'Fashion']);
        Category::firstOrCreate(['slug' => 'home-kitchen'], ['name' => 'Home & Kitchen']);

        // Create Amazon merchant with store_id & affiliate_param_key
        Merchant::firstOrCreate(
            ['domain' => 'amazon.in'],
            [
                'name' => 'Amazon India',
                'slug' => 'amazon-india',
                'store_id' => '1',
                'affiliate_param_key' => 'tag'
            ]
        );
    }

    public function test_category_precedence_canonical_match(): void
    {
        $payload = [
            'title' => 'Sample Item ABC',
            'original_price' => 2000,
            'discounted_price' => 1500,
            'url' => 'https://amazon.in/dp/B0CANONICAL1',
            'asin' => 'B0CANONICAL1',
            'trace_id' => 'trace_c1',
            'pipeline_run_id' => 'run_c1',
            'category_name' => 'Fashion',
            'category_id' => 1, // Simulates worker sending 1 as default
            'observation_id' => 'obs-canonical-1',
        ];

        $response = $this->postJson('/api/worker/ingest', $payload, [
            'Authorization' => 'Bearer test-secret-key'
        ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200);
        $deal = Deal::where('asin', 'B0CANONICAL1')->first();
        $this->assertNotNull($deal);
        $this->assertEquals('Fashion', $deal->category->name);
    }

    public function test_category_precedence_title_classifier_resolves_mobiles(): void
    {
        $payload = [
            'title' => 'Samsung Galaxy M15 5G (4GB RAM, 128GB Storage)',
            'original_price' => 15999,
            'discounted_price' => 12999,
            'url' => 'https://amazon.in/dp/B0PHONE1234',
            'asin' => 'B0PHONE1234',
            'trace_id' => 'trace_p1',
            'pipeline_run_id' => 'run_p1',
            'category_id' => 1, // Worker default 1
            'observation_id' => 'obs-mobile-1',
        ];

        $response = $this->postJson('/api/worker/ingest', $payload, [
            'Authorization' => 'Bearer test-secret-key'
        ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200);
        $deal = Deal::where('asin', 'B0PHONE1234')->first();
        $this->assertNotNull($deal);
        $this->assertEquals('Mobiles', $deal->category->name);
    }

    public function test_category_precedence_title_classifier_resolves_home_and_kitchen(): void
    {
        $payload = [
            'title' => 'Halonix 2-in-1 LED Bulb Cool White (Pack of 2)',
            'original_price' => 499,
            'discounted_price' => 199,
            'url' => 'https://amazon.in/dp/B0BULB12345',
            'asin' => 'B0BULB12345',
            'trace_id' => 'trace_b1',
            'pipeline_run_id' => 'run_b1',
            'observation_id' => 'obs-bulb-1',
        ];

        $response = $this->postJson('/api/worker/ingest', $payload, [
            'Authorization' => 'Bearer test-secret-key'
        ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200);
        $deal = Deal::where('asin', 'B0BULB12345')->first();
        $this->assertNotNull($deal);
        $this->assertEquals('Home & Kitchen', $deal->category->name);
    }

    public function test_category_precedence_fallback_uncategorized(): void
    {
        $payload = [
            'title' => 'Xyzzy Random Mystery Widget QQQ 99',
            'original_price' => 500,
            'discounted_price' => 250,
            'url' => 'https://amazon.in/dp/B0MYSTERY99',
            'asin' => 'B0MYSTERY99',
            'trace_id' => 'trace_m1',
            'pipeline_run_id' => 'run_m1',
            'observation_id' => 'obs-mystery-1',
        ];

        $response = $this->postJson('/api/worker/ingest', $payload, [
            'Authorization' => 'Bearer test-secret-key'
        ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200);
        $deal = Deal::where('asin', 'B0MYSTERY99')->first();
        $this->assertNotNull($deal);
        $this->assertEquals('Uncategorized', $deal->category->name);
        $this->assertNotEquals(1, $deal->category_id);
    }

    public function test_structured_offers_and_coupon_saved(): void
    {
        $structuredOffers = [
            [
                'offer_type' => 'coupon_clip',
                'discount_type' => 'fixed_amount',
                'discount_value' => 200.0,
                'min_spend' => 1000.0,
                'clip_required' => true,
                'verified_effective_price' => 1299.0
            ]
        ];

        $payload = [
            'title' => 'Noise Pulse 2 Max Smart Watch',
            'original_price' => 5999,
            'discounted_price' => 1499,
            'url' => 'https://amazon.in/dp/B0NOISEWATCH',
            'asin' => 'B0NOISEWATCH',
            'trace_id' => 'trace_w1',
            'pipeline_run_id' => 'run_w1',
            'promo_code' => 'NOISE200',
            'bank_offer' => 'Instant ₹200 off on HDFC Cards',
            'structured_offers' => $structuredOffers,
            'observation_id' => 'obs-noise-offer',
        ];

        $response = $this->postJson('/api/worker/ingest', $payload, [
            'Authorization' => 'Bearer test-secret-key'
        ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200);
        $deal = Deal::where('asin', 'B0NOISEWATCH')->first();
        $this->assertNotNull($deal);
        $this->assertEquals('NOISE200', $deal->coupon_code);
        $this->assertEquals('NOISE200', $deal->promo_code);
        $this->assertEquals('Instant ₹200 off on HDFC Cards', $deal->bank_offer);
        $this->assertIsArray($deal->structured_offers);
        $this->assertEquals('coupon_clip', $deal->structured_offers[0]['offer_type']);
        $this->assertEquals(200.0, $deal->structured_offers[0]['discount_value']);
    }

    public function test_idempotency_key_deduplication(): void
    {
        $idempotencyKey = 'test_idempotency_token_1234567890';

        $payload = [
            'title' => 'Anker Soundcore Earbuds',
            'original_price' => 3999,
            'discounted_price' => 1999,
            'url' => 'https://amazon.in/dp/B0ANKER123',
            'asin' => 'B0ANKER123',
            'trace_id' => 'trace_a1',
            'pipeline_run_id' => 'run_a1',
            'idempotency_key' => $idempotencyKey,
            'observation_id' => 'obs-anker-1',
        ];

        // First ingestion
        $response1 = $this->postJson('/api/worker/ingest', $payload, [
            'Authorization' => 'Bearer test-secret-key'
        ]);
        if ($response1->status() !== 200) {
            dump($response1->json());
        }
        $response1->assertStatus(200);
        $this->assertFalse($response1->json('idempotent', false));

        // Second ingestion with identical idempotency_key
        $response2 = $this->postJson('/api/worker/ingest', $payload, [
            'Authorization' => 'Bearer test-secret-key'
        ]);
        if ($response2->status() !== 200) {
            dump($response2->json());
        }
        $response2->assertStatus(200);
        $this->assertTrue($response2->json('idempotent'));

        // Verify only 1 deal exists in database
        $count = Deal::where('asin', 'B0ANKER123')->count();
        $this->assertEquals(1, $count);
    }

    public function test_reingestion_updates_existing_deal_coupon_and_bank_offer(): void
    {
        $initialPayload = [
            'title' => 'Logitech MX Master 3S Wireless Mouse',
            'original_price' => 9995,
            'discounted_price' => 7995,
            'url' => 'https://amazon.in/dp/B0MXMASTER3S',
            'asin' => 'B0MXMASTER3S',
            'trace_id' => 'trace_mx_1',
            'pipeline_run_id' => 'run_mx_1',
            'observation_id' => 'obs-mx-1',
        ];

        // 1. Initial Ingestion without coupon or bank offer
        $res1 = $this->postJson('/api/worker/ingest', $initialPayload, [
            'Authorization' => 'Bearer test-secret-key'
        ]);
        $res1->assertStatus(200);

        $deal = Deal::where('asin', 'B0MXMASTER3S')->first();
        $this->assertNotNull($deal);
        $this->assertNull($deal->coupon_code);
        $this->assertNull($deal->bank_offer);

        // 2. Re-ingestion of existing deal with newly discovered coupon & bank offer
        $reingestPayload = [
            'title' => 'Logitech MX Master 3S Wireless Mouse',
            'original_price' => 9995,
            'discounted_price' => 7995, // Same price, but offers updated!
            'url' => 'https://amazon.in/dp/B0MXMASTER3S',
            'asin' => 'B0MXMASTER3S',
            'trace_id' => 'trace_mx_2',
            'pipeline_run_id' => 'run_mx_2',
            'observation_id' => 'obs-mx-2',
            'coupon_code' => 'LOGI500',
            'bank_offer' => 'Instant ₹500 off on ICICI Cards',
            'structured_offers' => [
                [
                    'offer_type' => 'coupon_clip',
                    'discount_type' => 'fixed',
                    'discount_value' => 500.0,
                    'coupon_code' => 'LOGI500',
                ]
            ],
            'offer_fingerprint' => 'fp_logi_mx_500'
        ];

        $res2 = $this->postJson('/api/worker/ingest', $reingestPayload, [
            'Authorization' => 'Bearer test-secret-key'
        ]);
        $res2->assertStatus(200);
        $this->assertEquals('updated', $res2->json('status'));

        // 3. Verify deal in database has refreshed offer data
        $deal->refresh();
        $this->assertEquals('LOGI500', $deal->coupon_code);
        $this->assertEquals('LOGI500', $deal->promo_code);
        $this->assertEquals('Instant ₹500 off on ICICI Cards', $deal->bank_offer);
        $this->assertIsArray($deal->structured_offers);
        $this->assertEquals('coupon_clip', $deal->structured_offers[0]['offer_type']);
        $this->assertEquals('fp_logi_mx_500', $deal->offer_fingerprint);
    }
}

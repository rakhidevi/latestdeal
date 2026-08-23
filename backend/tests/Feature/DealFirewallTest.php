<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Deal;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\User;

class DealFirewallTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Category::create(['id' => 1, 'name' => 'Test', 'slug' => 'test-cat']);
        Merchant::create(['id' => 1, 'name' => 'Test', 'domain' => 'test.com', 'store_id' => '1', 'affiliate_param_key' => 'tag']);
        
        // Authenticate as admin so PUBLISHED status is not downgraded by Deal model safeguard
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@fw.com', 'password' => bcrypt('p'), 'role' => 'admin']);
        $this->actingAs($this->admin);
    }

    private function createRawDeal($attributes = [])
    {
        return Deal::create(array_merge([
            'title' => 'Test Deal',
            'url' => 'https://example.com/test-' . uniqid(),
            'original_price' => 100,
            'discounted_price' => 50,
            'observation_id' => 'obs_' . uniqid(),
            'editorial_status' => Deal::STATUS_DRAFT,
            'image_path' => 'dummy.jpg',
            'status' => 'active',
            'editorial_summary' => null,
            'editorial_verdict' => null,
            'pros' => null,
            'cons' => null,
            'editor_id' => null,
            'reviewed_at' => null,
            'category_id' => 1,
            'merchant_id' => 1,
        ], $attributes));
    }

    private function createPublishableDeal($attributes = [])
    {
        $uniqueId = uniqid();
        $defaults = [
            'title' => 'Publishable Test Deal',
            'slug'  => 'publishable-test-deal-' . $uniqueId, // explicit slug so route resolves
            'url'   => 'https://example.com/pub-test-' . $uniqueId,
            'original_price' => 100,
            'discounted_price' => 50,
            'observation_id' => 'obs_pub_' . $uniqueId,
            'editorial_status' => Deal::STATUS_PUBLISHED,
            'image_path' => 'dummy.jpg',
            'status' => 'active',
            'editorial_summary' => 'This is a genuine review.',
            'editorial_verdict' => 'Highly recommended.',
            'pros' => ['Good battery'],
            'cons' => ['High price'],
            'best_for' => 'Daily use',
            'not_for' => 'Heavy duty',
            'editor_id' => 1,
            'reviewed_at' => now(),
            'category_id' => 1,
            'merchant_id' => 1,
        ];
        $merged = array_merge($defaults, $attributes);
        // withoutEvents bypasses the publication safeguard in creating/saving boot hooks
        return Deal::withoutEvents(fn () => Deal::create($merged));
    }

    public function test_raw_deals_return_404_on_direct_access()
    {
        $rawDeal = $this->createRawDeal();
        $response = $this->get(route('deals.show', $rawDeal->slug));
        $response->assertStatus(404);
    }

    public function test_publishable_deals_return_200()
    {
        $deal = $this->createPublishableDeal();
        $response = $this->get(route('deals.show', $deal->slug));
        $response->assertStatus(200);
    }

    public function test_expired_publishable_deal_without_historical_value_returns_410()
    {
        $deal = $this->createPublishableDeal([
            'status' => Deal::STATUS_EXPIRED,
            'editorial_summary' => 'Short summary.',
            'is_editor_pick' => false
        ]);

        $response = $this->get(route('deals.show', $deal->slug));
        $response->assertStatus(410);
    }

    public function test_expired_publishable_deal_with_historical_value_returns_200()
    {
        $deal = $this->createPublishableDeal([
            'status' => Deal::STATUS_EXPIRED,
            'is_editor_pick' => true // gives it historical value
        ]);

        $response = $this->get(route('deals.show', $deal->slug));
        $response->assertStatus(200);
    }

    public function test_raw_deals_are_excluded_from_sitemap()
    {
        $rawDeal = $this->createRawDeal();
        $publishableDeal = $this->createPublishableDeal();

        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString($publishableDeal->slug, $content);
        $this->assertStringNotContainsString($rawDeal->slug, $content);
    }

    public function test_unauthenticated_scraper_cannot_publish_deal()
    {
        // Explicitly log out to simulate an unauthenticated scraper
        auth()->logout();

        $deal = new Deal([
            'title' => 'Unauth Deal',
            'url' => 'https://example.com/unauth-test',
            'image_path' => 'dummy.jpg',
            'original_price' => 100,
            'discounted_price' => 50,
            'observation_id' => 'obs_unauth',
            'editorial_status' => Deal::STATUS_PUBLISHED,
            'category_id' => 1,
            'merchant_id' => 1,
        ]);

        // Simulated scraper saving without auth
        $deal->save();

        // Must downgrade to DISCOVERED (the safeguard in Deal model)
        $this->assertEquals(Deal::STATUS_DISCOVERED, $deal->fresh()->editorial_status);
    }

    public function test_authenticated_editor_can_publish_deal()
    {
        $editor = User::create(['name' => 'Editor', 'email' => 'editor@test.com', 'password' => bcrypt('password'), 'role' => 'admin']);
        $this->actingAs($editor);

        $deal = new Deal([
            'title' => 'Auth Deal',
            'url' => 'https://example.com/auth-test',
            'image_path' => 'dummy.jpg',
            'original_price' => 100,
            'discounted_price' => 50,
            'observation_id' => 'obs_auth',
            'editorial_status' => Deal::STATUS_PUBLISHED,
            'category_id' => 1,
            'merchant_id' => 1,
        ]);

        $deal->save();

        $this->assertEquals(Deal::STATUS_PUBLISHED, $deal->fresh()->editorial_status);
    }

    public function test_canonical_urls_ignore_query_parameters()
    {
        $deal = $this->createPublishableDeal();
        
        $urlsToTest = [
            route('deals.show', $deal->slug),
            route('deals.show', $deal->slug) . '?utm_source=amazon',
            route('deals.show', $deal->slug) . '?ref=abc',
            route('deals.show', $deal->slug) . '?utm_source=x&utm_campaign=y',
        ];

        $expectedCanonical = route('deals.show', $deal->slug);

        foreach ($urlsToTest as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
            
            // The response content should contain the canonical link tag pointing to the clean URL
            $response->assertSee('<link rel="canonical" href="' . $expectedCanonical . '">', false);
        }
    }
}

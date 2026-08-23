<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Deal;
use App\Models\DealAiGeneration;
use App\Models\Merchant;
use App\Services\AI\AIRouter;
use App\Services\Editorial\EditorialGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class EditorialGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Brand::create(['id' => 1, 'name' => 'Puma', 'slug' => 'puma', 'is_active' => true]);
        Category::create(['id' => 1, 'name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true]);
        Merchant::create(['id' => 1, 'name' => 'Amazon', 'domain' => 'amazon.in', 'store_id' => 'amzn', 'affiliate_param_key' => 'tag']);
    }

    protected function createDraftDeal(array $overrides = []): Deal
    {
        return Deal::withoutEvents(function () use ($overrides) {
            return Deal::create(array_merge([
                'title' => 'Puma Mens Running Shoes',
                'brand' => 'Puma',
                'brand_id' => 1,
                'category_id' => 1,
                'merchant_id' => 1,
                'url' => 'https://amazon.in/dp/TESTPUMA' . uniqid(),
                'image_path' => 'dummy.jpg',
                'original_price' => 5000,
                'discounted_price' => 2500,
                'calculated_discount_percent' => 50.0,
                'observation_id' => 'obs_' . uniqid(),
                'editorial_status' => 'DRAFT',
            ], $overrides));
        });
    }

    /**
     * Test Deterministic QA: Valid price and discount claims pass.
     */
    public function test_deterministic_qa_passes_valid_claims()
    {
        $generator = app(EditorialGenerator::class);
        $facts = [
            'original_price' => 5000.0,
            'discounted_price' => 2500.0,
            'amount_saved' => 2500.0,
            'discount_percentage' => 50.0,
        ];

        $parsed = [
            'editorial_summary' => 'Puma running shoes available at ₹2500, saving ₹2500 from original ₹5000.',
            'editorial_verdict' => 'A great deal at 50% off.',
            'pros' => ['50% discount'],
            'cons' => [],
            'best_for' => [],
            'not_for' => []
        ];

        $reflection = new \ReflectionClass(EditorialGenerator::class);
        $method = $reflection->getMethod('deterministicQa');
        $method->setAccessible(true);

        $notes = $method->invoke($generator, $parsed, $facts);
        $this->assertEmpty($notes, "Deterministic QA should pass when prices and discounts match source facts.");
    }

    /**
     * Test Deterministic QA: Invented price fails.
     */
    public function test_deterministic_qa_fails_invented_price()
    {
        $generator = app(EditorialGenerator::class);
        $facts = [
            'original_price' => 5000.0,
            'discounted_price' => 2500.0,
            'amount_saved' => 2500.0,
            'discount_percentage' => 50.0,
        ];

        $parsed = [
            'editorial_summary' => 'Puma running shoes usually cost ₹9999 but now at ₹2500.',
            'editorial_verdict' => 'Save big.',
            'pros' => [],
            'cons' => [],
            'best_for' => [],
            'not_for' => []
        ];

        $reflection = new \ReflectionClass(EditorialGenerator::class);
        $method = $reflection->getMethod('deterministicQa');
        $method->setAccessible(true);

        $notes = $method->invoke($generator, $parsed, $facts);
        $this->assertNotEmpty($notes);
        $this->assertStringContainsString('Invented price or amount 9999', $notes[0]);
    }

    /**
     * Test Deterministic QA: Invented discount percentage fails.
     */
    public function test_deterministic_qa_fails_invented_discount()
    {
        $generator = app(EditorialGenerator::class);
        $facts = [
            'original_price' => 5000.0,
            'discounted_price' => 2500.0,
            'amount_saved' => 2500.0,
            'discount_percentage' => 50.0,
        ];

        $parsed = [
            'editorial_summary' => 'Get this awesome deal at 80% discount today.',
            'editorial_verdict' => 'Massive savings.',
            'pros' => [],
            'cons' => [],
            'best_for' => [],
            'not_for' => []
        ];

        $reflection = new \ReflectionClass(EditorialGenerator::class);
        $method = $reflection->getMethod('deterministicQa');
        $method->setAccessible(true);

        $notes = $method->invoke($generator, $parsed, $facts);
        $this->assertNotEmpty($notes);
        $this->assertStringContainsString('Invented discount 80%', $notes[0]);
    }

    /**
     * Test Semantic QA: Unsupported claims fail-closed.
     */
    public function test_semantic_qa_flags_unsupported_claims()
    {
        $mockAiRouter = Mockery::mock(AIRouter::class);
        $mockAiRouter->shouldReceive('chat')
            ->once()
            ->andReturn([
                'content' => json_encode([
                    'pass' => false,
                    'notes' => ["Removed unsupported claim: 'stylish design'"]
                ]),
                'provider' => 'test-provider',
                'model' => 'test-model'
            ]);

        $generator = new EditorialGenerator($mockAiRouter);
        $facts = ['title' => 'Puma Running Shoes', 'brand' => 'Puma'];
        $parsed = [
            'editorial_summary' => 'Stylish Puma running shoes with premium comfort.',
            'editorial_verdict' => 'Great buy.',
            'pros' => ['Stylish'],
            'cons' => [],
            'best_for' => [],
            'not_for' => []
        ];

        $reflection = new \ReflectionClass(EditorialGenerator::class);
        $method = $reflection->getMethod('semanticQa');
        $method->setAccessible(true);

        $notes = $method->invoke($generator, $parsed, $facts);
        $this->assertNotEmpty($notes);
        $this->assertStringContainsString("Removed unsupported claim: 'stylish design'", $notes[0]);
    }

    /**
     * Test Full Generator Workflow with Retries and Logging into deal_ai_generations.
     */
    public function test_editorial_generator_retries_and_logs_generations()
    {
        $deal = $this->createDraftDeal();

        $mockAiRouter = Mockery::mock(AIRouter::class);

        // Attempt 1: Returns invented price (₹9999), deterministic QA will fail it
        $badGenerationJson = json_encode([
            'editorial_summary' => 'Puma shoes usually ₹9999, now ₹2500.',
            'editorial_verdict' => 'Good deal.',
            'pros' => ['50% off'],
            'cons' => [],
            'best_for' => [],
            'not_for' => []
        ]);

        // Attempt 2: Valid generation & passes semantic QA
        $goodGenerationJson = json_encode([
            'editorial_summary' => 'Puma Mens Running Shoes at ₹2500 with 50% discount.',
            'editorial_verdict' => 'Solid savings on footwear.',
            'pros' => ['50% discount'],
            'cons' => ['Limited stock'],
            'best_for' => [],
            'not_for' => []
        ]);

        $semanticQaPassJson = json_encode([
            'pass' => true,
            'notes' => []
        ]);

        $mockAiRouter->shouldReceive('chat')
            ->times(3) // 1st gen, 2nd gen, 2nd gen semantic QA check
            ->andReturnValues([
                ['content' => $badGenerationJson, 'provider' => 'test-provider', 'model' => 'test-model'],
                ['content' => $goodGenerationJson, 'provider' => 'test-provider', 'model' => 'test-model'],
                ['content' => $semanticQaPassJson, 'provider' => 'test-provider', 'model' => 'test-model'],
            ]);

        $generator = new EditorialGenerator($mockAiRouter);
        $success = $generator->generateForDeal($deal);

        $this->assertTrue($success, "Generator should succeed on 2nd attempt.");

        $deal->refresh();
        $this->assertEquals('IN_REVIEW', $deal->editorial_status);
        $this->assertEquals('Puma Mens Running Shoes at ₹2500 with 50% discount.', $deal->editorial_summary);

        // Verify generations table logging
        $generations = DealAiGeneration::where('deal_id', $deal->id)->orderBy('generation_number', 'asc')->get();
        $this->assertCount(2, $generations);

        // 1st generation record
        $this->assertEquals('FACTUALITY_FAILED', $generations[0]->status);
        $this->assertFalse((bool)$generations[0]->qa_result);
        $this->assertNotEmpty($generations[0]->qa_notes);

        // 2nd generation record
        $this->assertEquals('SUCCESS', $generations[1]->status);
        $this->assertTrue((bool)$generations[1]->qa_result);
        $this->assertEquals('HIGH', $generations[1]->source_completeness);
        $this->assertEquals('HIGH', $generations[1]->content_confidence);
    }
}

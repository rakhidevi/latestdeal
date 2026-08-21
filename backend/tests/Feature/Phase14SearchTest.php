<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductType;
use App\Services\Search\QueryParserService;

class Phase14SearchTest extends TestCase
{
    use RefreshDatabase;

    protected $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new QueryParserService();
        
        // Seed test data
        Brand::create(['name' => 'Puma', 'slug' => 'puma', 'is_active' => true]);
        Brand::create(['name' => 'LG', 'slug' => 'lg', 'is_active' => true]);
        Brand::create(['name' => 'Samsung', 'slug' => 'samsung', 'is_active' => true]);

        Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
        Category::create(['name' => 'Footwear', 'slug' => 'footwear']);
        
        ProductType::create(['name' => 'Running Shoes', 'slug' => 'running-shoes']);
        ProductType::create(['name' => 'Refrigerator', 'slug' => 'refrigerator']);
        ProductType::create(['name' => 'TV', 'slug' => 'tv']);
    }

    public function test_puma_brand_resolution()
    {
        $query = $this->parser->parse("Puma");
        $this->assertNotEmpty($query->brandIds);
        $this->assertEmpty($query->categoryIds);
        $this->assertEmpty($query->productTypeIds);
        $this->assertEmpty($query->keywords);
        
        $brand = Brand::find($query->brandIds[0]);
        $this->assertEquals('Puma', $brand->name);
    }

    public function test_puma_shoes_brand_and_category()
    {
        $query = $this->parser->parse("PUMA shoes");
        $this->assertNotEmpty($query->brandIds);
        $this->assertNotEmpty($query->categoryIds);
        $this->assertEmpty($query->keywords);
    }

    public function test_puma_running_shoes_brand_and_product_type()
    {
        $query = $this->parser->parse("Puma running shoes");
        $this->assertNotEmpty($query->brandIds);
        $this->assertNotEmpty($query->productTypeIds);
        // "Running Shoes" should be extracted entirely, leaving no residual keywords ideally
        // Wait, "Shoes" is also a category. Our parser uses longest-string-first so "Running Shoes" matches,
        // then "Shoes" is already removed from string, so category shouldn't match. Let's verify this in practice.
    }

    public function test_samsung_tv_under_50000()
    {
        $query = $this->parser->parse("Samsung TV under 50000");
        $this->assertEquals(50000, $query->maxPrice);
        $this->assertNotEmpty($query->brandIds);
        $this->assertNotEmpty($query->productTypeIds); // TV
    }
    
    public function test_price_formatting_variations()
    {
        $queries = [
            "under 50k",
            "below 50000",
            "< 50000",
            "50000 or less"
        ];
        
        foreach ($queries as $q) {
            $query = $this->parser->parse($q);
            $this->assertEquals(50000, $query->maxPrice, "Failed on: $q");
        }
    }

    public function test_discount_parsing()
    {
        $queries = [
            "60% off running shoes",
            "Puma shoes 60 percent off",
            "discount above 60%",
            "at least 60%",
            "60%+"
        ];
        
        foreach ($queries as $q) {
            $query = $this->parser->parse($q);
            $this->assertEquals(60, $query->minDiscount, "Failed on: $q");
        }
    }

    public function test_residual_keywords()
    {
        $query = $this->parser->parse("LG 655L Frost Free Refrigerator");
        $this->assertNotEmpty($query->brandIds);
        $this->assertNotEmpty($query->productTypeIds);
        $this->assertContains('655l', $query->keywords);
        $this->assertContains('frost', $query->keywords);
        $this->assertContains('free', $query->keywords);
    }

    public function test_taxonomy_intersection_secondary_category()
    {
        // "Puma Shoes" should return a deal where primary is Footwear and secondary is Shoes
        $puma = Brand::where('name', 'Puma')->first();
        $footwear = Category::where('name', 'Footwear')->first();
        $shoes = Category::where('name', 'Shoes')->first();
        
        $deal = \App\Models\Deal::create([
            'title' => 'Puma Test Deal',
            'brand_id' => $puma->id,
            'category_id' => $footwear->id, // Primary
            'editorial_status' => 'PUBLISHED',
            'original_price' => 1000,
            'discounted_price' => 500,
            'observation_id' => 'test1'
        ]);
        
        $deal->categories()->attach($shoes->id); // Secondary
        
        $query = $this->parser->parse("Puma Shoes");
        
        $searchEngine = new \App\Services\Search\DealSearchService(new \App\Services\Search\SearchRankingService());
        $results = $searchEngine->search($query);
        
        $this->assertCount(1, $results);
        $this->assertEquals($deal->id, $results->first()->id);
    }

    public function test_publication_isolation()
    {
        $puma = Brand::where('name', 'Puma')->first();
        $shoes = Category::where('name', 'Shoes')->first();
        
        $createDeal = function($status) use ($puma, $shoes) {
            return \App\Models\Deal::create([
                'title' => "Puma Deal $status",
                'brand_id' => $puma->id,
                'category_id' => $shoes->id,
                'editorial_status' => $status,
                'original_price' => 1000,
                'discounted_price' => 500,
                'observation_id' => "test_$status"
            ]);
        };
        
        $published = $createDeal('PUBLISHED');
        $draft = $createDeal('DRAFT');
        $inReview = $createDeal('IN_REVIEW');
        $aiGenerating = $createDeal('AI_GENERATING');
        
        $query = $this->parser->parse("Puma Shoes");
        $searchEngine = new \App\Services\Search\DealSearchService(new \App\Services\Search\SearchRankingService());
        $results = $searchEngine->search($query);
        
        $this->assertCount(1, $results);
        $this->assertEquals($published->id, $results->first()->id);
    }

    public function test_ranking_hierarchy()
    {
        $puma = Brand::where('name', 'Puma')->first();
        $nike = Brand::where('name', 'Samsung')->first(); // Using Samsung as Nike is not seeded
        
        $shoes = Category::where('name', 'Shoes')->first();
        $runningShoes = ProductType::where('name', 'Running Shoes')->first();
        
        // 1. Puma Running Shoes (Best Match: Brand + Product Type)
        $deal1 = \App\Models\Deal::create([
            'title' => 'Puma Running Shoes',
            'brand_id' => $puma->id,
            'category_id' => $shoes->id,
            'editorial_status' => 'PUBLISHED',
            'original_price' => 1000,
            'discounted_price' => 500,
            'observation_id' => 'rank1'
        ]);
        $deal1->productTypes()->attach($runningShoes->id);

        // 2. Puma Shoes (Brand + Category)
        $deal2 = \App\Models\Deal::create([
            'title' => 'Puma Casual Shoes',
            'brand_id' => $puma->id,
            'category_id' => $shoes->id,
            'editorial_status' => 'PUBLISHED',
            'original_price' => 1000,
            'discounted_price' => 500,
            'observation_id' => 'rank2'
        ]);

        // 3. Puma Socks (Brand only)
        $deal3 = \App\Models\Deal::create([
            'title' => 'Puma Socks',
            'brand_id' => $puma->id,
            'category_id' => Category::where('name', 'Footwear')->first()->id,
            'editorial_status' => 'PUBLISHED',
            'original_price' => 1000,
            'discounted_price' => 500,
            'observation_id' => 'rank3'
        ]);
        
        $query = $this->parser->parse("Puma running shoes");
        $searchEngine = new \App\Services\Search\DealSearchService(new \App\Services\Search\SearchRankingService());
        $results = $searchEngine->search($query);
        
        // Result order should be Deal1, Deal2, Deal3
        $this->assertCount(3, $results);
        $this->assertEquals($deal1->id, $results[0]->id);
        $this->assertEquals($deal2->id, $results[1]->id);
        $this->assertEquals($deal3->id, $results[2]->id);
    }
}

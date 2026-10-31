<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Deal;
use App\Models\Merchant;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class PriceUpdateControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $merchant = Merchant::firstOrCreate(
            ['name' => 'Amazon India'],
            ['domain' => 'amazon.in', 'store_id' => 'amazon', 'affiliate_param_key' => 'tag', 'status' => 'active']
        );

        $category = Category::firstOrCreate(
            ['slug' => 'electronics'],
            ['name' => 'Electronics']
        );

        $deal = new Deal();
        $deal->id = 446;
        $deal->title = 'Noise ColorFit Pulse 2 Max 1.85" Display Bluetooth Calling Smart Watch';
        $deal->slug = 'noise-colorfit-pulse-2-max-446';
        $deal->original_price = 5999;
        $deal->discounted_price = 1499;
        $deal->status = 'out_of_stock';
        $deal->merchant_id = $merchant->id;
        $deal->category_id = $category->id;
        $deal->url = 'https://www.amazon.in/dp/B0B6BLTGTT';
        $deal->image_path = 'deals/default.png';
        $deal->editorial_status = Deal::STATUS_PUBLISHED;
        $deal->save();
    }

    public function test_amazon_product_with_twister_script_does_not_falsely_flag_out_of_stock()
    {
        // Amazon HTML with twister JS dictionary containing 'Currently unavailable.' in script tag,
        // but real DOM availability showing 'In stock' and price 1299
        $mockHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <script>
        var twisterData = {"currentlyUnavailableMessage":"Currently unavailable."};
    </script>
</head>
<body>
    <div id="availability">
        <span class="a-size-medium a-color-success"> In stock </span>
    </div>
    <span class="priceToPay">
        <span class="a-price-whole">1,299</span>
    </span>
    <span class="a-text-price">
        <span class="a-offscreen">₹5,999</span>
    </span>
    <input type="submit" id="add-to-cart-button" value="Add to Cart" />
</body>
</html>
HTML;

        Http::fake([
            'https://www.amazon.in/dp/B0B6BLTGTT' => Http::response($mockHtml, 200),
        ]);

        $deal = Deal::find(446);
        $this->assertEquals('out_of_stock', $deal->status);

        $response = $this->postJson('/api/deals/446/refresh-price');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'deal_id' => 446,
            'discounted_price' => 1299.0,
            'original_price' => 5999.0,
            'discount_pct' => 78,
        ]);

        $deal->refresh();
        $this->assertEquals('active', $deal->status);
        $this->assertEquals(1299, $deal->discounted_price);
    }

    public function test_amazon_product_genuinely_out_of_stock_is_flagged()
    {
        $mockHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
</head>
<body>
    <div id="outOfStock">
        <span class="a-color-price">Currently unavailable.</span>
        <br>We don't know when or if this item will be back in stock.
    </div>
</body>
</html>
HTML;

        Http::fake([
            'https://www.amazon.in/dp/B0B6BLTGTT' => Http::response($mockHtml, 200),
        ]);

        $deal = Deal::find(446);
        $deal->status = 'active';
        $deal->save();

        $response = $this->postJson('/api/deals/446/refresh-price');

        $response->assertJson([
            'success' => false,
            'is_out_of_stock' => true,
            'message' => 'This product is currently out of stock on Amazon India.',
        ]);

        $deal->refresh();
        $this->assertEquals('out_of_stock', $deal->status);
    }
}

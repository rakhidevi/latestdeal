<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deal;
use App\Models\Merchant;
use App\Models\Tag;
use App\Models\PriceHistory;
use App\Models\Category;
use Illuminate\Support\Str;

class RealDealSeeder extends Seeder
{
    public function run(): void
    {
        $amazon = Merchant::updateOrCreate(
            ['name' => 'Amazon India'],
            ['domain' => 'amazon.in', 'affiliate_param_key' => 'tag', 'store_id' => 'latestdeal-21', 'status' => 1]
        );

        $flipkart = Merchant::updateOrCreate(
            ['name' => 'Flipkart'],
            ['domain' => 'flipkart.com', 'affiliate_param_key' => 'affid', 'store_id' => 'latestdeal', 'status' => 1]
        );

        $croma = Merchant::updateOrCreate(
            ['name' => 'Croma'],
            ['domain' => 'croma.com', 'affiliate_param_key' => 'affid', 'store_id' => 'latestdeal', 'status' => 1]
        );

        $reliance = Merchant::updateOrCreate(
            ['name' => 'Reliance Digital'],
            ['domain' => 'reliancedigital.in', 'affiliate_param_key' => 'affid', 'store_id' => 'latestdeal', 'status' => 1]
        );

        $myntra = Merchant::updateOrCreate(
            ['name' => 'Myntra'],
            ['domain' => 'myntra.com', 'affiliate_param_key' => 'affid', 'store_id' => 'latestdeal', 'status' => 1]
        );

        $mobilesCat = Category::firstOrCreate(['slug' => 'mobiles'], ['name' => 'Mobiles']);
        $electronicsCat = Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics']);
        $homeCat = Category::firstOrCreate(['slug' => 'home-kitchen'], ['name' => 'Home & Kitchen']);
        $fitnessCat = Category::firstOrCreate(['slug' => 'sports-fitness'], ['name' => 'Sports & Fitness']);
        $fashionCat = Category::firstOrCreate(['slug' => 'fashion-accessories'], ['name' => 'Fashion & Accessories']);
        $beautyCat = Category::firstOrCreate(['slug' => 'beauty-personal-care'], ['name' => 'Beauty & Personal Care']);
        $courseCat = Category::firstOrCreate(['slug' => 'courses-education'], ['name' => 'Courses & Education']);
        $gamingCat = Category::firstOrCreate(['slug' => 'gaming'], ['name' => 'Gaming']);

        $dealsData = [
            [
                'title' => 'EF ECOFLOW DELTA 2 Portable Power Station | 1024Wh LiFePO4 Battery',
                'description' => 'Fast Charging 0-80% in 50 Mins, Solar Generator for Outdoor Camping, Home Backup Power Station.',
                'original_price' => 105000,
                'discounted_price' => 65990,
                'category_id' => $homeCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B0F38D5BH6',
                'image_path' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(10),
                'tags' => ['Home & Kitchen', 'Power Station', 'EF ECOFLOW'],
                'promo' => null
            ],
            [
                'title' => 'Fitkit by Cult Walking Pad Neo (BLDC 3.5HP Peak Power) Compact Under-desk Treadmill',
                'description' => 'Compact Smart Motorized Under Desk Walking Treadmill with LED Display, Remote Control, Free Dietitian Consult.',
                'original_price' => 19990,
                'discounted_price' => 7999,
                'category_id' => $fitnessCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B0GYSTL6QT',
                'image_path' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(5),
                'tags' => ['Sports & Fitness', 'Treadmill', 'Fitkit'],
                'promo' => null
            ],
            [
                'title' => 'ZEBRONICS Juke BAR 9510WS PRO Dolby 5.1 Soundbar',
                'description' => '5.1 Channel Soundbar with Wireless Subwoofer & Rear Speakers, Dolby Audio, 525W RMS Output, Bluetooth 5.0.',
                'original_price' => 24999,
                'discounted_price' => 9499,
                'category_id' => $electronicsCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B0DGPZTGB4',
                'image_path' => 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(12),
                'tags' => ['Electronics', 'Audio', 'ZEBRONICS'],
                'promo' => null
            ],
            [
                'title' => 'Spacewood Optima 3 Door Wardrobe for Bedroom with Lockable Door & Hanging Rod',
                'description' => 'Spacious 3 Door Engineered Wood Armoire Closet with Shelves and Lockable Drawers for Bedroom Storage.',
                'original_price' => 34999,
                'discounted_price' => 24148,
                'category_id' => $homeCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B0DWK616GS',
                'image_path' => 'https://images.unsplash.com/photo-1595428774223-ef52624120d2?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(8),
                'tags' => ['Home & Kitchen', 'Furniture', 'Spacewood'],
                'promo' => null
            ],
            [
                'title' => 'Apple iPhone 15 (128 GB) - Blue',
                'description' => 'Dynamic Island bubbles up alerts and Live Activities. INNOVATIVE DESIGN — iPhone 15 features a durable color-infused glass and aluminum design.',
                'original_price' => 79900,
                'discounted_price' => 69900,
                'category_id' => $mobilesCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B0CHX1W1XY',
                'image_path' => 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(7),
                'tags' => ['Mobiles', 'Smartphone', 'Apple'],
                'promo' => null
            ],
            [
                'title' => 'OnePlus Nord CE4 5G (8GB RAM, 128GB Storage, Celadon Marble)',
                'description' => 'Qualcomm Snapdragon 7 Gen 3, 100W SUPERVOOC Fast Charging, 5500mAh Battery, 50MP Sony LYT-600 Camera with OIS.',
                'original_price' => 24999,
                'discounted_price' => 19998,
                'category_id' => $mobilesCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B0CX58MTNN',
                'image_path' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(15),
                'tags' => ['Mobiles', 'Smartphone', 'OnePlus'],
                'promo' => null
            ],
            [
                'title' => 'Realme Narzo 70 Pro 5G (8GB RAM, 128GB Storage, Glass Green)',
                'description' => 'Dimensity 7050 5G Chipset, Flagship Sony IMX890 OIS Camera, Horizon Glass Design, 67W SUPERVOOC Charge.',
                'original_price' => 24999,
                'discounted_price' => 17999,
                'category_id' => $mobilesCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B0CX1Y49P2',
                'image_path' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(12),
                'tags' => ['Mobiles', 'Smartphone', 'Realme'],
                'promo' => null
            ],
            [
                'title' => 'Samsung Galaxy M35 5G (6GB RAM, 128GB Storage, Daybreak Blue)',
                'description' => 'Corning Gorilla Glass Victus+, 6000mAh Battery, 50MP OIS Triple Camera, Vapor Cooling Chamber, Super AMOLED 120Hz Display.',
                'original_price' => 24499,
                'discounted_price' => 16999,
                'category_id' => $mobilesCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B0D7Z8FJZ9',
                'image_path' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(10),
                'tags' => ['Mobiles', 'Smartphone', 'Samsung'],
                'promo' => null
            ],
            [
                'title' => 'Sony WH-1000XM5 Wireless Noise Cancelling Headphones',
                'description' => 'Industry Leading Noise Cancellation. Up to 30-hour battery life with quick charging. Ultra-comfortable lightweight design. Note: Stock status verified.',
                'original_price' => 34990,
                'discounted_price' => 26990,
                'category_id' => $electronicsCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B09Y2MYL5C',
                'image_path' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop',
                'status' => 'out_of_stock',
                'expires_at' => now()->addDays(3),
                'tags' => ['Electronics', 'Audio', 'Headphones'],
                'promo' => null
            ],
            [
                'title' => 'Noise ColorFit Pulse 2 Max 1.85" Display Bluetooth Calling Smart Watch',
                'description' => '1.85" TFT Display, 550 NITS Brightness, 100 Sports Modes, Smart DND, Noise Health Suite.',
                'original_price' => 5999,
                'discounted_price' => 1499,
                'category_id' => $electronicsCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B0B6BLTGTT',
                'image_path' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(9),
                'tags' => ['Electronics', 'Smartwatch', 'Noise'],
                'promo' => 'NOISE75'
            ],
            [
                'title' => 'Noise Buds VS102 Plus Wireless Earbuds with 70H Playtime',
                'description' => 'Instacharge (10 min charge = 120 min playtime), 11mm Driver, IPX5 Waterproof, Hyper Sync Tech.',
                'original_price' => 3499,
                'discounted_price' => 999,
                'category_id' => $electronicsCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B0DS2XNXZB',
                'image_path' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(6),
                'tags' => ['Electronics', 'Audio', 'Noise'],
                'promo' => null
            ],
            [
                'title' => 'ASUS TUF Gaming F15 Laptop',
                'description' => '15.6" FHD 144Hz, Intel Core i5-11400H 11th Gen, RTX 2050 4GB Graphics, 16GB RAM, 512GB SSD, Windows 11',
                'original_price' => 74990,
                'discounted_price' => 54990,
                'category_id' => $gamingCat->id,
                'merchant_id' => $amazon->id,
                'url' => 'https://www.amazon.in/dp/B0C27SD9YR',
                'image_path' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(14),
                'tags' => ['Gaming', 'Laptops', 'ASUS'],
                'promo' => null
            ],
            // Non-Amazon Multi-Merchant Real Deals
            [
                'title' => 'Motorola Edge 50 Fusion 5G (8GB RAM, 128GB Storage, Marshmallow Blue)',
                'description' => 'Snapdragon 7s Gen 2 Processor, 50MP Sony LYT-700C OIS Camera, 144Hz 3D Curved pOLED Display, IP68 Underwater Protection, 68W TurboPower.',
                'original_price' => 25999,
                'discounted_price' => 21999,
                'category_id' => $mobilesCat->id,
                'merchant_id' => $flipkart->id,
                'url' => 'https://www.flipkart.com/motorola-edge-50-fusion/p/itm7d39b15599c7e',
                'image_path' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(14),
                'tags' => ['Mobiles', 'Smartphone', 'Motorola'],
                'promo' => null
            ],
            [
                'title' => 'Nothing Phone (2a) 5G (8GB RAM, 128GB Storage, Black)',
                'description' => 'Custom Dimensity 7200 Pro Processor, 50MP Dual Camera with OIS, Iconic Transparent Design with Glyph Interface, 120Hz Flexible AMOLED.',
                'original_price' => 25999,
                'discounted_price' => 23999,
                'category_id' => $mobilesCat->id,
                'merchant_id' => $flipkart->id,
                'url' => 'https://www.flipkart.com/nothing-phone-2a-5g-black-128-gb/p/itm85c6bca5edadc',
                'image_path' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(10),
                'tags' => ['Mobiles', 'Smartphone', 'Nothing'],
                'promo' => null
            ],
            [
                'title' => 'Apple iPad 10th Gen (10.9 Inch, Wi-Fi, 64GB, Silver)',
                'description' => '10.9-inch Liquid Retina Display with True Tone, A14 Bionic Chip with 6-core CPU and 4-core GPU, 12MP Wide back camera, Touch ID.',
                'original_price' => 34900,
                'discounted_price' => 30990,
                'category_id' => $electronicsCat->id,
                'merchant_id' => $croma->id,
                'url' => 'https://www.croma.com/apple-ipad-10th-generation-wi-fi-64gb-silver/p/264263',
                'image_path' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(12),
                'tags' => ['Electronics', 'Tablets', 'Apple'],
                'promo' => null
            ],
            [
                'title' => 'Sony WH-CH720N Wireless Over-Ear Active Noise Cancelling Headphones',
                'description' => 'Integrated Processor V1 for Noise Cancellation, Lightweight comfortable design, Up to 35 hours battery life with quick charging.',
                'original_price' => 14990,
                'discounted_price' => 9990,
                'category_id' => $electronicsCat->id,
                'merchant_id' => $reliance->id,
                'url' => 'https://www.reliancedigital.in/sony-wh-ch720n-active-noise-cancelling-wireless-headphones/p/493666244',
                'image_path' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(8),
                'tags' => ['Electronics', 'Audio', 'Sony'],
                'promo' => null
            ],
            [
                'title' => 'Puma Men Softride Rift Tech Running Shoes',
                'description' => 'SOFTRIDE midsole for extreme cushioning and all-day comfort, SoftFoam+ sockliner, breathable knit upper, durable rubber traction outsole.',
                'original_price' => 6499,
                'discounted_price' => 2924,
                'category_id' => $fashionCat->id,
                'merchant_id' => $myntra->id,
                'url' => 'https://www.myntra.com/sports-shoes/puma/puma-men-softride-rift-tech-running-shoes/24681324/buy',
                'image_path' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop',
                'status' => 'active',
                'expires_at' => now()->addDays(15),
                'tags' => ['Fashion & Accessories', 'Shoes', 'Puma'],
                'promo' => 'MYNTRA20'
            ]
        ];

        $categoriesData = [
            ['name' => 'Apparel & Accessories', 'commission_rate' => 10.0],
            ['name' => 'Shoes', 'commission_rate' => 10.0],
            ['name' => 'Luggage & Bags', 'commission_rate' => 10.0],
            ['name' => 'Watches', 'commission_rate' => 10.0],
            ['name' => 'Beauty', 'commission_rate' => 10.0],
            ['name' => 'Kitchen', 'commission_rate' => 5.0],
            ['name' => 'Furniture', 'commission_rate' => 5.0],
            ['name' => 'Home', 'commission_rate' => 5.0],
            ['name' => 'Grocery', 'commission_rate' => 4.7],
            ['name' => 'Amazon Fresh', 'commission_rate' => 4.7],
            ['name' => 'Health and Personal Care', 'commission_rate' => 4.7],
            ['name' => 'Echo & Alexa Devices', 'commission_rate' => 5.0],
            ['name' => 'Fire TV Devices', 'commission_rate' => 5.0],
            ['name' => 'Pet Products', 'commission_rate' => 4.7],
            ['name' => 'Mobile Accessories', 'commission_rate' => 4.0],
            ['name' => 'Books', 'commission_rate' => 5.9],
            ['name' => 'Toys', 'commission_rate' => 5.9],
            ['name' => 'Personal Care Appliances', 'commission_rate' => 5.9],
            ['name' => 'Baby Products', 'commission_rate' => 5.9],
            ['name' => 'Automotive', 'commission_rate' => 5.9],
            ['name' => 'Sports', 'commission_rate' => 5.9],
            ['name' => 'BISS', 'commission_rate' => 3.5],
            ['name' => 'Lawn & Garden', 'commission_rate' => 3.5],
            ['name' => 'Video Games', 'commission_rate' => 3.5],
            ['name' => 'Large Appliances', 'commission_rate' => 3.5],
            ['name' => 'Televisions', 'commission_rate' => 3.5],
            ['name' => 'Personal Computers', 'commission_rate' => 3.5],
            ['name' => 'Smart Watches', 'commission_rate' => 3.5],
            ['name' => 'Electronics', 'commission_rate' => 3.5],
            ['name' => 'Bicycles & Heavy Gym Equipment', 'commission_rate' => 2.5],
            ['name' => 'Tyres & Rims', 'commission_rate' => 2.5],
            ['name' => 'Data Storage Devices', 'commission_rate' => 2.0],
            ['name' => 'Mobile Phones', 'commission_rate' => 1.0],
            ['name' => 'Bill Payment & Recharges', 'commission_rate' => 0.0],
        ];

        foreach ($categoriesData as $cat) {
            $catAttrs = ['name' => $cat['name']];
            if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'commission_rate')) {
                $catAttrs['commission_rate'] = $cat['commission_rate'];
            }
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                $catAttrs
            );
        }

        foreach ($dealsData as $data) {
            $tags = $data['tags'] ?? [];
            unset($data['tags']);
            
            $promo = $data['promo'] ?? null;
            unset($data['promo']);

            unset($data['description']);
            unset($data['expires_at']);
            
            $data['promo_code'] = $promo;
            $data['editorial_status'] = Deal::STATUS_PUBLISHED;
            if (\Illuminate\Support\Facades\Schema::hasColumn('deals', 'ai_score')) {
                $data['ai_score'] = 92;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('deals', 'confidence_score')) {
                $data['confidence_score'] = 92;
            }

            $deal = Deal::where('url', $data['url'])->first();
            if (!$deal) {
                $deal = Deal::where('title', $data['title'])->first();
            }

            if ($deal) {
                $deal->update($data);
                if (!str_ends_with($deal->slug, '-' . $deal->id)) {
                    $deal->slug = Str::slug($deal->title) . '-' . $deal->id;
                    $deal->saveQuietly();
                }
            } else {
                $data['slug'] = Str::slug($data['title']);
                $deal = Deal::create($data);
                if (!str_ends_with($deal->slug, '-' . $deal->id)) {
                    $deal->slug = $deal->slug . '-' . $deal->id;
                    $deal->saveQuietly();
                }
            }

            // Attach tags
            $tagIds = [];
            foreach ($tags as $tagName) {
                $tag = Tag::firstOrCreate([
                    'slug' => \Illuminate\Support\Str::slug($tagName)
                ], [
                    'name' => $tagName
                ]);
                $tagIds[] = $tag->id;
            }
            $deal->tags()->sync($tagIds);

            // Clean up any historical price points that are invalid (e.g. exceeding original price * 1.05)
            $deal->priceHistories()->where('price', '>', $data['original_price'] * 1.05)->delete();

            // Record initial or refreshed clean price history if fewer than 2 valid points exist
            if ($deal->priceHistories()->count() < 2) {
                $deal->priceHistories()->delete();

                $midPrice = (int) round($data['discounted_price'] + ($data['original_price'] - $data['discounted_price']) * 0.4);

                PriceHistory::create([
                    'deal_id' => $deal->id,
                    'price' => $data['original_price'],
                    'recorded_at' => now()->subDays(15)
                ]);
                PriceHistory::create([
                    'deal_id' => $deal->id,
                    'price' => $midPrice,
                    'recorded_at' => now()->subDays(5)
                ]);
                PriceHistory::create([
                    'deal_id' => $deal->id,
                    'price' => $data['discounted_price'],
                    'recorded_at' => now()
                ]);
            }

            // Reset stale price_intelligence cache if column exists
            if (\Illuminate\Support\Facades\Schema::hasColumn('deals', 'price_intelligence')) {
                $deal->update(['price_intelligence' => null]);
            }
        }

        // Recount all categories, brands, and merchants
        if (class_exists(\App\Services\Catalog\BrandCounter::class)) {
            app(\App\Services\Catalog\BrandCounter::class)->recountAll();
        }
    }
}

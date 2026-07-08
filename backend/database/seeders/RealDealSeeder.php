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
        $merchant = Merchant::firstOrCreate(
            ['name' => 'Amazon India'],
            ['domain' => 'amazon.in', 'affiliate_param_key' => 'tag', 'store_id' => 'latestdeal-21']
        );

        $category = \App\Models\Category::firstOrCreate(
            ['name' => 'Electronics'],
            ['slug' => 'electronics']
        );

        $dealsData = [
            [
                'title' => 'Apple iPhone 15 (128 GB) - Blue',
                'description' => 'Dynamic Island bubbles up alerts and Live Activities. INNOVATIVE DESIGN — iPhone 15 features a durable color-infused glass and aluminum design.',
                'original_price' => 79900,
                'discounted_price' => 69900,
                'category_id' => $category->id,
                'merchant_id' => $merchant->id,
                'url' => 'https://www.amazon.in/dp/B0CHX1W1XY',
                'image_path' => '/storage/deals/iphone15.jpg',
                'status' => 'active',
                'expires_at' => now()->addDays(7),
                'tags' => ['Electronics', 'Smartphone', 'Apple'],
                'promo' => null
            ],
            [
                'title' => 'Sony WH-1000XM5 Wireless Noise Cancelling Headphones',
                'description' => 'Industry Leading Noise Cancellation. Up to 30-hour battery life with quick charging. Ultra-comfortable lightweight design.',
                'original_price' => 34990,
                'discounted_price' => 26990,
                'category_id' => $category->id,
                'merchant_id' => $merchant->id,
                'url' => 'https://www.amazon.in/dp/B09Y2MYL5C',
                'image_path' => '/storage/deals/sony_xm5.jpg',
                'status' => 'active',
                'expires_at' => now()->addDays(3),
                'tags' => ['Electronics', 'Audio', 'Headphones'],
                'promo' => 'SONY10'
            ],
            [
                'title' => 'ASUS TUF Gaming F15 Laptop',
                'description' => '15.6" FHD 144Hz, Intel Core i5-11400H 11th Gen, RTX 3050 4GB Graphics, 8GB RAM, 512GB SSD, Windows 11',
                'original_price' => 74990,
                'discounted_price' => 54990,
                'category_id' => $category->id,
                'merchant_id' => $merchant->id,
                'url' => 'https://www.amazon.in/dp/B09CCW5XW8',
                'image_path' => '/storage/deals/asus_tuf.jpg',
                'status' => 'active',
                'expires_at' => now()->addDays(14),
                'tags' => ['Electronics', 'Laptops', 'Gaming'],
                'promo' => null
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
            ['name' => 'Bill Payment & Recharges', 'commission_rate' => 0.0], // Flat max up to INR 3, handling this as 0% for now or special logic later
            ['name' => 'All Other Categories', 'commission_rate' => 5.0],
        ];

        foreach ($categoriesData as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'commission_rate' => $cat['commission_rate']
                ]
            );
        }

        foreach ($dealsData as $data) {
            $tags = $data['tags'];
            unset($data['tags']);
            
            $promo = $data['promo'];
            unset($data['promo']);

            unset($data['description']);
            unset($data['expires_at']);
            
            $data['promo_code'] = $promo;

            $deal = Deal::create($data);

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

            // Create some fake price history
            PriceHistory::create([
                'deal_id' => $deal->id,
                'price' => $data['original_price'],
                'recorded_at' => now()->subDays(15)
            ]);
            PriceHistory::create([
                'deal_id' => $deal->id,
                'price' => $data['original_price'] - 2000,
                'recorded_at' => now()->subDays(5)
            ]);
            PriceHistory::create([
                'deal_id' => $deal->id,
                'price' => $data['discounted_price'],
                'recorded_at' => now()
            ]);
        }
    }
}

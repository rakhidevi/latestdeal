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
                'original_price' => 659900,
                'discounted_price' => 65990,
                'category_id' => $homeCat->id,
                'merchant_id' => $merchant->id,
                'url' => 'https://www.amazon.in/dp/B0BP9RR1E6',
                'image_path' => '/storage/deals/ecoflow.jpg',
                'status' => 'active',
                'expires_at' => now()->addDays(10),
                'tags' => ['Home & Kitchen', 'Power Station', 'EF ECOFLOW'],
                'promo' => 'ECOFLOW90'
            ],
            [
                'title' => 'Fitkit by Cult Walking Pad Neo (BLDC 3.5HP Peak Power) Compact Under-desk Treadmill',
                'description' => 'Compact Smart Motorized Under Desk Walking Treadmill with LED Display, Remote Control, Free Dietitian Consult.',
                'original_price' => 79990,
                'discounted_price' => 7999,
                'category_id' => $fitnessCat->id,
                'merchant_id' => $merchant->id,
                'url' => 'https://www.amazon.in/dp/B0CGXB345Y',
                'image_path' => '/storage/deals/walking_pad.jpg',
                'status' => 'active',
                'expires_at' => now()->addDays(5),
                'tags' => ['Sports & Fitness', 'Treadmill', 'Fitkit'],
                'promo' => 'FITKIT90'
            ],
            [
                'title' => 'ZEBRONICS Juke BAR 9510WS PRO Dolby 5.1 Soundbar',
                'description' => '5.1 Channel Soundbar with Wireless Subwoofer & Rear Speakers, Dolby Audio, 525W RMS Output, Bluetooth 5.0.',
                'original_price' => 54999,
                'discounted_price' => 9499,
                'category_id' => $electronicsCat->id,
                'merchant_id' => $merchant->id,
                'url' => 'https://www.amazon.in/dp/B0C39RKQW7',
                'image_path' => '/storage/deals/soundbar.jpg',
                'status' => 'active',
                'expires_at' => now()->addDays(12),
                'tags' => ['Electronics', 'Audio', 'ZEBRONICS'],
                'promo' => null
            ],
            [
                'title' => 'Bniture 3 Door Wardrobe for Bedroom with Lockable Door & Hanging Rod',
                'description' => 'Spacious 3 Door Engineered Wood Armoire Closet with Shelves and Lockable Drawers for Bedroom Storage.',
                'original_price' => 99199,
                'discounted_price' => 24148,
                'category_id' => $homeCat->id,
                'merchant_id' => $merchant->id,
                'url' => 'https://www.amazon.in/dp/B0BHQ49X3K',
                'image_path' => '/storage/deals/wardrobe.jpg',
                'status' => 'active',
                'expires_at' => now()->addDays(8),
                'tags' => ['Home & Kitchen', 'Furniture', 'Bniture'],
                'promo' => null
            ],
            [
                'title' => 'Apple iPhone 15 (128 GB) - Blue',
                'description' => 'Dynamic Island bubbles up alerts and Live Activities. INNOVATIVE DESIGN — iPhone 15 features a durable color-infused glass and aluminum design.',
                'original_price' => 79900,
                'discounted_price' => 69900,
                'category_id' => $electronicsCat->id,
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
                'category_id' => $electronicsCat->id,
                'merchant_id' => $merchant->id,
                'url' => 'https://www.amazon.in/dp/B09Y2MYL5C',
                'image_path' => '/storage/deals/sony_xm5.jpg',
                'status' => 'active',
                'expires_at' => now()->addDays(3),
                'tags' => ['Electronics', 'Audio', 'Headphones'],
                'promo' => 'SONY10'
            ],
            [
                'title' => 'Noise ColorFit Pulse 2 Max 1.85" Display Bluetooth Calling Smart Watch',
                'description' => '1.85" TFT Display, 550 NITS Brightness, 100 Sports Modes, Smart DND, Noise Health Suite.',
                'original_price' => 5999,
                'discounted_price' => 1499,
                'category_id' => $electronicsCat->id,
                'merchant_id' => $merchant->id,
                'url' => 'https://www.amazon.in/dp/B0B5L2B56Y',
                'image_path' => '/storage/deals/noise_watch.jpg',
                'status' => 'active',
                'expires_at' => now()->addDays(9),
                'tags' => ['Electronics', 'Smartwatch', 'Noise'],
                'promo' => 'NOISE75'
            ],
            [
                'title' => 'Noise Buds VS102 Truly Wireless Earbuds with 50H Playtime',
                'description' => 'Instacharge (10 min charge = 120 min playtime), 11mm Driver, IPX5 Waterproof, Hyper Sync Tech.',
                'original_price' => 3499,
                'discounted_price' => 999,
                'category_id' => $electronicsCat->id,
                'merchant_id' => $merchant->id,
                'url' => 'https://www.amazon.in/dp/B09CGCB8LL',
                'image_path' => '/storage/deals/noise_earbuds.jpg',
                'status' => 'active',
                'expires_at' => now()->addDays(6),
                'tags' => ['Electronics', 'Audio', 'Noise'],
                'promo' => null
            ],
            [
                'title' => 'ASUS TUF Gaming F15 Laptop',
                'description' => '15.6" FHD 144Hz, Intel Core i5-11400H 11th Gen, RTX 3050 4GB Graphics, 8GB RAM, 512GB SSD, Windows 11',
                'original_price' => 74990,
                'discounted_price' => 54990,
                'category_id' => $gamingCat->id,
                'merchant_id' => $merchant->id,
                'url' => 'https://www.amazon.in/dp/B09CCW5XW8',
                'image_path' => '/storage/deals/asus_tuf.jpg',
                'status' => 'active',
                'expires_at' => now()->addDays(14),
                'tags' => ['Gaming', 'Laptops', 'ASUS'],
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

            $deal = Deal::updateOrCreate(
                ['url' => $data['url']],
                $data
            );

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

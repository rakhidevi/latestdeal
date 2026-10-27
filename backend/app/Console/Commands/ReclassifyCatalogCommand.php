<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deal;
use App\Models\Category;
use Illuminate\Support\Str;

class ReclassifyCatalogCommand extends Command
{
    protected $signature = 'catalog:reclassify';
    protected $description = 'Reclassify deals into accurate product categories based on validated title taxonomy';

    public function handle(): int
    {
        $this->info('Starting catalog reclassification...');

        // Ensure target categories exist with clean slugs and icons
        $categoryDefinitions = [
            'mobiles' => ['name' => 'Mobiles', 'icon' => '📱'],
            'electronics' => ['name' => 'Electronics', 'icon' => '💻'],
            'home-kitchen' => ['name' => 'Home & Kitchen', 'icon' => '🏡'],
            'fashion-accessories' => ['name' => 'Fashion & Accessories', 'icon' => '👗'],
            'beauty-personal-care' => ['name' => 'Beauty & Personal Care', 'icon' => '💄'],
            'sports-fitness' => ['name' => 'Sports & Fitness', 'icon' => '🏋️'],
            'software' => ['name' => 'Software & Antivirus', 'icon' => '🛡️'],
        ];

        $categories = [];
        foreach ($categoryDefinitions as $slug => $meta) {
            $categories[$slug] = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $meta['name'], 'icon' => $meta['icon']]
            );
        }

        $deals = Deal::all();
        $reclassifiedCount = 0;

        foreach ($deals as $deal) {
            $titleLower = strtolower($deal->title);
            $targetSlug = null;

            // 1. Software & Antivirus
            if (preg_match('/\b(norton|antivirus|vpn|software|microsoft 365|office 2024)\b/i', $titleLower)) {
                $targetSlug = 'software';
            }
            // 2. Sports & Fitness
            elseif (preg_match('/\b(treadmill|walking pad|dumbbell|gym|yoga mat|exercise bike|protein|shaker)\b/i', $titleLower)) {
                $targetSlug = 'sports-fitness';
            }
            // 3. Beauty & Personal Care
            elseif (preg_match('/\b(face wash|cream|serum|shampoo|conditioner|lotion|sunscreen|lipstick|perfume|deodorant|skincare|hair dryer|trimmer|shaver|soap)\b/i', $titleLower)) {
                $targetSlug = 'beauty-personal-care';
            }
            // 4. Fashion & Accessories
            elseif (preg_match('/\b(shirt|t-shirt|jeans|shoes|sneaker|jacket|dress|saree|kurta|wallet|belt|backpack|trousers|sunglasses|sandal|clog)\b/i', $titleLower)) {
                $targetSlug = 'fashion-accessories';
            }
            // 5. Home & Kitchen (Catches faucet filters, key holders, LED bulbs, cookware)
            elseif (preg_match('/\b(holder|faucet|filter|tap|bulb|led|light|lamp|kitchen|cookware|curtain|bedsheet|mattress|pillow|furniture|bottle|mop|decor|towel|pan|pot|clock|storage rack|knife|spoon|basin|purifier|geyser|vacuum|fan|heater|cleaner kit|cleaning kit)\b/i', $titleLower)) {
                $targetSlug = 'home-kitchen';
            }
            // 6. Mobiles (Smartphones)
            elseif (preg_match('/\b(smartphone|smartphones|mobile|mobiles|iphone|galaxy s\d+|galaxy a\d+|galaxy m\d+|galaxy z|realme|oneplus|redmi|poco|pixel \d+|motorola|iqoo)\b/i', $titleLower) && !preg_match('/\b(charger|charging cable|case|cover)\b/i', $titleLower)) {
                $targetSlug = 'mobiles';
            }
            // 7. Electronics (Laptops, TVs, Audio, Tablets, Wearables, Accessories)
            elseif (preg_match('/\b(laptop|macbook|notebook|tv|television|soundbar|speaker|headphone|headphones|earbud|earbuds|earphone|airpod|airpods|smartwatch|camera|monitor|tablet|ipad|printer|keyboard|mouse|power bank|charger|cable)\b/i', $titleLower)) {
                $targetSlug = 'electronics';
            }

            if ($targetSlug && isset($categories[$targetSlug])) {
                $newCatId = $categories[$targetSlug]->id;
                if ($deal->category_id !== $newCatId) {
                    $deal->category_id = $newCatId;
                    $deal->saveQuietly();
                    $reclassifiedCount++;
                }
            }
        }

        // Update category deal counts
        foreach ($categories as $cat) {
            $count = Deal::where('category_id', $cat->id)->where('status', 'active')->count();
            if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'deal_count')) {
                $cat->deal_count = $count;
                $cat->saveQuietly();
            }
        }

        $this->info("Successfully reclassified {$reclassifiedCount} deals into verified categories.");
        return 0;
    }
}

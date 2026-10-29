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

        $merchantsMap = [
            'Amazon India' => $amazon,
            'Flipkart' => $flipkart,
            'Croma' => $croma,
            'Reliance Digital' => $reliance,
            'Myntra' => $myntra,
        ];

        $categoriesMap = [
            'mobiles' => Category::firstOrCreate(['slug' => 'mobiles'], ['name' => 'Mobiles']),
            'electronics' => Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics']),
            'home-kitchen' => Category::firstOrCreate(['slug' => 'home-kitchen'], ['name' => 'Home & Kitchen']),
            'sports-fitness' => Category::firstOrCreate(['slug' => 'sports-fitness'], ['name' => 'Sports & Fitness']),
            'fashion-accessories' => Category::firstOrCreate(['slug' => 'fashion-accessories'], ['name' => 'Fashion & Accessories']),
            'beauty-personal-care' => Category::firstOrCreate(['slug' => 'beauty-personal-care'], ['name' => 'Beauty & Personal Care']),
            'gaming' => Category::firstOrCreate(['slug' => 'gaming'], ['name' => 'Gaming']),
        ];

        $jsonFile = __DIR__ . '/deals_seed_data.json';
        if (!file_exists($jsonFile)) {
            return;
        }

        $dealsData = json_decode(file_get_contents($jsonFile), true) ?: [];

        foreach ($dealsData as $data) {
            $tags = $data['tags'] ?? [];
            unset($data['tags']);
            unset($data['availability_status'], $data['availability_note']);

            if (!\Illuminate\Support\Facades\Schema::hasColumn('deals', 'ai_score')) {
                unset($data['ai_score']);
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('deals', 'confidence_score')) {
                unset($data['confidence_score']);
            }

            $catSlug = $data['category_slug'] ?? 'electronics';
            unset($data['category_slug']);
            $data['category_id'] = $categoriesMap[$catSlug]->id ?? $categoriesMap['electronics']->id;

            $mName = $data['merchant_name'] ?? 'Amazon India';
            unset($data['merchant_name']);
            $data['merchant_id'] = $merchantsMap[$mName]->id ?? $amazon->id;

            $data['editorial_status'] = Deal::STATUS_PUBLISHED;
            $data['is_editor_pick'] = true;
            $data['reviewed_at'] = now();

            if (\Illuminate\Support\Facades\Schema::hasColumn('deals', 'ai_score')) {
                $data['ai_score'] = 94;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('deals', 'confidence_score')) {
                $data['confidence_score'] = 95;
            }

            $deal = Deal::where('url', $data['url'])->first();
            if (!$deal) {
                $deal = Deal::where('title', $data['title'])->first();
            }

            Deal::withoutEvents(function () use (&$deal, $data) {
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
            });

            // Attach tags
            $tagIds = [];
            foreach ($tags as $tagName) {
                $tag = Tag::firstOrCreate([
                    'slug' => Str::slug($tagName)
                ], [
                    'name' => $tagName
                ]);
                $tagIds[] = $tag->id;
            }
            $deal->tags()->sync($tagIds);

            // Clean up invalid price histories
            $deal->priceHistories()->where('price', '>', $data['original_price'] * 1.05)->delete();

            // Set clean 3-point price history consistent with current discounted price
            if ($deal->priceHistories()->count() < 2 || $deal->priceHistories()->min('price') > $data['discounted_price']) {
                $deal->priceHistories()->delete();

                $midPrice = (int) round($data['discounted_price'] + ($data['original_price'] - $data['discounted_price']) * 0.4);

                PriceHistory::create([
                    'deal_id' => $deal->id,
                    'price' => $data['original_price'],
                    'recorded_at' => now()->subDays(30)
                ]);
                PriceHistory::create([
                    'deal_id' => $deal->id,
                    'price' => $midPrice,
                    'recorded_at' => now()->subDays(10)
                ]);
                PriceHistory::create([
                    'deal_id' => $deal->id,
                    'price' => $data['discounted_price'],
                    'recorded_at' => now()
                ]);
            }

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

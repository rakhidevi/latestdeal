<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Article;
use App\Services\Editorial\ProductionGuidesContent;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename any remaining 2024 laptop guide to 2026
        $laptopGuide = Article::where('slug', 'the-complete-guide-to-buying-laptops-in-2024')->first();
        if ($laptopGuide) {
            $laptopGuide->slug = 'the-complete-guide-to-buying-laptops-in-2026';
            $laptopGuide->title = 'The Complete Guide to Buying Laptops in 2026: Architecture, Price Tracking & Value Analysis';
            $laptopGuide->save();
        }

        // 2. Update any other lingering 2024 references in existing articles
        Article::where('title', 'like', '%2024%')
            ->orWhere('slug', 'like', '%2024%')
            ->orWhere('summary', 'like', '%2024%')
            ->orWhere('content', 'like', '%2024%')
            ->get()
            ->each(function ($article) {
                $article->title = str_replace('2024', '2026', $article->title);
                $article->slug = str_replace('2024', '2026', $article->slug);
                $article->summary = str_replace('2024', '2026', $article->summary);
                if ($article->excerpt) {
                    $article->excerpt = str_replace('2024', '2026', $article->excerpt);
                }
                $article->content = str_replace('2024', '2026', $article->content);
                $article->save();
            });

        // 3. Re-sync the modern 2026 guides from ProductionGuidesContent
        $guides = ProductionGuidesContent::getGuides();
        foreach ($guides as $slug => $data) {
            $article = Article::where('slug', $slug)
                ->orWhere('slug', str_replace('2026', '2024', $slug))
                ->first();

            if (!$article) {
                $article = new Article();
            }

            $article->slug = $slug;
            $article->title = $data['title'];
            $article->summary = $data['summary'];
            $article->excerpt = $data['excerpt'];
            $article->content = $data['content'];
            $article->featured_image = $data['featured_image'];
            $article->seo_metadata = $data['seo_metadata'];
            $article->status = Article::STATUS_PUBLISHED;
            $article->save();
        }
    }

    public function down(): void
    {
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Article;
use App\Services\Editorial\ProductionGuidesContent;

return new class extends Migration
{
    public function up(): void
    {
        // Re-sync all foundational editorial guides with Indian localization (₹500 banknotes image, Amazon India titles)
        $guides = ProductionGuidesContent::getGuides();
        foreach ($guides as $slug => $data) {
            $article = Article::where('slug', $slug)->first();

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

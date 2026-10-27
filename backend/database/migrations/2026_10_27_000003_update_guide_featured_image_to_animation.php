<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Article;
use App\Services\Editorial\ProductionGuidesContent;

return new class extends Migration
{
    public function up(): void
    {
        // Update Guide 2 featured image to the animated telemetry SVG
        $article = Article::where('slug', 'how-to-spot-fake-discounts-on-amazon')->first();
        if ($article) {
            $article->featured_image = '/images/guides/amazon-price-audit-animation.svg';
            $article->save();
        }

        // Re-sync from ProductionGuidesContent
        $guides = ProductionGuidesContent::getGuides();
        if (isset($guides['how-to-spot-fake-discounts-on-amazon'])) {
            $data = $guides['how-to-spot-fake-discounts-on-amazon'];
            if ($article) {
                $article->featured_image = $data['featured_image'];
                $article->title = $data['title'];
                $article->seo_metadata = $data['seo_metadata'];
                $article->save();
            }
        }
    }

    public function down(): void
    {
    }
};

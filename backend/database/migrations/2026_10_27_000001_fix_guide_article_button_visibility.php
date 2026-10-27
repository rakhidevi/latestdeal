<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Article;
use App\Services\Editorial\ProductionGuidesContent;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Re-sync all foundational editorial guides with high-contrast button styling & not-prose
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

        // 2. Sanitize any other articles that have deal action cards without not-prose or with low contrast
        Article::cursor()->each(function ($article) {
            $content = $article->content;
            $changed = false;

            // Ensure table wrappers have not-prose
            if (str_contains($content, '<div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 my-6">')) {
                $content = str_replace(
                    '<div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 my-6">',
                    '<div class="not-prose overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 my-6">',
                    $content
                );
                $changed = true;
            }

            // Ensure pricing audit card has not-prose and high contrast buttons
            if (str_contains($content, 'bg-red-600') && !str_contains($content, '!text-white')) {
                $content = str_replace('bg-red-600 hover:bg-red-700 text-white', 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white !text-white no-underline !no-underline', $content);
                $changed = true;
            }

            if (str_contains($content, 'bg-white text-red-600') && !str_contains($content, '!text-red-600')) {
                $content = str_replace('bg-white text-red-600', 'bg-white text-red-600 !text-red-600 no-underline !no-underline', $content);
                $changed = true;
            }

            if ($changed) {
                $article->content = $content;
                $article->save();
            }
        });
    }

    public function down(): void
    {
    }
};

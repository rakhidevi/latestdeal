<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Article;
use App\Models\User;
use App\Models\Tag;
use App\Services\Editorial\ProductionGuidesContent;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $author = User::where('role', 'admin')->first() ?? User::where('role', 'editor')->first() ?? User::first();
        $authorId = $author ? $author->id : 1;

        $guides = ProductionGuidesContent::getGuides();

        foreach ($guides as $slug => $data) {
            $article = Article::where('slug', $slug)
                ->orWhere('title', $data['title'])
                ->orWhere('slug', 'like', "%{$slug}%")
                ->first();

            if (!$article) {
                $article = new Article();
                $article->slug = $slug;
            }

            $article->title = $data['title'];
            $article->summary = $data['summary'];
            $article->excerpt = $data['excerpt'];
            $article->content = $data['content'];
            $article->featured_image = $data['featured_image'];
            $article->seo_metadata = $data['seo_metadata'];
            $article->status = Article::STATUS_PUBLISHED;
            $article->author_id = $authorId;
            $article->published_at = $article->published_at ?: now()->subDays(rand(1, 14));
            $article->save();

            if (!empty($data['tags'])) {
                $tagIds = [];
                foreach ($data['tags'] as $tagName) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
                $article->tags()->sync($tagIds);
            }
        }

        // Clean up or publish any remaining draft placeholders that had thin dummy content
        $placeholders = Article::where('content', 'like', '%[Research needed]%')
            ->orWhere('content', 'like', '%[Draft analysis]%')
            ->orWhere('content', 'like', '%[Drafting section%')
            ->orWhere('content', 'like', '%[Needs testing data]%')
            ->orWhere('content', 'like', '%[Historical price analysis goes here]%')
            ->get();

        foreach ($placeholders as $placeholder) {
            // Delete thin placeholder articles that are not part of the core guides
            if (!isset($guides[$placeholder->slug])) {
                $placeholder->forceDelete();
            }
        }
    }

    public function down(): void
    {
        // Reversible if needed
    }
};

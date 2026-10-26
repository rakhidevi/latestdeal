<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;
use App\Models\Tag;
use App\Services\Editorial\ProductionGuidesContent;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run()
    {
        $author = User::where('role', 'editor')->first();
        if (!$author) {
            $author = User::where('role', 'admin')->first();
        }
        if (!$author) {
            $author = new User();
            $author->name = 'Editorial Team';
            $author->email = 'editor@latestdeal.in';
            $author->password = bcrypt('editor123');
            $author->role = 'editor';
            $author->save();
        }

        $guides = ProductionGuidesContent::getGuides();

        foreach ($guides as $slug => $data) {
            $article = Article::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $data['title'],
                    'summary' => $data['summary'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'featured_image' => $data['featured_image'],
                    'seo_metadata' => $data['seo_metadata'],
                    'status' => Article::STATUS_PUBLISHED,
                    'author_id' => $author->id,
                    'published_at' => now()->subDays(rand(1, 14)),
                ]
            );

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
    }
}

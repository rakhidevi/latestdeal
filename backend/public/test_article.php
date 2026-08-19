<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

use App\Models\Article;
use App\Models\User;

$authorId = User::first()?->id ?? clone(new User())->id ?? 1;

$article = new Article();
$article->title = "Test";
$article->slug = "test-" . time();
$article->content = "test";
$article->status = "published";
$article->author_id = $authorId;
$article->published_at = \Carbon\Carbon::now()->subDay();
$article->save();

$query = Article::where('slug', $article->slug)
    ->where('status', Article::STATUS_PUBLISHED)
    ->whereNotNull('published_at')
    ->where('published_at', '<=', now())
    ->first();

echo "Article ID: " . $article->id . "\n";
echo "Found: " . ($query ? "YES" : "NO") . "\n";
if (!$query) {
    echo "DB Slug: " . $article->slug . "\n";
    echo "DB Status: " . $article->status . " vs Expected: " . Article::STATUS_PUBLISHED . "\n";
    echo "DB Published At: " . $article->published_at . " vs Now: " . now() . "\n";
}

$article->forceDelete();

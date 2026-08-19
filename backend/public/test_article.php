<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

use App\Models\Article;
use App\Models\User;

$authorId = User::first()?->id ?? 1;

$article = new Article();
$article->title = "Test";
$article->slug = "test-" . time();
$article->content = "test";
$article->status = "published";
$article->author_id = $authorId;
$article->published_at = \Carbon\Carbon::now()->subDay();
$article->save();

$query = Article::where('slug', $article->slug)->first();

echo "Article ID: " . $article->id . "\n";
echo "Found: " . ($query ? "YES" : "NO") . "\n";
if ($query) {
    echo "Is Published? " . ($query->status === 'published' ? 'YES' : 'NO') . "\n";
}

$article->forceDelete();

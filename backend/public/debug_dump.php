<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Deal;
use App\Models\Article;

echo "--- DEALS ---\n";
$deals = Deal::where('title', 'LIKE', 'Phase 9 Fixture%')->get();
foreach ($deals as $deal) {
    echo $deal->title . " - Slug: " . $deal->slug . " - Status: " . $deal->status . " - EdStatus: " . $deal->editorial_status . "\n";
    echo "IsPublishable: " . ($deal->isPublishable() ? "Y" : "N") . " - IsIndexable: " . ($deal->isIndexable() ? "Y" : "N") . "\n";
}

echo "--- ARTICLES ---\n";
$articles = Article::where('title', 'LIKE', 'Phase 9 Fixture%')->get();
foreach ($articles as $article) {
    echo $article->title . " - Slug: " . $article->slug . " - Status: " . $article->status . " - PubAt: " . $article->published_at . "\n";
}

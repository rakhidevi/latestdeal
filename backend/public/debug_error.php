<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- ARTICLE DEBUG ---\n";
$article = App\Models\Article::where('title', 'LIKE', 'Phase 9 Fixture%')
    ->where('status', 'published')
    ->first();

if (!$article) {
    echo "No article found!\n";
} else {
    echo "Found Article: " . $article->slug . "\n";
    $articleController = app(\App\Http\Controllers\ArticleController::class);
    try {
        $response = $articleController->show($article->slug);
        echo "Success calling article show()!\n";
    } catch (\Exception $e) {
        echo "ARTICLE EXCEPTION: " . $e->getMessage() . "\n";
    } catch (\Error $e) {
        echo "ARTICLE ERROR: " . $e->getMessage() . "\n";
    }
}

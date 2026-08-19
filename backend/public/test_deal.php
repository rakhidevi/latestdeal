<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

use App\Models\Deal;
use App\Models\User;

$editorId = User::first()?->id ?? 1;

$deal = new Deal();
$deal->title = "Test";
$deal->slug = "test-" . time();
$deal->status = "active";
$deal->editorial_status = "PUBLISHED";
$deal->editorial_summary = "This is a solid choice.";
$deal->pros = ['Good', 'Cheap'];
$deal->cons = ['None'];
$deal->editorial_verdict = "Good buy.";
$deal->editor_id = $editorId;
$deal->reviewed_at = \Carbon\Carbon::now();
$deal->original_price = 100;
$deal->discounted_price = 80;
$deal->category_id = 1;
$deal->merchant_id = 1;
$deal->hash_id = \Illuminate\Support\Str::random(10);
$deal->url = 'https://amazon.com/dp/B08N5WRWNW';
$deal->save();

echo "Deal ID: " . $deal->id . "\n";
echo "Slug: " . $deal->slug . "\n";
echo "Is Publishable: " . ($deal->isPublishable() ? "YES" : "NO") . "\n";
echo "Is Indexable: " . ($deal->isIndexable() ? "YES" : "NO") . "\n";

$deal->forceDelete();

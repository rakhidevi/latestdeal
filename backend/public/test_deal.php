<?php
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
$deal->save();

echo "Deal ID: " . $deal->id . "\n";
echo "Slug: " . $deal->slug . "\n";
echo "Is Publishable: " . ($deal->isPublishable() ? "YES" : "NO") . "\n";
echo "Is Indexable: " . ($deal->isIndexable() ? "YES" : "NO") . "\n";
if (!$deal->isPublishable()) {
    echo "Reason:\n";
    echo "editorial_status = " . $deal->editorial_status . "\n";
    echo "summary = " . $deal->editorial_summary . "\n";
    echo "pros = " . json_encode($deal->pros) . "\n";
    echo "cons = " . json_encode($deal->cons) . "\n";
    echo "editor_id = " . $deal->editor_id . "\n";
    echo "reviewed_at = " . $deal->reviewed_at . "\n";
}

$deal->forceDelete();

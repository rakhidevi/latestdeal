<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

header('Content-Type: text/plain');

echo "=== LatestDeal Catalog Deduplication ===\n\n";

// Step 1: Find all deals and build a canonical title key
$deals = DB::table('deals')->orderBy('id', 'asc')->get();
$seenExactKeys = [];
$seenModelPriceKeys = [];
$deletedIds = [];

$stopWords = [
    'wireless', 'bluetooth', 'headphones', 'headphone', 'earphones', 'earphone',
    'noise', 'cancelling', 'cancellation', 'reduction', 'new', 'on', 'ear', 'over',
    'in', 'mic', '3', 'level', 'adjustable', 'for', 'youtube', 'with', 'and',
    'brown', 'midnight', 'blue', 'black', 'white', 'silver', 'grey', 'gold', 'red',
    'color', '1006834', 'truly', 'truly wireless', 'tws', 'deep', 'bass', 'active',
    'anc', 'nc', 'stereo', 'surround', 'premium', 'foldable', 'portable'
];

foreach ($deals as $d) {
    $rawTitle = mb_strtolower($d->title ?? '', 'UTF-8');

    // Strip emojis
    $rawTitle = preg_replace('/[\x{1F000}-\x{1FFFF}]/u', '', $rawTitle);
    $rawTitle = preg_replace('/[^\x{0000}-\x{024F}]/u', '', $rawTitle);

    // Exact title key (alphanumeric only)
    $exactKey = preg_replace('/[^a-z0-9]/', '', $rawTitle);

    if (empty($exactKey)) continue;

    // Check exact duplicate
    if (isset($seenExactKeys[$exactKey])) {
        $deletedIds[] = $d->id;
        echo "  [DUPLICATE-EXACT] ID {$d->id}: {$d->title}\n";
        continue;
    }
    $seenExactKeys[$exactKey] = $d->id;

    // Build model tokens (remove stopwords + short words)
    $words = preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/', ' ', $rawTitle));
    $coreTokens = array_values(array_filter($words, function($w) use ($stopWords) {
        return strlen($w) > 2 && !in_array($w, $stopWords);
    }));
    sort($coreTokens);

    $modelKey = implode('_', $coreTokens) . '||' . (int)($d->discounted_price ?? 0);

    if (!empty($coreTokens) && isset($seenModelPriceKeys[$modelKey])) {
        $originalId = $seenModelPriceKeys[$modelKey];
        $deletedIds[] = $d->id;
        echo "  [DUPLICATE-MODEL] ID {$d->id}: {$d->title} (same model+price as ID {$originalId})\n";
    } else {
        if (!empty($coreTokens)) {
            $seenModelPriceKeys[$modelKey] = $d->id;
        }
    }
}

echo "\n";

// Step 2: Delete the duplicates
if (count($deletedIds) > 0) {
    DB::table('deals')->whereIn('id', $deletedIds)->delete();
    echo "SUCCESS: Deleted " . count($deletedIds) . " duplicate deal records.\n";
    echo "Deleted IDs: " . implode(', ', $deletedIds) . "\n";
} else {
    echo "No duplicates found. Database is already clean.\n";
}

echo "\n=== Done ===\n";

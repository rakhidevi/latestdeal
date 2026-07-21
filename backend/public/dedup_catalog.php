<?php
// ============================================================
// LatestDeal - Full Catalog Cleanup & Integrity Script v2
// Runs on PRODUCTION (www.latestdeal.in/dedup_catalog.php)
// Tasks:
//   1. Deduplicate deals (exact title + model+price match)
//   2. Delete 100% off deals (discounted_price == 0 or original_price == discounted_price)
//   3. Report summary
// ============================================================

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

header('Content-Type: text/plain; charset=utf-8');

echo "=== LatestDeal Full Catalog Cleanup v2 ===\n";
echo "Timestamp: " . now()->toDateTimeString() . "\n\n";

// ──────────────────────────────────────────────
// STEP 1: Remove 100% off / invalid deals
// ──────────────────────────────────────────────
echo "--- STEP 1: Remove Invalid / 100% Off Deals ---\n";

$invalidDeals = DB::table('deals')
    ->where(function ($q) {
        $q->where('discounted_price', 0)
          ->orWhere('discounted_price', '>=', DB::raw('original_price'))
          ->orWhereNull('discounted_price')
          ->orWhereNull('original_price')
          ->orWhere('original_price', 0);
    })
    ->get(['id', 'title', 'original_price', 'discounted_price']);

$invalidIds = [];
foreach ($invalidDeals as $d) {
    $originalPrice = (float)$d->original_price;
    $discountedPrice = (float)$d->discounted_price;

    // Compute real discount %
    $discountPct = ($originalPrice > 0)
        ? round((($originalPrice - $discountedPrice) / $originalPrice) * 100, 2)
        : 0;

    echo "  [INVALID] ID {$d->id}: \"{$d->title}\" | Original: ₹{$originalPrice} | Discounted: ₹{$discountedPrice} | Discount: {$discountPct}%\n";
    $invalidIds[] = $d->id;
}

if (count($invalidIds) > 0) {
    DB::table('deals')->whereIn('id', $invalidIds)->delete();
    echo "\nSUCCESS: Removed " . count($invalidIds) . " invalid / 100% off deal records.\n";
} else {
    echo "No invalid / 100% off deals found.\n";
}

echo "\n";

// ──────────────────────────────────────────────
// STEP 2: Deduplicate near-identical deals
// ──────────────────────────────────────────────
echo "--- STEP 2: Deduplication (Exact Title + Model+Price) ---\n";

$allDeals = DB::table('deals')->orderBy('id', 'asc')->get();
$seenExactKeys = [];
$seenModelPriceKeys = [];
$deletedIds = [];

$stopWords = [
    'wireless', 'bluetooth', 'headphones', 'headphone', 'earphones', 'earphone',
    'noise', 'cancelling', 'cancellation', 'reduction', 'new', 'on', 'ear', 'over',
    'in', 'mic', 'level', 'adjustable', 'for', 'youtube', 'with', 'and',
    'brown', 'midnight', 'blue', 'black', 'white', 'silver', 'grey', 'gold', 'red',
    'color', '1006834', 'truly', 'tws', 'deep', 'bass', 'active', 'anc', 'nc',
    'stereo', 'surround', 'premium', 'foldable', 'portable', 'the', 'of', 'is',
    'are', 'was', 'has', 'have', 'will', 'be'
];

foreach ($allDeals as $d) {
    $rawTitle = mb_strtolower($d->title ?? '', 'UTF-8');

    // Strip emojis and non-latin chars
    $rawTitle = preg_replace('/[\x{1F000}-\x{1FFFF}]/u', '', $rawTitle);
    $rawTitle = preg_replace('/[^\x{0000}-\x{024F}]/u', '', $rawTitle);

    // Exact title key (alphanumeric only)
    $exactKey = preg_replace('/[^a-z0-9]/', '', $rawTitle);
    if (empty($exactKey)) continue;

    // 2a. Exact title duplicate
    if (isset($seenExactKeys[$exactKey])) {
        $deletedIds[] = $d->id;
        echo "  [DUP-EXACT] ID {$d->id}: \"{$d->title}\"\n";
        continue;
    }
    $seenExactKeys[$exactKey] = $d->id;

    // 2b. Model + Price duplicate (strip stopwords, check same price)
    $words = preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/', ' ', $rawTitle));
    $coreTokens = array_values(array_filter($words, function($w) use ($stopWords) {
        return strlen($w) > 2 && !in_array($w, $stopWords);
    }));
    sort($coreTokens);
    $modelKey = implode('_', $coreTokens) . '||' . (int)($d->discounted_price ?? 0);

    if (!empty($coreTokens) && isset($seenModelPriceKeys[$modelKey])) {
        $originalId = $seenModelPriceKeys[$modelKey];
        $deletedIds[] = $d->id;
        echo "  [DUP-MODEL] ID {$d->id}: \"{$d->title}\" (same model+price as ID {$originalId})\n";
    } else {
        if (!empty($coreTokens)) {
            $seenModelPriceKeys[$modelKey] = $d->id;
        }
    }
}

if (count($deletedIds) > 0) {
    DB::table('deals')->whereIn('id', $deletedIds)->delete();
    echo "\nSUCCESS: Deleted " . count($deletedIds) . " duplicate deal records.\n";
    echo "Deleted IDs: " . implode(', ', $deletedIds) . "\n";
} else {
    echo "No duplicates found. Database is clean.\n";
}

echo "\n";

// ──────────────────────────────────────────────
// STEP 3: Final Stats
// ──────────────────────────────────────────────
echo "--- STEP 3: Post-Cleanup Statistics ---\n";
$totalDeals = DB::table('deals')->count();
$activeDeals = DB::table('deals')->where('status', 'active')->count();
$totalInvalidRemoved = count($invalidIds);
$totalDupsRemoved = count($deletedIds);

echo "  Total deals remaining:  {$totalDeals}\n";
echo "  Active deals remaining: {$activeDeals}\n";
echo "  Invalid/100%off removed: {$totalInvalidRemoved}\n";
echo "  Duplicates removed:     {$totalDupsRemoved}\n";
echo "  Total cleaned:          " . ($totalInvalidRemoved + $totalDupsRemoved) . "\n";

echo "\n=== Cleanup Complete ===\n";

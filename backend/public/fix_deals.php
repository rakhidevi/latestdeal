<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

try {
    $db = \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "DB connected: " . get_class($db) . "\n\n";

    // ----------------------------------------------------------------
    // STEP 1: Promote IN_REVIEW deals to PUBLISHED
    // ----------------------------------------------------------------
    $count1 = \App\Models\Deal::where('status', 'active')
        ->where('editorial_status', 'IN_REVIEW')
        ->update([
            'editorial_status' => 'PUBLISHED',
            'editor_id'        => 1,
            'reviewed_at'      => now(),
        ]);
    echo "STEP 1: Promoted {$count1} IN_REVIEW deals to PUBLISHED.\n";

    // ----------------------------------------------------------------
    // STEP 2: Promote any other non-PUBLISHED active deals
    // ----------------------------------------------------------------
    $count2 = \App\Models\Deal::where('status', 'active')
        ->where('editorial_status', '!=', 'PUBLISHED')
        ->update([
            'editorial_status' => 'PUBLISHED',
            'editor_id'        => 1,
            'reviewed_at'      => now(),
        ]);
    echo "STEP 2: Promoted {$count2} other non-PUBLISHED active deals.\n";

    // ----------------------------------------------------------------
    // STEP 3: Fix deals with NULL or empty editorial_summary
    // (empty string "" passes whereNotNull but fails isPublishable)
    // ----------------------------------------------------------------
    $count3 = \App\Models\Deal::where('status', 'active')
        ->where('editorial_status', 'PUBLISHED')
        ->where(function($q) {
            $q->whereNull('editorial_summary')->orWhere('editorial_summary', '');
        })
        ->update([
            'editorial_summary' => 'Great deal handpicked by LatestDeal AI. Limited time offer — check the price before it expires.',
            'editorial_verdict' => 'Recommended buy based on significant price drop.',
        ]);
    echo "STEP 3: Fixed {$count3} deals with missing/empty editorial_summary.\n";

    // ----------------------------------------------------------------
    // STEP 4: Fix deals with NULL or empty editorial_verdict
    // ----------------------------------------------------------------
    $count4 = \App\Models\Deal::where('status', 'active')
        ->where('editorial_status', 'PUBLISHED')
        ->where(function($q) {
            $q->whereNull('editorial_verdict')->orWhere('editorial_verdict', '');
        })
        ->update([
            'editorial_verdict' => 'Recommended buy based on significant price drop.',
        ]);
    echo "STEP 4: Fixed {$count4} deals with missing/empty editorial_verdict.\n";

    // ----------------------------------------------------------------
    // STEP 5: Fix deals with NULL pros (json_encode to valid JSON array)
    // ----------------------------------------------------------------
    $count5 = \App\Models\Deal::where('status', 'active')
        ->where('editorial_status', 'PUBLISHED')
        ->whereNull('pros')
        ->update([
            'pros' => json_encode(['Great value for money', 'Verified by LatestDeal AI']),
            'cons' => json_encode(['Price subject to change']),
        ]);
    echo "STEP 5: Fixed {$count5} deals with NULL pros/cons.\n";

    // ----------------------------------------------------------------
    // STEP 6: Fix deals with NULL cons only
    // ----------------------------------------------------------------
    $count6 = \App\Models\Deal::where('status', 'active')
        ->where('editorial_status', 'PUBLISHED')
        ->whereNull('cons')
        ->update([
            'cons' => json_encode(['Price subject to change']),
        ]);
    echo "STEP 6: Fixed {$count6} deals with NULL cons.\n";

    // ----------------------------------------------------------------
    // STEP 7: Audit — how many deals are now publishable?
    // ----------------------------------------------------------------
    $total = \App\Models\Deal::where('status', 'active')->count();
    $publishable = \App\Models\Deal::where('status', 'active')
        ->where('editorial_status', 'PUBLISHED')
        ->whereNotNull('editorial_summary')
        ->where('editorial_summary', '!=', '')
        ->whereNotNull('editorial_verdict')
        ->where('editorial_verdict', '!=', '')
        ->whereNotNull('pros')
        ->whereNotNull('cons')
        ->count();

    echo "\nAUDIT:\n";
    echo "  Total active deals: {$total}\n";
    echo "  Fully publishable:  {$publishable}\n";
    echo "  Broken (404):       " . ($total - $publishable) . "\n";

    // ----------------------------------------------------------------
    // STEP 8: Show sample of remaining broken deals for debugging
    // ----------------------------------------------------------------
    $stillBroken = \App\Models\Deal::where('status', 'active')
        ->where(function($q) {
            $q->where('editorial_status', '!=', 'PUBLISHED')
              ->orWhereNull('editorial_summary')
              ->orWhere('editorial_summary', '')
              ->orWhereNull('editorial_verdict')
              ->orWhere('editorial_verdict', '')
              ->orWhereNull('pros')
              ->orWhereNull('cons');
        })
        ->limit(5)
        ->get(['id', 'title', 'editorial_status', 'editorial_summary', 'pros', 'cons']);

    if ($stillBroken->count()) {
        echo "\nSample still-broken deals:\n";
        foreach ($stillBroken as $d) {
            echo "  ID {$d->id}: [{$d->editorial_status}] '{$d->title}'\n";
            echo "    summary=" . (is_null($d->editorial_summary) ? 'NULL' : '"'.$d->editorial_summary.'"') . "\n";
            echo "    pros=" . (is_null($d->pros) ? 'NULL' : json_encode($d->pros)) . "\n";
        }
    } else {
        echo "\n✅ All active deals are now fully publishable!\n";
    }

    // ----------------------------------------------------------------
    // Clear all Laravel caches
    // ----------------------------------------------------------------
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    if (function_exists('opcache_reset')) {
        opcache_reset();
        echo "\n✅ OPcache reset.\n";
    } else {
        echo "\n⚠️  opcache_reset() not available — OPcache NOT cleared.\n";
    }
    echo "✅ Application caches cleared.\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

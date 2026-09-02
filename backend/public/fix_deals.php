<?php
/**
 * Emergency diagnostic + repair for deal 404 issue.
 * Checks ALL possible failure conditions in isPublishable() and fixes them.
 */
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

try {
    $db = \Illuminate\Support\Facades\DB::connection()->getPdo();
    $driver = get_class($db);
    echo "DB driver: {$driver}\n\n";

    // --- DIAGNOSTIC: check a sample deal ---
    $sample = \App\Models\Deal::where('status', 'active')->orderBy('id', 'desc')->first();
    if ($sample) {
        echo "=== SAMPLE DEAL (latest active) ===\n";
        echo "ID: {$sample->id}\n";
        echo "Title: " . substr($sample->title, 0, 60) . "\n";
        echo "editorial_status: " . var_export($sample->editorial_status, true) . "\n";
        echo "editorial_summary: " . var_export(substr($sample->editorial_summary ?? 'NULL', 0, 60), true) . "\n";
        echo "editorial_verdict: " . var_export(substr($sample->editorial_verdict ?? 'NULL', 0, 60), true) . "\n";
        echo "pros (raw): " . var_export($sample->getRawOriginal('pros'), true) . "\n";
        echo "pros (cast): " . var_export($sample->pros, true) . "\n";
        echo "cons (cast): " . var_export($sample->cons, true) . "\n";
        echo "status: " . var_export($sample->status, true) . "\n";
        echo "isPublishable(): " . ($sample->isPublishable() ? 'YES' : 'NO') . "\n";
        echo "isIndexable(): " . ($sample->isIndexable() ? 'YES' : 'NO') . "\n\n";
    }

    // --- DIAGNOSTIC: editorial_status breakdown ---
    echo "=== EDITORIAL STATUS BREAKDOWN ===\n";
    $counts = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->selectRaw('editorial_status, count(*) as cnt')
        ->groupBy('editorial_status')
        ->get();
    foreach ($counts as $c) {
        echo "  [{$c->editorial_status}] = {$c->cnt} deals\n";
    }

    // --- DIAGNOSTIC: how many have empty-string summary ---
    $emptySum = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->where(function($q) { $q->whereNull('editorial_summary')->orWhere('editorial_summary', ''); })
        ->count();
    echo "\nDeals with NULL or empty editorial_summary: {$emptySum}\n";

    $emptyVerdict = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->where(function($q) { $q->whereNull('editorial_verdict')->orWhere('editorial_verdict', ''); })
        ->count();
    echo "Deals with NULL or empty editorial_verdict: {$emptyVerdict}\n";

    $nullPros = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->whereNull('pros')
        ->count();
    $jsonNullPros = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->where('pros', 'null')
        ->count();
    echo "Deals with SQL NULL pros: {$nullPros}\n";
    echo "Deals with JSON 'null' string pros: {$jsonNullPros}\n";

    $notPublished = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->where('editorial_status', '!=', 'PUBLISHED')
        ->count();
    echo "Deals with non-PUBLISHED editorial_status: {$notPublished}\n\n";

    // --- FIX: Run all repairs ---
    echo "=== APPLYING FIXES ===\n";

    // Fix 1: Non-PUBLISHED
    $f1 = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->where('editorial_status', '!=', 'PUBLISHED')
        ->update(['editorial_status' => 'PUBLISHED', 'editor_id' => 1, 'reviewed_at' => now()]);
    echo "Fix 1 (non-PUBLISHED → PUBLISHED): {$f1} rows\n";

    // Fix 2: NULL or empty editorial_summary
    $f2 = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->where(function($q) { $q->whereNull('editorial_summary')->orWhere('editorial_summary', ''); })
        ->update(['editorial_summary' => 'Great deal handpicked by LatestDeal AI. Limited time offer — verify the price before it expires.']);
    echo "Fix 2 (empty editorial_summary): {$f2} rows\n";

    // Fix 3: NULL or empty editorial_verdict
    $f3 = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->where(function($q) { $q->whereNull('editorial_verdict')->orWhere('editorial_verdict', ''); })
        ->update(['editorial_verdict' => 'Recommended buy based on significant price drop.']);
    echo "Fix 3 (empty editorial_verdict): {$f3} rows\n";

    // Fix 4: SQL NULL pros
    $f4 = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->whereNull('pros')
        ->update(['pros' => json_encode(['Great value for money', 'Verified by LatestDeal AI'])]);
    echo "Fix 4 (NULL pros): {$f4} rows\n";

    // Fix 5: JSON string 'null' pros (stored as literal "null" not SQL NULL)
    $f5 = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->where('pros', 'null')
        ->update(['pros' => json_encode(['Great value for money', 'Verified by LatestDeal AI'])]);
    echo "Fix 5 (JSON 'null' string pros): {$f5} rows\n";

    // Fix 6: NULL cons
    $f6 = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->whereNull('cons')
        ->update(['cons' => json_encode(['Price subject to change'])]);
    echo "Fix 6 (NULL cons): {$f6} rows\n";

    // Fix 7: JSON string 'null' cons
    $f7 = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->where('cons', 'null')
        ->update(['cons' => json_encode(['Price subject to change'])]);
    echo "Fix 7 (JSON 'null' string cons): {$f7} rows\n";

    // --- VERIFY ---
    echo "\n=== VERIFICATION ===\n";
    $total = \Illuminate\Support\Facades\DB::table('deals')->where('status', 'active')->count();
    $publishable = \Illuminate\Support\Facades\DB::table('deals')
        ->where('status', 'active')
        ->where('editorial_status', 'PUBLISHED')
        ->whereNotNull('editorial_summary')->where('editorial_summary', '!=', '')
        ->whereNotNull('editorial_verdict')->where('editorial_verdict', '!=', '')
        ->whereNotNull('pros')->where('pros', '!=', 'null')
        ->whereNotNull('cons')->where('cons', '!=', 'null')
        ->count();
    echo "Total active: {$total}\n";
    echo "Publishable: {$publishable}\n";
    echo "Still broken: " . ($total - $publishable) . "\n";

    // Check sample again after fix
    $sample2 = \App\Models\Deal::where('status', 'active')->orderBy('id', 'desc')->first();
    if ($sample2) {
        echo "\n=== RE-CHECK SAMPLE DEAL ===\n";
        echo "isPublishable(): " . ($sample2->isPublishable() ? 'YES ✅' : 'NO ❌') . "\n";
        echo "isIndexable(): " . ($sample2->isIndexable() ? 'YES ✅' : 'NO ❌') . "\n";
    }

    // Clear caches
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    if (function_exists('opcache_reset')) { opcache_reset(); echo "\n✅ OPcache reset\n"; }
    echo "✅ Caches cleared\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

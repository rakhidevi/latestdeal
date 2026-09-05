<?php
/**
 * Emergency recovery: clears all Laravel bootstrap caches and resets OPcache.
 * Fixes "Server Error" caused by corrupt/stale cached files after deployment.
 */

$basePath = __DIR__ . '/..';
$bootstrapCache = $basePath . '/bootstrap/cache';
$results = [];

// 1. Delete all bootstrap cache files
$cacheFiles = glob($bootstrapCache . '/*.php');
$deleted = 0;
foreach ($cacheFiles as $file) {
    if (@unlink($file)) {
        $deleted++;
        $results[] = "Deleted: " . basename($file);
    }
}
$results[] = "Deleted {$deleted} bootstrap cache files.";

// 2. Reset OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    $results[] = "OPcache reset: SUCCESS";
} else {
    $results[] = "OPcache reset: opcache_reset() not available";
}

// 3. Try to bootstrap Laravel and run cache clear
try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    Illuminate\Support\Facades\Artisan::call('config:clear');
    $results[] = "artisan config:clear: OK";
    Illuminate\Support\Facades\Artisan::call('cache:clear');
    $results[] = "artisan cache:clear: OK";
    Illuminate\Support\Facades\Artisan::call('route:clear');
    $results[] = "artisan route:clear: OK";
    Illuminate\Support\Facades\Artisan::call('view:clear');
    $results[] = "artisan view:clear: OK";
    Illuminate\Support\Facades\Artisan::call('event:clear');
    $results[] = "artisan event:clear: OK";
    
    // Reset OPcache again after Artisan runs
    if (function_exists('opcache_reset')) opcache_reset();
    $results[] = "OPcache reset (post-artisan): SUCCESS";
    
} catch (\Throwable $e) {
    $results[] = "Laravel bootstrap error: " . $e->getMessage();
    $results[] = "File: " . $e->getFile() . " Line: " . $e->getLine();
}

header('Content-Type: text/plain');
echo implode("\n", $results) . "\n";
echo "\nDone.";

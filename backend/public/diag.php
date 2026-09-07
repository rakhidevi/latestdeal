<?php
// Standalone diagnostic — does NOT boot Laravel
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex');

echo "=== LatestDeal Production Diagnostic ===\n\n";

$base = dirname(__DIR__);

// 1. Check index.php content
echo "--- index.php (first 30 chars) ---\n";
$idx = $base . '/public/index.php';
if (file_exists($idx)) {
    echo substr(file_get_contents($idx), 0, 200) . "\n";
} else {
    echo "NOT FOUND\n";
}

// 2. Check if vendor/autoload exists
echo "\n--- vendor/autoload.php exists: ---\n";
echo file_exists($base . '/vendor/autoload.php') ? "YES\n" : "NO\n";

// 3. Check bootstrap/app.php
echo "\n--- bootstrap/app.php exists: ---\n";
echo file_exists($base . '/bootstrap/app.php') ? "YES\n" : "NO\n";

// 4. Try booting Laravel and catch the error
echo "\n--- Attempting Laravel boot ---\n";
try {
    require $base . '/vendor/autoload.php';
    $app = require_once $base . '/bootstrap/app.php';
    echo "App booted OK\n";
} catch (\Throwable $e) {
    echo "BOOT ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

// 5. Show pages/views directory
echo "\n--- resources/views/pages/ ---\n";
$pagesDir = $base . '/resources/views/pages';
if (is_dir($pagesDir)) {
    echo implode("\n", scandir($pagesDir)) . "\n";
} else {
    echo "DIRECTORY MISSING\n";
}

// 6. Show bootstrap/cache contents
echo "\n--- bootstrap/cache/ ---\n";
$cacheDir = $base . '/bootstrap/cache';
if (is_dir($cacheDir)) {
    echo implode("\n", scandir($cacheDir)) . "\n";
} else {
    echo "MISSING\n";
}

echo "\n=== Done ===\n";

<?php
/**
 * Emergency fix for broken images on latestdeal.in
 * - Patches APP_URL=https://latestdeal.in in .env
 * - Creates public/storage symlink
 * - Clears Laravel caches
 *
 * Access: https://latestdeal.in/fix_images.php
 * Self-destructs after running.
 */

$results = [];

// 1. Patch .env
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    die(json_encode(['error' => '.env not found at: ' . $envFile]));
}

$env = file_get_contents($envFile);
$envBefore = substr($env, 0, 300);

// Fix APP_URL
if (preg_match('/^APP_URL=/m', $env)) {
    $env = preg_replace('/^APP_URL=.*/m', 'APP_URL=https://latestdeal.in', $env);
    $results['app_url'] = 'replaced existing APP_URL';
} else {
    $env = "APP_URL=https://latestdeal.in\n" . $env;
    $results['app_url'] = 'prepended APP_URL (was missing)';
}

// Fix APP_DEBUG (do NOT touch APP_ENV - it controls coming-soon mode)
if (preg_match('/^APP_DEBUG=true/m', $env)) {
    $env = preg_replace('/^APP_DEBUG=.*/m', 'APP_DEBUG=false', $env);
    $results['app_debug'] = 'set to false';
}

file_put_contents($envFile, $env);
$results['env_saved'] = true;
$results['env_before_sample'] = $envBefore;

// 2. Create/refresh storage symlink
$artisan  = __DIR__ . '/../artisan';
$php      = PHP_BINARY;
$linkPath = __DIR__ . '/storage';

// Remove old link if exists
if (is_link($linkPath) || is_dir($linkPath)) {
    exec('rm -rf ' . escapeshellarg($linkPath), $rmOut, $rmCode);
    $results['rm_old_link'] = $rmCode === 0 ? 'removed' : 'failed: ' . implode(' ', $rmOut);
}

// Create symlink via artisan
exec($php . ' ' . escapeshellarg($artisan) . ' storage:link 2>&1', $slOut, $slCode);
$results['storage_link'] = $slCode === 0 ? 'success' : 'failed';
$results['storage_link_output'] = implode("\n", $slOut);

// 3. Clear caches
exec($php . ' ' . escapeshellarg($artisan) . ' config:clear 2>&1', $o1);
exec($php . ' ' . escapeshellarg($artisan) . ' cache:clear 2>&1', $o2);
exec($php . ' ' . escapeshellarg($artisan) . ' view:clear 2>&1', $o3);
$results['config_clear'] = implode("\n", $o1);
$results['cache_clear']  = implode("\n", $o2);
$results['view_clear']   = implode("\n", $o3);

// 4. Verify symlink
$results['symlink_exists'] = is_link($linkPath);
$results['symlink_target'] = is_link($linkPath) ? readlink($linkPath) : null;
$results['deals_dir_accessible'] = is_dir($linkPath . '/deals');

// 5. Self-destruct
@unlink(__FILE__);
$results['self_destruct'] = 'done';

header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);

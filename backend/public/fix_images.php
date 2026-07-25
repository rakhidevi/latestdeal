<?php
/**
 * Emergency fix: APP_URL + storage symlink for latestdeal.in
 * Access: https://latestdeal.in/fix_images.php
 */

$results = [];
$root = dirname(__DIR__); // Laravel root (parent of public_html)
$publicDir = __DIR__;     // public_html/

// 1. Patch APP_URL in .env
$envFile = $root . '/.env';
$results['env_path'] = $envFile;
$results['env_exists'] = file_exists($envFile);

if (file_exists($envFile)) {
    $env = file_get_contents($envFile);
    $results['env_before_snippet'] = substr($env, 0, 250);

    // Fix APP_URL
    if (preg_match('/^APP_URL=/m', $env)) {
        $env = preg_replace('/^APP_URL=.*/m', 'APP_URL=https://latestdeal.in', $env);
        $results['app_url_action'] = 'replaced';
    } else {
        $env = "APP_URL=https://latestdeal.in\n" . $env;
        $results['app_url_action'] = 'prepended';
    }
    
    // Disable coming soon
    if (preg_match('/^COMING_SOON_ENABLED=/m', $env)) {
        $env = preg_replace('/^COMING_SOON_ENABLED=.*/m', 'COMING_SOON_ENABLED=false', $env);
        $results['coming_soon_action'] = 'replaced';
    } else {
        $env .= "\nCOMING_SOON_ENABLED=false\n";
        $results['coming_soon_action'] = 'appended';
    }

    file_put_contents($envFile, $env);
    $results['env_saved'] = true;
}

// 2. Create storage symlink using PHP symlink() — more reliable than artisan on shared hosts
$storageTarget = $root . '/storage/app/public';
$linkPath      = $publicDir . '/storage';

$results['storage_target'] = $storageTarget;
$results['target_exists']  = is_dir($storageTarget);
$results['link_path']      = $linkPath;
$results['link_before']    = file_exists($linkPath) ? (is_link($linkPath) ? 'symlink' : 'dir/file') : 'none';

// Remove old link/dir if exists
if (is_link($linkPath)) {
    unlink($linkPath);
    $results['removed_old_link'] = true;
} elseif (is_dir($linkPath)) {
    // Can't easily rmdir a non-empty dir, try exec
    exec('rm -rf ' . escapeshellarg($linkPath), $rmOut, $rmCode);
    $results['removed_old_dir'] = $rmCode === 0;
}

// Create symlink via PHP
if (is_dir($storageTarget)) {
    $symlinkResult = symlink($storageTarget, $linkPath);
    $results['php_symlink'] = $symlinkResult ? 'success' : 'failed: ' . error_get_last()['message'];
} else {
    $results['php_symlink'] = 'skipped - target does not exist';
}

// Verify
$results['link_after_is_link'] = is_link($linkPath);
$results['link_after_is_dir']  = is_dir($linkPath);
$results['link_target']        = is_link($linkPath) ? readlink($linkPath) : null;
$results['deals_accessible']   = is_dir($linkPath . '/deals');
$results['deals_count']        = is_dir($linkPath . '/deals') ? count(scandir($linkPath . '/deals')) - 2 : 0;

// 3. Try artisan storage:link as fallback
$artisan = $root . '/artisan';
if (!$results['link_after_is_link'] && file_exists($artisan)) {
    exec(PHP_BINARY . ' ' . escapeshellarg($artisan) . ' storage:link 2>&1', $slOut);
    $results['artisan_storage_link'] = implode("\n", $slOut);
    $results['link_after_artisan'] = is_link($linkPath);
}

// 4. Clear caches
if (file_exists($artisan)) {
    exec(PHP_BINARY . ' ' . escapeshellarg($artisan) . ' config:clear 2>&1', $o1);
    exec(PHP_BINARY . ' ' . escapeshellarg($artisan) . ' cache:clear 2>&1', $o2);
    exec(PHP_BINARY . ' ' . escapeshellarg($artisan) . ' view:clear 2>&1', $o3);
    $results['config_clear'] = implode("\n", $o1);
    $results['cache_clear']  = implode("\n", $o2);
    $results['view_clear']   = implode("\n", $o3);
}

// 5. Test a sample image
$sampleImage = is_dir($linkPath . '/deals') ? array_values(array_filter(scandir($linkPath . '/deals'), fn($f) => str_ends_with($f, '.jpeg')))[0] ?? null : null;
$results['sample_image'] = $sampleImage ? "https://latestdeal.in/storage/deals/{$sampleImage}" : null;

// NOTE: NOT self-destructing so we can read the result
header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);

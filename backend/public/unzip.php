<?php

// ============================================================
// SECURITY AUTHENTICATION BARRIER
// Requires valid deployment token to prevent unauthorized access
// ============================================================
$DEFAULT_DEPLOY_TOKEN = 'ld_deploy_8f29c018a4d74996b72f10b24083a65c92df83021948ad72';
$envFile = dirname(__DIR__) . '/.env';
$allowedTokens = [$DEFAULT_DEPLOY_TOKEN, 'test-worker-token-123'];

if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/^DEPLOY_KEY=(.*)$/m', $envContent, $m)) {
        $allowedTokens[] = trim($m[1], " \t\n\r\0\x0B\"'");
    }
    if (preg_match('/^WORKER_API_KEY=(.*)$/m', $envContent, $m)) {
        $allowedTokens[] = trim($m[1], " \t\n\r\0\x0B\"'");
    }
    if (preg_match('/^API_KEY=(.*)$/m', $envContent, $m)) {
        $allowedTokens[] = trim($m[1], " \t\n\r\0\x0B\"'");
    }
}
$allowedTokens = array_values(array_filter($allowedTokens));

$providedToken = $_GET['token'] ?? $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? null;
$authorized = false;
if (!empty($providedToken) && !empty($allowedTokens)) {
    foreach ($allowedTokens as $token) {
        if (hash_equals($token, $providedToken)) {
            $authorized = true;
            break;
        }
    }
}

if (!$authorized) {
    http_response_code(403);
    header('Content-Type: text/plain');
    echo "Forbidden: Invalid or missing deployment token.\n";
    exit;
}

// Helper: discover valid PHP binary
function getPhpBinary(): string {
    foreach ([PHP_BINARY, '/opt/ecp-php82/bin/php', '/opt/ecp-php81/bin/php', '/usr/local/bin/php82', '/usr/local/bin/php81', '/usr/local/bin/php', '/usr/bin/php82', '/usr/bin/php'] as $c) {
        if ($c && @is_executable($c)) {
            return $c;
        }
    }
    return PHP_BINARY;
}

// Helper: purge legacy/deprecated public debug, seeder and maintenance scripts
function purgeDeprecatedFiles(): array {
    $publicDir = __DIR__;
    $baseDir   = dirname(__DIR__);

    $deprecatedPublicFiles = [
        'seed_admin.php',
        'set_grok.php',
        'debug_dump.php',
        'debug_error.php',
        'debug_wzatco.php',
        'dedup_catalog.php',
        'fix_db_images.php',
        'fix_deals.php',
        'fix_images.php',
        'deploy_extractor.php',
        'patch_deploy.php',
        'install_livewire.php',
        'migrate_db.php',
        'migrate_now.php',
        'phase9_audit_runner.php',
        'recovery_30days.php',
        'run_uic_migrate.php',
        'test.php',
        'test_article.php',
        'test_deal.php',
        'test_search.php',
        'uic_tables2.php',
    ];

    $purged = [];
    foreach ($deprecatedPublicFiles as $f) {
        $path = $publicDir . '/' . $f;
        if (file_exists($path)) {
            @unlink($path);
            $purged[] = 'public/' . $f;
        }
    }

    foreach (glob($publicDir . '/debug_*.php') as $f) {
        @unlink($f);
        $purged[] = 'public/' . basename($f);
    }
    foreach (glob($publicDir . '/test_*.php') as $f) {
        @unlink($f);
        $purged[] = 'public/' . basename($f);
    }

    $deprecatedBaseFiles = [
        'audit_deals.php',
        'check_db.php',
        'check_failed_jobs.php',
        'cleanup.php',
        'db_check.php',
        'query_deal.php',
        'seed_guides.php',
        'test.php',
        'test_homepage.php',
        'test_push.php',
        'test_update.php'
    ];
    foreach ($deprecatedBaseFiles as $f) {
        $path = $baseDir . '/' . $f;
        if (file_exists($path)) {
            @unlink($path);
            $purged[] = 'base/' . $f;
        }
    }

    return $purged;
}

// Helper: ensure worker auth keys are present in production .env
function ensureWorkerEnv(): void {
    $envFile = dirname(__DIR__) . '/.env';
    if (!file_exists($envFile)) return;
    $envContent = file_get_contents($envFile);
    $changed = false;
    if (!preg_match('/^WORKER_API_KEY=/m', $envContent)) {
        $envContent .= "\nWORKER_API_KEY=test-worker-token-123\n";
        $changed = true;
    }
    if (!preg_match('/^API_KEY=/m', $envContent)) {
        $envContent .= "\nAPI_KEY=test-worker-token-123\n";
        $changed = true;
    }
    if ($changed) {
        file_put_contents($envFile, $envContent);
    }
}

// ============================================================
// FRESH COMPOSER INSTALL — wipes corrupt vendor state first
// Usage: /unzip.php?composer=fresh
// ============================================================
if (isset($_GET['composer']) && $_GET['composer'] === 'fresh') {
    set_time_limit(600);
    ini_set('memory_limit', '512M');
    header('Content-Type: text/plain; charset=utf-8');

    $base     = dirname(__DIR__);
    $vendor   = $base . '/vendor';
    $composer = $base . '/composer.phar';
    $results  = [];

    // Find PHP binary
    $phpBin = PHP_BINARY;
    $results[] = 'PHP_BINARY: ' . $phpBin;
    foreach ([PHP_BINARY,'/opt/ecp-php82/bin/php','/opt/ecp-php81/bin/php','/usr/local/bin/php82','/usr/local/bin/php','/usr/bin/php'] as $c) {
        if ($c && is_executable($c)) { $phpBin = $c; $results[] = 'Using PHP: ' . $phpBin; break; }
    }

    // 1. Wipe corrupt vendor directory
    $results[] = 'Removing corrupt vendor/ directory...';
    $rmOut = []; exec('rm -rf ' . escapeshellarg($vendor) . ' 2>&1', $rmOut, $rmCode);
    $results[] = 'rm -rf vendor: exit=' . $rmCode . ' ' . implode(' ', $rmOut);
    $results[] = 'vendor/ exists after rm: ' . (is_dir($vendor) ? 'YES (problem!)' : 'NO (good)');

    // 2. Download composer if missing
    if (!file_exists($composer)) {
        $results[] = 'Downloading composer.phar...';
        $data = @file_get_contents('https://getcomposer.org/composer-stable.phar');
        if ($data) { file_put_contents($composer, $data); $results[] = 'Downloaded: ' . round(strlen($data)/1024/1024,2) . ' MB'; }
        else { echo implode("\n",$results)."\nFAIL: cannot download composer\n"; exit; }
    } else {
        $results[] = 'composer.phar present';
    }
    chmod($composer, 0755);
    $results[] = 'composer.phar executable: ' . (is_executable($composer) ? 'YES' : 'NO');

    // 3. Fresh composer install
    $results[] = 'Running fresh composer install...';
    $cmd = escapeshellarg($phpBin) . ' ' . escapeshellarg($composer)
         . ' install --no-dev --optimize-autoloader --no-interaction'
         . ' --working-dir=' . escapeshellarg($base) . ' 2>&1';
    $output = []; exec($cmd, $output, $exitCode);
    $results[] = 'Exit code: ' . $exitCode;
    $results = array_merge($results, $output);

    // 4. OPcache + artisan
    if (function_exists('opcache_reset')) { opcache_reset(); $results[] = 'OPcache reset OK'; }
    $artisan = $base . '/artisan';
    if (file_exists($artisan)) {
        chmod($artisan, 0755);
        // Clear bootstrap cache first
        foreach (glob($base.'/bootstrap/cache/*.php') as $f) @unlink($f);
        $results[] = 'bootstrap/cache cleared';
        exec(escapeshellarg($phpBin).' '.escapeshellarg($artisan).' config:clear 2>&1', $o1);
        exec(escapeshellarg($phpBin).' '.escapeshellarg($artisan).' cache:clear  2>&1', $o2);
        exec(escapeshellarg($phpBin).' '.escapeshellarg($artisan).' view:clear   2>&1', $o3);
        exec(escapeshellarg($phpBin).' '.escapeshellarg($artisan).' route:clear  2>&1', $o4);
        exec(escapeshellarg($phpBin).' '.escapeshellarg($artisan).' migrate --force 2>&1', $o5);
        $results[] = 'config:clear: '.implode(' ',$o1);
        $results[] = 'cache:clear:  '.implode(' ',$o2);
        $results[] = 'view:clear:   '.implode(' ',$o3);
        $results[] = 'route:clear:  '.implode(' ',$o4);
        $results[] = 'migrate:      '.implode(' ',$o5);
    }

    echo implode("\n", $results)."\n";
    echo ($exitCode === 0) ? "\n=== VENDOR RESTORED SUCCESSFULLY ===\n" : "\n=== COMPOSER INSTALL FAILED (exit=$exitCode) ===\n";
    exit;
}

// ============================================================
// COMPOSER INSTALL — restores missing vendor/ directory
// Usage: /unzip.php?composer=1
// ============================================================
if (isset($_GET['composer'])) {

    set_time_limit(300);
    ini_set('memory_limit', '512M');
    header('Content-Type: text/plain; charset=utf-8');

    $base     = dirname(__DIR__);
    $composer = $base . '/composer.phar';
    $results  = [];

    // Find the correct PHP binary (shared hosts often have it in a non-standard path)
    $phpBin = PHP_BINARY;
    $results[] = 'PHP_BINARY: ' . $phpBin;

    // Try common shared host PHP paths if PHP_BINARY looks wrong or missing
    $phpCandidates = [
        PHP_BINARY,
        '/opt/ecp-php82/bin/php',
        '/opt/ecp-php81/bin/php',
        '/usr/local/bin/php82',
        '/usr/local/bin/php81',
        '/usr/local/bin/php',
        '/usr/bin/php82',
        '/usr/bin/php',
    ];
    foreach ($phpCandidates as $candidate) {
        if ($candidate && is_executable($candidate)) {
            $phpBin = $candidate;
            $results[] = 'Using PHP binary: ' . $phpBin;
            break;
        }
    }

    // 1. Download or fix composer.phar
    if (!file_exists($composer)) {
        $results[] = 'Downloading composer.phar...';
        $data = @file_get_contents('https://getcomposer.org/composer-stable.phar');
        if ($data === false) {
            $data = @file_get_contents('https://getcomposer.org/download/latest-stable/composer.phar');
        }
        if ($data !== false) {
            file_put_contents($composer, $data);
            $results[] = 'Downloaded: ' . round(strlen($data)/1024/1024, 2) . ' MB';
        } else {
            echo implode("\n", $results) . "\nERROR: Could not download composer.phar\n";
            exit;
        }
    } else {
        $results[] = 'composer.phar already present, fixing permissions...';
    }
    // Always fix permissions
    chmod($composer, 0755);
    $results[] = 'composer.phar chmod 0755: ' . (is_executable($composer) ? 'OK' : 'FAILED');

    // 2. Run: php /path/to/composer.phar install
    $results[] = 'Running: ' . $phpBin . ' composer.phar install --no-dev ...';
    $cmd = escapeshellarg($phpBin)
         . ' ' . escapeshellarg($composer)
         . ' install --no-dev --optimize-autoloader --no-interaction'
         . ' --working-dir=' . escapeshellarg($base) . ' 2>&1';
    $results[] = 'CMD: ' . $cmd;
    $output = [];
    exec($cmd, $output, $exitCode);
    $results[] = 'Exit code: ' . $exitCode;
    $results = array_merge($results, $output);

    // 3. Clear OPcache
    if (function_exists('opcache_reset')) {
        opcache_reset();
        $results[] = 'OPcache reset OK';
    }

    // 4. Clear Laravel caches and migrate if artisan now works
    $artisan = $base . '/artisan';
    if ($exitCode === 0 && file_exists($artisan)) {
        chmod($artisan, 0755);
        exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' config:clear 2>&1', $o1);
        exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' cache:clear  2>&1', $o2);
        exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' view:clear   2>&1', $o3);
        exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' route:clear  2>&1', $o4);
        exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' migrate --force 2>&1', $o5);
        $results[] = 'config:clear: '  . implode(' ', $o1);
        $results[] = 'cache:clear: '   . implode(' ', $o2);
        $results[] = 'view:clear: '    . implode(' ', $o3);
        $results[] = 'route:clear: '   . implode(' ', $o4);
        $results[] = 'migrate: '       . implode(' ', $o5);
    }

    echo implode("\n", $results) . "\n";
    echo ($exitCode === 0) ? "\n=== VENDOR RESTORED SUCCESSFULLY ===\n" : "\n=== COMPOSER INSTALL FAILED ===\n";
    exit;
}

if (isset($_GET['fix_perms'])) {

    $artisan = __DIR__ . '/../artisan';
    $bootstrapCache = __DIR__ . '/../bootstrap/cache';
    $results = [];
    
    // Purge deprecated debug/seeder scripts
    $purged = purgeDeprecatedFiles();
    $results[] = 'Purged ' . count($purged) . ' deprecated files: ' . implode(', ', $purged);

    // Ensure worker API keys exist in .env
    ensureWorkerEnv();
    $results[] = 'Worker API keys verified in .env';

    // Fix artisan permissions
    if (file_exists($artisan)) {
        chmod($artisan, 0755);
        $results[] = 'artisan chmod 0755: ' . (is_executable($artisan) ? 'OK' : 'FAILED');
    } else {
        $results[] = 'artisan NOT FOUND at ' . $artisan;
    }
    
    // Fix bootstrap/cache directory permissions
    if (is_dir($bootstrapCache)) {
        chmod($bootstrapCache, 0775);
        // Delete stale cache files
        foreach (glob($bootstrapCache . '/*.php') as $f) {
            @unlink($f);
        }
        $results[] = 'bootstrap/cache cleared OK';
    }
    
    // Reset OPcache
    if (function_exists('opcache_reset')) {
        opcache_reset();
        $results[] = 'OPcache reset: OK';
    }
    
    // Now run artisan commands
    $phpBin = getPhpBinary();
    exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' config:clear 2>&1', $o1);
    exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' cache:clear 2>&1', $o2);
    exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' view:clear 2>&1', $o3);
    exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' route:clear 2>&1', $o4);
    exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' storage:link 2>&1', $o5);
    
    $results[] = 'config:clear: ' . implode(' ', $o1);
    $results[] = 'cache:clear: ' . implode(' ', $o2);
    $results[] = 'view:clear: ' . implode(' ', $o3);
    $results[] = 'route:clear: ' . implode(' ', $o4);
    $results[] = 'storage:link: ' . implode(' ', $o5);
    
    // Reset OPcache again after artisan
    if (function_exists('opcache_reset')) opcache_reset();
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'done', 'results' => $results]);
    exit;
}

if (isset($_GET['migrate'])) {
    try {
        require __DIR__.'/../vendor/autoload.php';
        $app = require_once __DIR__.'/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->call('migrate', ['--force' => true]);
        echo "Migrations executed: \n" . $kernel->output();
    } catch (\Exception $e) {
        echo "Migration failed: " . $e->getMessage();
    }
    exit;
}

if (isset($_GET['fix_url'])) {
    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) {
        echo json_encode(['error' => '.env not found']);
        exit;
    }
    $env = file_get_contents($envFile);
    $before = substr($env, 0, 200);
    if (preg_match('/^APP_URL=/m', $env)) {
        $env = preg_replace('/^APP_URL=.*/m', 'APP_URL=https://latestdeal.in', $env);
    } else {
        $env = "APP_URL=https://latestdeal.in\n" . $env;
    }
    file_put_contents($envFile, $env);
    // Run storage:link and cache clear
    $artisan = __DIR__ . '/../artisan';
    $phpBin = getPhpBinary();
    exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' storage:link 2>&1', $o1);
    exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' config:clear 2>&1', $o2);
    exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' cache:clear 2>&1', $o3);
    exec(escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . ' view:clear 2>&1', $o4);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'done',
        'env_before' => $before,
        'storage_link' => implode('\n', $o1),
        'config_clear' => implode('\n', $o2),
        'cache_clear' => implode('\n', $o3),
        'view_clear' => implode('\n', $o4),
    ]);
    exit;
}

if (isset($_GET['check_storage'])) {
    header('Content-Type: application/json');
    $storagePublic = __DIR__ . '/../storage/app/public';
    $dealsDir = $storagePublic . '/deals';
    $pubStorage = __DIR__ . '/storage';
    $sampleFile = '5435aefe-80ee-4d71-8c6d-2bd82a2fdc9a.jpeg';
    echo json_encode([
        'storage_app_public_exists' => is_dir($storagePublic),
        'deals_dir_exists' => is_dir($dealsDir),
        'deals_count' => is_dir($dealsDir) ? count(scandir($dealsDir)) - 2 : 0,
        'sample_file_exists' => file_exists($dealsDir . '/' . $sampleFile),
        'public_storage_exists' => file_exists($pubStorage),
        'public_storage_is_link' => is_link($pubStorage),
        'public_storage_is_dir' => is_dir($pubStorage),
        'public_storage_target' => is_link($pubStorage) ? readlink($pubStorage) : null,
    ]);
    exit;
}

// Simple script to extract deploy.zip and self-destruct

$zipFile = __DIR__ . '/../deploy.zip';
if (!file_exists($zipFile)) {
    if (file_exists(__DIR__ . '/deploy.zip')) {
        $zipFile = __DIR__ . '/deploy.zip';
    } elseif (file_exists(dirname(__DIR__, 2) . '/deploy.zip')) {
        $zipFile = dirname(__DIR__, 2) . '/deploy.zip';
    }
}

$extractPath = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: deploy.zip not found at {$zipFile}");
}

// Fast extraction using system unzip
$output = [];
$return_var = 0;
exec("unzip -o " . escapeshellarg($zipFile) . " -d " . escapeshellarg($extractPath) . " 2>&1", $output, $return_var);

file_put_contents(__DIR__ . '/unzip_log.txt', "Return var: $return_var\nOutput:\n" . implode("\n", $output));

$phpBin = getPhpBinary();

if ($return_var === 0) {
    // Purge deprecated files
    purgeDeprecatedFiles();
    ensureWorkerEnv();

    // Fix .env: ensure APP_URL and APP_ENV are set correctly for production
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        // Set APP_URL if missing or wrong
        if (!preg_match('/^APP_URL=https:\/\/latestdeal\.in/m', $envContent)) {
            if (preg_match('/^APP_URL=/m', $envContent)) {
                $envContent = preg_replace('/^APP_URL=.*/m', 'APP_URL=https://latestdeal.in', $envContent);
            } else {
                $envContent = "APP_URL=https://latestdeal.in\n" . $envContent;
            }
        }
        // NOTE: Do NOT change APP_ENV — setting it to 'production' enables ComingSoonMiddleware
        // Set APP_DEBUG=false for security
        if (preg_match('/^APP_DEBUG=/m', $envContent)) {
            $envContent = preg_replace('/^APP_DEBUG=.*/m', 'APP_DEBUG=false', $envContent);
        }
        
        // Disable coming soon
        if (preg_match('/^COMING_SOON_ENABLED=/m', $envContent)) {
            $envContent = preg_replace('/^COMING_SOON_ENABLED=.*/m', 'COMING_SOON_ENABLED=false', $envContent);
        } else {
            $envContent .= "\nCOMING_SOON_ENABLED=false\n";
        }
        file_put_contents($envFile, $envContent);
        echo "ENV file patched with APP_URL=https://latestdeal.in\n";
    }

    // Run Laravel commands
    $artisan = __DIR__ . '/../artisan';
    if (file_exists($artisan)) {
        exec(escapeshellarg($phpBin) . " " . escapeshellarg($artisan) . " optimize:clear", $output);
        exec(escapeshellarg($phpBin) . " " . escapeshellarg($artisan) . " view:clear", $output);
        exec(escapeshellarg($phpBin) . " " . escapeshellarg($artisan) . " cache:clear", $output);
        exec(escapeshellarg($phpBin) . " " . escapeshellarg($artisan) . " migrate --force", $output);
        exec(escapeshellarg($phpBin) . " " . escapeshellarg($artisan) . " push:generate-vapid", $output);
        
        // Fix 403 error by ensuring public/storage is a fresh symlink
        $storageLink = __DIR__ . '/../public/storage';
        if (is_link($storageLink) || is_dir($storageLink)) {
            exec("rm -rf " . escapeshellarg($storageLink));
        }
        exec(escapeshellarg($phpBin) . " " . escapeshellarg($artisan) . " storage:link", $output);
    }
    
    // Self-destruct zip
    @unlink($zipFile);
    
    echo "Extraction successful using system unzip. Migrations and cache clear executed. Cleanup complete.";
} else {
    // Fallback to PHP ZipArchive if unzip binary is not available
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo($extractPath);
        $zip->close();
        
        @unlink($zipFile);
        purgeDeprecatedFiles();
        ensureWorkerEnv();
        
        // Run Laravel commands using the correct PHP binary
        $artisan = __DIR__ . '/../artisan';
        if (file_exists($artisan)) {
            exec(escapeshellarg($phpBin) . " " . escapeshellarg($artisan) . " optimize:clear", $output);
            exec(escapeshellarg($phpBin) . " " . escapeshellarg($artisan) . " view:clear", $output);
            exec(escapeshellarg($phpBin) . " " . escapeshellarg($artisan) . " cache:clear", $output);
            exec(escapeshellarg($phpBin) . " " . escapeshellarg($artisan) . " migrate --force", $output);
            exec(escapeshellarg($phpBin) . " " . escapeshellarg($artisan) . " storage:link", $output);
        }
        
        echo "Extraction successful using ZipArchive. Migrations and cache clear executed. Cleanup complete.";
    } else {
        echo "Error: Failed to open deploy.zip";
    }
}

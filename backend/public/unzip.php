<?php

if (isset($_GET['fix_perms'])) {
    $artisan = __DIR__ . '/../artisan';
    $bootstrapCache = __DIR__ . '/../bootstrap/cache';
    $results = [];
    
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
    $phpBin = PHP_BINARY;
    exec($phpBin . ' ' . escapeshellarg($artisan) . ' config:clear 2>&1', $o1);
    exec($phpBin . ' ' . escapeshellarg($artisan) . ' cache:clear 2>&1', $o2);
    exec($phpBin . ' ' . escapeshellarg($artisan) . ' view:clear 2>&1', $o3);
    exec($phpBin . ' ' . escapeshellarg($artisan) . ' route:clear 2>&1', $o4);
    exec($phpBin . ' ' . escapeshellarg($artisan) . ' storage:link 2>&1', $o5);
    
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
if (isset($_GET['debug_deals'])) {
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
        $stmt = $db->query("SELECT id, title, url, image_path FROM deals WHERE status = 'active' ORDER BY id DESC");
        header('Content-Type: application/json');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (\Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
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
    exec(PHP_BINARY . ' ' . escapeshellarg($artisan) . ' storage:link 2>&1', $o1);
    exec(PHP_BINARY . ' ' . escapeshellarg($artisan) . ' config:clear 2>&1', $o2);
    exec(PHP_BINARY . ' ' . escapeshellarg($artisan) . ' cache:clear 2>&1', $o3);
    exec(PHP_BINARY . ' ' . escapeshellarg($artisan) . ' view:clear 2>&1', $o4);
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

if ($return_var === 0) {
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
        exec(PHP_BINARY . " " . escapeshellarg($artisan) . " optimize:clear", $output);
        exec(PHP_BINARY . " " . escapeshellarg($artisan) . " view:clear", $output);
        exec(PHP_BINARY . " " . escapeshellarg($artisan) . " cache:clear", $output);
        exec(PHP_BINARY . " " . escapeshellarg($artisan) . " migrate --force", $output);
        exec(PHP_BINARY . " " . escapeshellarg($artisan) . " push:generate-vapid", $output);
        
        // Fix 403 error by ensuring public/storage is a fresh symlink
        $storageLink = __DIR__ . '/../public/storage';
        if (is_link($storageLink) || is_dir($storageLink)) {
            exec("rm -rf " . escapeshellarg($storageLink));
        }
        exec(PHP_BINARY . " " . escapeshellarg($artisan) . " storage:link", $output);
    }
    
    // Self-destruct and cleanup
    @unlink($zipFile);
    // @unlink(__FILE__); // Disabled for debugging
    
    echo "Extraction successful using system unzip. Migrations and cache clear executed. Cleanup complete.";
} else {
    // Fallback to PHP ZipArchive if unzip binary is not available
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo($extractPath);
        $zip->close();
        
        @unlink($zipFile);
        // @unlink(__FILE__); // Disabled for debugging
        
        // Run Laravel commands using the correct PHP binary
        $artisan = __DIR__ . '/../artisan';
        if (file_exists($artisan)) {
            exec(PHP_BINARY . " " . escapeshellarg($artisan) . " optimize:clear", $output);
            exec(PHP_BINARY . " " . escapeshellarg($artisan) . " view:clear", $output);
            exec(PHP_BINARY . " " . escapeshellarg($artisan) . " cache:clear", $output);
            exec(PHP_BINARY . " " . escapeshellarg($artisan) . " migrate --force", $output);
            exec(PHP_BINARY . " " . escapeshellarg($artisan) . " storage:link", $output);
        }
        
        echo "Extraction successful using ZipArchive. Migrations and cache clear executed. Cleanup complete.";
    } else {
        echo "Error: Failed to open deploy.zip";
    }
}

<?php

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
        // Set APP_ENV=production
        if (preg_match('/^APP_ENV=/m', $envContent)) {
            $envContent = preg_replace('/^APP_ENV=.*/m', 'APP_ENV=production', $envContent);
        }
        // Set APP_DEBUG=false
        if (preg_match('/^APP_DEBUG=/m', $envContent)) {
            $envContent = preg_replace('/^APP_DEBUG=.*/m', 'APP_DEBUG=false', $envContent);
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

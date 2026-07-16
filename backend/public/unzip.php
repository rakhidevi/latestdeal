<?php

if (isset($_GET['migrate'])) {
    $artisan = __DIR__ . '/../artisan';
    if (file_exists($artisan)) {
        exec(PHP_BINARY . " " . escapeshellarg($artisan) . " migrate --force", $output);
        echo "Migrations executed: " . implode("\n", $output);
    } else {
        echo "Artisan not found!";
    }
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
    @unlink(__FILE__);
    
    echo "Extraction successful using system unzip. Migrations and cache clear executed. Cleanup complete.";
} else {
    // Fallback to PHP ZipArchive if unzip binary is not available
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo($extractPath);
        $zip->close();
        
        @unlink($zipFile);
        @unlink(__FILE__);
        
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

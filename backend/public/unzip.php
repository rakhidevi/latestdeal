<?php
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
    // Self-destruct and cleanup
    @unlink($zipFile);
    @unlink(__FILE__);
    
    echo "Extraction successful using system unzip. Cleanup complete.";
} else {
    // Fallback to PHP ZipArchive if unzip binary is not available
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo($extractPath);
        $zip->close();
        
        @unlink($zipFile);
        @unlink(__FILE__);
        
        echo "Extraction successful using ZipArchive. Cleanup complete.";
    } else {
        echo "Error: Failed to open deploy.zip";
    }
}

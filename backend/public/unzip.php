<?php
// Simple script to extract deploy.zip and self-destruct

$zipFile = __DIR__ . '/../deploy.zip';
$extractPath = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: deploy.zip not found at {$zipFile}");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractPath);
    $zip->close();
    
    // Self-destruct and cleanup
    @unlink($zipFile);
    @unlink(__FILE__);
    
    echo "Extraction successful. Cleanup complete.";
} else {
    echo "Error: Failed to open deploy.zip";
}

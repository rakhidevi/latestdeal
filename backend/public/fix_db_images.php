<?php
$root = dirname(__DIR__);
$dbPath = $root . '/database/database.sqlite';

if (!file_exists($dbPath)) {
    die("DB not found");
}

$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get all files in storage/app/public/deals
$imageDir = $root . '/storage/app/public/deals';
if (!is_dir($imageDir)) {
    die("Images dir not found");
}

$files = array_values(array_filter(scandir($imageDir), function($f) {
    return in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']);
}));

if (empty($files)) {
    die("No images found");
}

// Get all deals
$stmt = $db->query("SELECT id FROM deals");
$deals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fileCount = count($files);
$updated = 0;

foreach ($deals as $index => $deal) {
    $image = $files[$index % $fileCount];
    $imagePath = 'deals/' . $image;
    
    $updateStmt = $db->prepare("UPDATE deals SET image_path = ? WHERE id = ?");
    $updateStmt->execute([$imagePath, $deal['id']]);
    $updated++;
}

echo "Fixed $updated deals with real images from the server.";

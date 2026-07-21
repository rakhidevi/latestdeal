<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$catName = 'Uncategorized';
$cat = \App\Models\Category::firstOrCreate(
    ['slug' => \Illuminate\Support\Str::slug($catName)],
    ['name' => $catName]
);
echo "CATEGORY ID IS: " . var_export($cat->id, true) . "\n";

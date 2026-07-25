<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$deal = DB::table('deals')->where('title', 'like', '%Wzatco%')->first();
if ($deal) {
    echo "Found deal ID: {$deal->id}\n";
    echo "Image Path in DB: {$deal->image_path}\n";
    echo "Files in public/deals: " . (is_dir(public_path('deals')) ? count(scandir(public_path('deals'))) : 'NO DIR') . "\n";
    echo "Files in storage/app/public/deals: " . (is_dir(storage_path('app/public/deals')) ? count(scandir(storage_path('app/public/deals'))) : 'NO DIR') . "\n";
} else {
    echo "No Wzatco deal found in production DB.\n";
    
    // What about the hero deals? Let's check them.
    $heroDeals = DB::table('deals')->limit(5)->get();
    echo "Sample deals:\n";
    foreach ($heroDeals as $d) {
        echo "- {$d->title} (Img: {$d->image_path})\n";
    }
}

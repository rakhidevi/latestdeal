<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
$deal = DB::table('deals')->where('title', 'like', '%Wzatco%')->first();
$path = $deal->image_path;
echo "File exists in public_path: " . (file_exists(public_path($path)) ? 'YES' : 'NO') . "\n";
echo "File exists in storage_path: " . (file_exists(storage_path('app/public/' . $path)) ? 'YES' : 'NO') . "\n";

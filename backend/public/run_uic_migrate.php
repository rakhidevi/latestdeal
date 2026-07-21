<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
$output = Illuminate\Support\Facades\Artisan::output();

$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES LIKE "uic_%"');

echo json_encode(['output' => $output, 'tables' => $tables]);

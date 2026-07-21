<?php
// Persistent migrate runner (does NOT delete itself)
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

header('Content-Type: text/plain');
try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "Migrations ran successfully:\n";
    echo \Illuminate\Support\Facades\Artisan::output();
} catch (\Exception $e) {
    echo "Error running migrations: " . $e->getMessage();
}

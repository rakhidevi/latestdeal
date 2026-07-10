<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "Migrations ran successfully: <br>";
    echo nl2br(\Illuminate\Support\Facades\Artisan::output());
} catch (\Exception $e) {
    echo "Error running migrations: " . $e->getMessage();
}

// Clean up for security
@unlink(__FILE__);

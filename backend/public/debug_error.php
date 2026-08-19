<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Deal;

$deal = Deal::where('title', 'LIKE', 'Phase 9 Fixture%')
    ->where('editorial_status', 'PUBLISHED')
    ->where('status', 'active')
    ->first();

if (!$deal) {
    echo "No deal found!";
    exit;
}

echo "Found Deal: " . $deal->slug . "\n";

$controller = app(\App\Http\Controllers\DealController::class);
try {
    $response = $controller->show($deal, app(\App\Services\User\InteractionService::class));
    echo "Success calling show()!\n";
    if (method_exists($response, 'render')) {
        $html = $response->render();
        echo "Rendered HTML length: " . strlen($html) . "\n";
    }
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} catch (\Error $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

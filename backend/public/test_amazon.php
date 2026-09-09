<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deal = \App\Models\Deal::find(96);
if ($deal) {
    $deal->original_price = 2999.00;
    $deal->save();
    echo "Deal 96 MRP updated to 2999.\n";
} else {
    echo "Deal 96 not found.\n";
}

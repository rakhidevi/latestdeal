<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$deals = \App\Models\Deal::where('editorial_status', 'PUBLISHED')->get();
foreach ($deals as $deal) {
    // Touching the deal updates the updated_at timestamp and triggers the updated event, which triggers the ElasticSearch reindex via DealObserver
    $deal->touch();
    echo "Triggered index for deal " . $deal->id . "\n";
}
echo "Done.\n";

<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $count = \App\Models\Deal::where('editorial_status', 'IN_REVIEW')
        ->update([
            'editorial_status' => 'PUBLISHED',
            'editor_id' => 1,
            'reviewed_at' => now()
        ]);
    echo "Updated $count deals from IN_REVIEW to PUBLISHED.\n";

    // Also update any old deals missing pros/cons with something so they show up
    $count2 = \App\Models\Deal::where('editorial_status', 'PUBLISHED')
        ->whereNull('pros')
        ->update([
            'pros' => json_encode(['Great deal']),
            'cons' => json_encode(['None']),
            'editorial_summary' => 'This is a great deal.',
            'editorial_verdict' => 'Highly recommended.'
        ]);
    echo "Fixed $count2 older published deals with missing metadata.\n";

    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    echo "Cache cleared.";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}

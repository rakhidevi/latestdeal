<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

App\Models\Deal::whereNull('short_url')->get()->each(function($deal) {
    try {
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        $url = file_get_contents('https://tinyurl.com/api-create.php?url=' . urlencode($deal->affiliate_url), false, $context);
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $deal->update(['short_url' => $url]);
            echo "Updated {$deal->id}: {$url}\n";
        }
    } catch(\Exception $e) {
        echo "Failed {$deal->id} - Exception: " . $e->getMessage() . "\n";
    }
});
echo "Done\n";

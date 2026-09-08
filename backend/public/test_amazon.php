<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = "https://www.amazon.in/dp/B0DD44X6J5";
$response = \Illuminate\Support\Facades\Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
])->get($url);

echo "Status: " . $response->status() . "\n";
echo "Body length: " . strlen($response->body()) . "\n";
if ($response->status() == 503) {
    echo "Amazon blocked the request (503).\n";
} else {
    // try to match price
    preg_match('/class="a-price-whole"[^>]*>([\d,]+)/s', $response->body(), $m);
    echo "Price match: " . ($m[1] ?? 'None') . "\n";
}

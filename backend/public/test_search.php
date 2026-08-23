<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req1 = \Illuminate\Http\Request::create('/api/v1/search', 'GET', ['q' => 'Skyvolt']);
$res1 = app()->handle($req1);
$data1 = json_decode($res1->getContent(), true);

echo "Search results for 'Skyvolt' (Deal 59):\n";
if (isset($data1['data']) && count($data1['data']) > 0) {
    foreach ($data1['data'] as $deal) {
        echo "- ID {$deal['id']}: {$deal['title']} (Status: {$deal['editorial_status']})\n";
    }
} else {
    echo "No deals found.\n";
}

$req2 = \Illuminate\Http\Request::create('/api/v1/search', 'GET', ['q' => 'Scorch']);
$res2 = app()->handle($req2);
$data2 = json_decode($res2->getContent(), true);

echo "\nSearch results for 'Scorch' (Deal 60):\n";
if (isset($data2['data']) && count($data2['data']) > 0) {
    foreach ($data2['data'] as $deal) {
        echo "- ID {$deal['id']}: {$deal['title']} (Status: {$deal['editorial_status']})\n";
    }
} else {
    echo "No deals found.\n";
}

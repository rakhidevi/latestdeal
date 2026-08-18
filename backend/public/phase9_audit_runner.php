<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->call('phase9:audit');

echo "Command run! Status: " . $status . "\n";
echo "Output from storage/app/phase9-audit.json:\n\n";

echo file_get_contents(__DIR__.'/../storage/app/phase9-audit.json');

<?php
$expectedToken = 'AGY-TMP-884392-REC';
if (!isset($_GET['token']) || $_GET['token'] !== $expectedToken) {
    http_response_code(403);
    die('Forbidden');
}
header('Content-Type: application/json');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$results = [];

// Seed Guides
$kernel->call('db:seed', ['--class' => 'ArticleSeeder', '--force' => true]);
$results['seed_output'] = $kernel->output();

$kernel->call('optimize:clear');

$results['deals_count'] = \App\Models\Deal::count();
$results['created_last_24h'] = \App\Models\Deal::where('created_at', '>=', now()->subHours(24))->count();

$latest = \App\Models\Deal::latest('created_at')->first(['id','title','created_at']);
$results['latest_deal'] = $latest ? $latest->toArray() : null;
$results['published_guides'] = \App\Models\Article::where('status', 'published')->count();

echo json_encode($results, JSON_PRETTY_PRINT);

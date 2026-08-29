<?php
$expectedToken = 'AGY-TMP-884392-REC';
if (!isset($_GET['token']) || $_GET['token'] !== $expectedToken) {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: application/json');
$zipFile = __DIR__ . '/../deploy.zip';
if (!file_exists($zipFile)) {
    if (file_exists(__DIR__ . '/deploy.zip')) {
        $zipFile = __DIR__ . '/deploy.zip';
    } elseif (file_exists(dirname(__DIR__, 2) . '/deploy.zip')) {
        $zipFile = dirname(__DIR__, 2) . '/deploy.zip';
    }
}

$extractPath = __DIR__ . '/../';
$results = [];

// 1. Verify ZIP exists
$results['zip_exists'] = file_exists($zipFile);
if (!$results['zip_exists']) {
    die(json_encode(['error' => 'deploy.zip not found', 'results' => $results]));
}

// 2. Extract
$output = []; $return_var = 0;
exec("unzip -o " . escapeshellarg($zipFile) . " -d " . escapeshellarg($extractPath) . " 2>&1", $output, $return_var);
$results['extract_status'] = $return_var === 0 ? 'Success' : 'Failed';
$results['extract_output'] = implode("\n", array_slice($output, -10));

// 3. Boot Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Clear Caches
$kernel->call('optimize:clear');

// 4. Verify Dangerous Routes
$kernel->call('route:list');
$routeOutput = $kernel->output();
$dangerousFound = preg_match('/(debug-env|migrate-fresh|seed-admin-now|force-publish)/', $routeOutput);
$results['dangerous_routes_absent'] = !$dangerousFound;

// 5. Check Deal Count BEFORE Seeding
$results['deals_count_before'] = \App\Models\Deal::count();
$results['created_last_24h'] = \App\Models\Deal::where('created_at', '>=', now()->subHours(24))->count();

$latest = \App\Models\Deal::latest('created_at')->first(['id','title','created_at','updated_at','editorial_status','status']);
$results['latest_deal'] = $latest ? $latest->toArray() : null;

// 6. Seed Guides
$kernel->call('db:seed', ['--class' => 'ArticleSeeder', '--force' => true]);
$results['seed_output'] = $kernel->output();

// 7. Verify Guides
$results['published_guides'] = \App\Models\Article::where('status', 'published')->count();

// Final Cache Clear
$kernel->call('optimize:clear');

echo json_encode($results, JSON_PRETTY_PRINT);

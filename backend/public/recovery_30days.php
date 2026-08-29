<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$expectedToken = 'AGY-TMP-884392-REC';
if (!isset($_GET['token']) || $_GET['token'] !== $expectedToken) {
    http_response_code(403);
    die('Forbidden');
}
header('Content-Type: application/json');

try {
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

    // Extract again to get the fixed ArticleSeeder
    if (file_exists($zipFile)) {
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
            $results['extract_status'] = 'Success';
        } else {
            $results['extract_status'] = 'Failed to open zip';
        }
    }

    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

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
} catch (\Throwable $e) {
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}


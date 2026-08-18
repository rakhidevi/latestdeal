<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use App\Models\Deal;
use App\Models\Article;

class Phase9AuditCommand extends Command
{
    protected $signature = 'phase9:audit {--url= : Base URL to test against}';
    protected $description = 'Runs the Phase 9 Gate 0 & Gate 1 environment and SEO firewall audits.';

    protected $results = [];

    public function handle()
    {
        $this->results = [
            'environment' => [],
            'content' => [],
            'seo' => [],
            'ads' => [],
            'legacy_ui' => [],
            'overall' => 'REVIEW'
        ];

        $this->info("LATESTDEAL");
        $this->info("PHASE 9 PUBLIC-SITE QUALITY REPORT (GATES 0-5)");
        $this->info("────────────────────────────────────\n");

        $this->runGate0();
        $this->runGate1();
        $this->runLegacyUIScan();
        
        $this->saveOutputs();
    }

    private function runGate0()
    {
        $this->info("GATE 0 — ENVIRONMENT VERIFICATION");
        $this->info("────────────────────────────────────");

        $commit = trim(shell_exec('git rev-parse HEAD 2>/dev/null') ?? 'Unknown');
        $laravelVersion = app()->version();
        $env = app()->environment();

        $latestMigration = DB::table('migrations')->orderBy('id', 'desc')->first();
        $migrationStr = $latestMigration ? $latestMigration->migration : 'None';

        $this->results['environment'] = [
            'app_version' => 'latest',
            'laravel_version' => $laravelVersion,
            'git_commit' => substr($commit, 0, 7),
            'migration_status' => $migrationStr
        ];

        $this->line(sprintf("%-25s %s", "Application Commit:", substr($commit, 0, 7)));
        $this->line(sprintf("%-25s %s", "Current Environment:", $env));
        $this->line(sprintf("%-25s %s", "Laravel Version:", $laravelVersion));
        $this->line(sprintf("%-25s %s", "Latest Migration:", $migrationStr));
        
        $dealTotal = Deal::count();
        $dealPublished = Deal::where('status', 'PUBLISHED')->count();
        $dealDraft = Deal::where('status', 'DRAFT')->count();
        
        $articleTotal = Article::count();
        $articlePublished = Article::where('status', 'published')->count();
        $articleDraft = Article::where('status', 'draft')->count();

        $this->results['content'] = [
            'total_deals' => $dealTotal,
            'published_deals' => $dealPublished,
            'draft_deals' => $dealDraft,
            'total_articles' => $articleTotal,
            'published_articles' => $articlePublished,
            'draft_articles' => $articleDraft
        ];

        $this->line(sprintf("%-25s %s", "Total Deals:", $dealTotal));
        $this->line(sprintf("%-25s %s", "Published Deals:", $dealPublished));
        $this->line(sprintf("%-25s %s", "Total Articles:", $articleTotal));
        $this->line(sprintf("%-25s %s", "Published Articles:", $articlePublished));
        
        $this->info("\n");
    }

    private function runGate1()
    {
        $this->info("GATE 1 — URL/SEO FIREWALL AUDIT");
        $this->info("────────────────────────────────────");
        
        $baseUrl = rtrim($this->option('url') ?? config('app.url', 'http://localhost'), '/');
        
        $this->info("Testing against: " . $baseUrl . "\n");

        $this->line(sprintf("%-20s | %-15s | %-10s | %s", "Resource Type", "Expected", "Actual", "Pass/Fail"));
        $this->line(str_repeat("-", 65));

        $tests = [
            'published_deal_test' => ['model' => Deal::where('status', 'PUBLISHED')->first(), 'route' => 'deals.show', 'expected' => 200],
            'draft_deal_test' => ['model' => Deal::where('status', 'DRAFT')->first(), 'route' => 'deals.show', 'expected' => 404],
            'review_deal_test' => ['model' => Deal::where('status', 'REVIEW')->first(), 'route' => 'deals.show', 'expected' => 404],
            'auto_deal_test' => ['model' => Deal::where('status', 'AUTO')->first(), 'route' => 'deals.show', 'expected' => 404],
            'rejected_deal_test' => ['model' => Deal::where('status', 'REJECTED')->first(), 'route' => 'deals.show', 'expected' => 404],
            'published_guide_test' => ['model' => Article::where('status', 'published')->first(), 'route' => 'guides.show', 'expected' => 200],
            'draft_guide_test' => ['model' => Article::where('status', 'draft')->first(), 'route' => 'guides.show', 'expected' => 404],
        ];

        $seoBlocked = false;

        foreach ($tests as $key => $test) {
            $model = $test['model'];
            $expectedStatus = $test['expected'];
            
            if (!$model) {
                $this->line(sprintf("%-20s | %-15s | %-10s | %s", str_replace('_test', '', $key), "{$expectedStatus}/...", "N/A", "NOT_TESTED"));
                $this->results['seo'][$key] = "NOT_TESTED";
                continue;
            }

            $url = $baseUrl . '/' . ($test['route'] === 'deals.show' ? 'deal/' : 'guides/') . $model->slug;
            
            try {
                $response = Http::withoutVerifying()->get($url);
                $actualStatus = $response->status();
                
                $pass = ($actualStatus === $expectedStatus) ? 'PASS' : 'FAIL';
                
                $this->line(sprintf("%-20s | %-15s | %-10s | %s", str_replace('_test', '', $key), "{$expectedStatus}", $actualStatus, $pass == 'PASS' ? '✅ PASS' : '❌ FAIL'));
                
                $this->results['seo'][$key] = $pass;
                if ($pass === 'FAIL') $seoBlocked = true;
            } catch (\Exception $e) {
                $this->line(sprintf("%-20s | %-15s | %-10s | %s", str_replace('_test', '', $key), "{$expectedStatus}", "ERR", "❌ FAIL"));
                $this->results['seo'][$key] = 'FAIL';
                $seoBlocked = true;
            }
        }
        
        if ($seoBlocked) {
            $this->results['overall'] = 'BLOCKED';
        }

        $this->info("\n");
    }

    private function runLegacyUIScan()
    {
        $this->info("LEGACY UI SCAN (Grep resources/views)");
        $this->info("────────────────────────────────────");

        $forbiddenStrings = [
            "AI Analysis Score",
            "AI RECOMMENDED BUY",
            "HIDDEN AMAZON GEM",
            "DEAL OF THE HOUR",
            "LOWEST PRICE EVER",
            "Selling Fast",
            "bought today",
            "viewing now",
            "Score 85/100" // example
        ];

        $viewsPath = resource_path('views');
        $allFiles = File::allFiles($viewsPath);

        $hasFails = false;

        foreach ($forbiddenStrings as $searchString) {
            $found = false;
            foreach ($allFiles as $file) {
                $content = file_get_contents($file->getPathname());
                if (stripos($content, $searchString) !== false) {
                    $found = true;
                    break;
                }
            }

            $status = $found ? 'FAIL' : 'PASS';
            $this->line(sprintf("%-30s | %s", $searchString, $status == 'PASS' ? '✅ PASS' : '❌ FOUND -> FAIL'));
            
            $this->results['legacy_ui'][$searchString] = $status;
            
            if ($found) {
                $hasFails = true;
            }
        }

        if ($hasFails) {
            $this->results['overall'] = 'BLOCKED';
        }

        $this->info("\n");
    }

    private function saveOutputs()
    {
        $this->info("FINAL STATUS: " . $this->results['overall']);
        $this->info("────────────────────────────────────");

        $jsonOutput = json_encode($this->results, JSON_PRETTY_PRINT);
        
        // Save to storage
        File::put(storage_path('app/phase9-audit.json'), $jsonOutput);
        
        // Let's also save text output to a log file
        $txtOutput = print_r($this->results, true);
        File::put(storage_path('logs/phase9-audit.txt'), $txtOutput);

        $this->info("Output saved to storage/app/phase9-audit.json and storage/logs/phase9-audit.txt");
    }
}

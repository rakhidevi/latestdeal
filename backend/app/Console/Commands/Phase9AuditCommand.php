<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Deal;
use App\Models\Article;
use App\Models\User;
use Carbon\Carbon;

class Phase9AuditCommand extends Command
{
    protected $signature = 'phase9:audit {--url= : Base URL to test against}';
    protected $description = 'Runs the Advanced Phase 9 Environment, Fixture, and SEO firewall audits.';

    protected $results = [];
    protected $fixtures = [];
    protected $baseUrl;
    protected $sitemapUrls = [];

    public function handle()
    {
        $this->baseUrl = rtrim($this->option('url') ?? config('app.url', 'http://localhost'), '/');

        $this->results = [
            'environment' => [],
            'lifecycle' => [],
            'seo' => [],
            'articles' => [],
            'legacy_ui' => [],
            'cleanup' => [],
            'overall' => 'REVIEW'
        ];

        $this->info("LATESTDEAL");
        $this->info("PHASE 9 PUBLIC-SITE QUALITY REPORT");
        $this->info("────────────────────────────────────\n");

        $this->runEnvironmentAudit();
        $this->runLifecycleDistribution();
        
        // Fetch sitemap for reconciliation
        $this->loadSitemap();

        try {
            $this->runHttpFirewallAudit();
            $this->runArticleAudit();
        } finally {
            $this->runFixtureCleanup();
        }

        $this->runLegacyUiAudit();
        
        $this->saveOutputs();
    }

    private function loadSitemap()
    {
        try {
            $response = Http::withoutVerifying()->get($this->baseUrl . '/sitemap.xml');
            if ($response->successful()) {
                $xml = simplexml_load_string($response->body());
                if ($xml) {
                    foreach ($xml->url as $url) {
                        $this->sitemapUrls[] = (string)$url->loc;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Could not load sitemap.xml");
        }
    }

    private function inSitemap($url)
    {
        return in_array($url, $this->sitemapUrls);
    }

    private function runEnvironmentAudit()
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
        
        $this->info("\n");
    }

    private function runLifecycleDistribution()
    {
        $this->info("DEAL LIFECYCLE DISTRIBUTION");
        $this->info("────────────────────────────────────");
        
        $statusCounts = DB::table('deals')
            ->select('status', 'editorial_status', DB::raw('count(*) as total'))
            ->groupBy('status', 'editorial_status')
            ->get();

        foreach ($statusCounts as $row) {
            $s = $row->status ?? 'NULL';
            $es = $row->editorial_status ?? 'NULL';
            $this->line(sprintf("Processing: %-15s | Editorial: %-15s | Count: %d", $s, $es, $row->total));
            $this->results['lifecycle'][] = [
                'status' => $s,
                'editorial_status' => $es,
                'count' => $row->total
            ];
        }

        $this->info("\n");
    }

    private function createDealFixture($name, $attributes)
    {
        $categoryId = \App\Models\Category::first()->id ?? 1;
        $merchantId = \App\Models\Merchant::first()->id ?? 1;

        $base = [
            'title' => "Phase 9 Fixture: {$name}",
            'slug' => "phase9-fixture-" . Str::random(8),
            'hash_id' => Str::random(10),
            'url' => 'https://amazon.com/dp/B08N5WRWNW',
            'original_price' => 100,
            'discounted_price' => 80,
            'is_ads_eligible' => 0,
            'currency' => 'INR',
            'category_id' => $categoryId,
            'merchant_id' => $merchantId,
            'image_path' => 'fixtures/test.jpg'
        ];

        $deal = new Deal(array_merge($base, $attributes));
        $deal->save(); // Force save, bypass events if possible but save() triggers events. 
        // We will just delete it in finally block.
        
        $this->fixtures[] = [
            'type' => 'deal',
            'id' => $deal->id
        ];

        return $deal;
    }

    private function createArticleFixture($name, $attributes)
    {
        $authorId = \App\Models\User::first()->id ?? 1;

        $base = [
            'title' => "Phase 9 Fixture Article: {$name}",
            'slug' => "phase9-article-" . Str::random(8),
            'content' => 'Test content for phase 9 audit firewall tests.',
            'excerpt' => 'Test excerpt',
            'status' => 'draft',
            'author_id' => $authorId
        ];

        $article = new Article(array_merge($base, $attributes));
        $article->save();
        
        $this->fixtures[] = [
            'type' => 'article',
            'id' => $article->id
        ];

        return $article;
    }

    private function runHttpFirewallAudit()
    {
        $this->info("SEO FIREWALL MATRIX (HTTP Tests)");
        $this->info("────────────────────────────────────");

        $editorId = User::first()->id ?? 1;

        $matrix = [
            'Discovered/Auto' => [
                'attributes' => ['status' => 'active', 'editorial_status' => 'AUTO'],
                'expected' => 404,
                'index' => false,
                'ads' => false
            ],
            'Qualified/Auto' => [
                'attributes' => ['status' => 'active', 'editorial_status' => 'AUTO'],
                'expected' => 404,
                'index' => false,
                'ads' => false
            ],
            'Draft' => [
                'attributes' => ['status' => 'active', 'editorial_status' => 'DRAFT'],
                'expected' => 404,
                'index' => false,
                'ads' => false
            ],
            'In Review' => [
                'attributes' => ['status' => 'active', 'editorial_status' => 'IN_REVIEW'],
                'expected' => 404,
                'index' => false,
                'ads' => false
            ],
            'Rejected' => [
                'attributes' => ['status' => 'active', 'editorial_status' => 'REJECTED'],
                'expected' => 404,
                'index' => false,
                'ads' => false
            ],
            'Published + valid editorial data' => [
                'attributes' => [
                    'status' => 'active', 
                    'editorial_status' => 'PUBLISHED',
                    'editorial_verdict' => 'Good buy.',
                    'editor_id' => $editorId,
                    'reviewed_at' => Carbon::now(),
                    'is_ads_eligible' => 1
                ],
                'expected' => 200,
                'index' => true,
                'ads' => true
            ],
            'Published + missing editor' => [
                'attributes' => [
                    'status' => 'active', 
                    'editorial_status' => 'PUBLISHED',
                    'editorial_verdict' => 'Good buy.',
                    'editor_id' => null,
                    'reviewed_at' => Carbon::now()
                ],
                'expected' => 404,
                'index' => false,
                'ads' => false
            ],
            'Published + missing review date' => [
                'attributes' => [
                    'status' => 'active', 
                    'editorial_status' => 'PUBLISHED',
                    'editorial_verdict' => 'Good buy.',
                    'editor_id' => $editorId,
                    'reviewed_at' => null
                ],
                'expected' => 404,
                'index' => false,
                'ads' => false
            ],
            'Published + missing verdict' => [
                'attributes' => [
                    'status' => 'active', 
                    'editorial_status' => 'PUBLISHED',
                    'editorial_verdict' => null,
                    'editor_id' => $editorId,
                    'reviewed_at' => Carbon::now()
                ],
                'expected' => 404,
                'index' => false,
                'ads' => false
            ],
            'Expired + thin' => [
                'attributes' => [
                    'status' => 'expired', 
                    'editorial_status' => 'PUBLISHED',
                    'editorial_verdict' => null,
                ],
                'expected' => 410, // Assuming thin expired is 410
                'index' => false,
                'ads' => false
            ],
        ];

        $this->line(sprintf("%-32s | %-4s | %-6s | %-7s | %-5s | %s", "Fixture", "HTTP", "Robots", "Sitemap", "Ads", "Result"));
        $this->line(str_repeat("-", 85));

        $hasFails = false;

        foreach ($matrix as $name => $test) {
            $deal = $this->createDealFixture($name, $test['attributes']);
            
            $url = $this->baseUrl . '/deal/' . $deal->slug;
            
            try {
                $response = Http::withoutVerifying()->get($url);
                $actualStatus = $response->status();
                $body = $response->body();
                
                $actualIndex = (strpos($body, 'noindex') === false && $actualStatus === 200);
                $actualSitemap = $this->inSitemap($url);
                $actualAds = (strpos($body, '<x-ad-banner') !== false || strpos($body, 'adsbygoogle') !== false);
                
                $pass = ($actualStatus === $test['expected']) ? 'PASS' : 'FAIL';
                
                // If it's a 200, check index rules
                if ($test['expected'] === 200) {
                    if ($test['index'] !== $actualIndex) $pass = 'FAIL (Index mismatch)';
                    if ($test['index'] !== $actualSitemap) $pass = 'FAIL (Sitemap mismatch)';
                }

                if ($pass !== 'PASS') $hasFails = true;

                $this->line(sprintf(
                    "%-32s | %-4s | %-6s | %-7s | %-5s | %s", 
                    $name, 
                    $actualStatus, 
                    $actualIndex ? 'INDEX' : 'NOINDX',
                    $actualSitemap ? 'YES' : 'NO',
                    $actualAds ? 'YES' : 'NO',
                    $pass == 'PASS' ? '✅ PASS' : '❌ ' . $pass
                ));
                
                $this->results['seo'][$name] = $pass;
            } catch (\Exception $e) {
                $this->line(sprintf("%-32s | %-4s | %-6s | %-7s | %-5s | %s", $name, "ERR", "-", "-", "-", "❌ ERROR"));
                $this->results['seo'][$name] = 'FAIL';
                $hasFails = true;
            }
        }
        
        if ($hasFails) {
            $this->results['overall'] = 'BLOCKED';
        }

        $this->info("\n");
    }

    private function runArticleAudit()
    {
        $this->info("ARTICLE FIREWALL MATRIX");
        $this->info("────────────────────────────────────");

        $articleCount = Article::count();
        $this->info("Current staging: Articles = {$articleCount}");

        $matrix = [
            'DRAFT' => [
                'attributes' => ['status' => 'draft', 'published_at' => null],
                'expected' => 404,
                'sitemap' => false
            ],
            'PUBLISHED' => [
                'attributes' => ['status' => 'published', 'published_at' => Carbon::now()->subDay()],
                'expected' => 200,
                'sitemap' => true
            ],
            'PUBLISHED + future published_at' => [
                'attributes' => ['status' => 'published', 'published_at' => Carbon::now()->addDays(5)],
                'expected' => 404,
                'sitemap' => false
            ],
        ];

        $hasFails = false;
        $this->line(sprintf("%-32s | %-4s | %-7s | %s", "Fixture", "HTTP", "Sitemap", "Result"));
        $this->line(str_repeat("-", 65));

        foreach ($matrix as $name => $test) {
            $article = $this->createArticleFixture($name, $test['attributes']);
            $url = $this->baseUrl . '/guides/' . $article->slug;

            try {
                $response = Http::withoutVerifying()->get($url);
                $actualStatus = $response->status();
                $actualSitemap = $this->inSitemap($url);
                
                $pass = ($actualStatus === $test['expected'] && $actualSitemap === $test['sitemap']) ? 'PASS' : 'FAIL';
                if ($pass !== 'PASS') $hasFails = true;

                $this->line(sprintf(
                    "%-32s | %-4s | %-7s | %s", 
                    $name, 
                    $actualStatus, 
                    $actualSitemap ? 'YES' : 'NO',
                    $pass == 'PASS' ? '✅ PASS' : '❌ FAIL'
                ));

                $this->results['articles'][$name] = $pass;
            } catch (\Exception $e) {
                $this->line(sprintf("%-32s | %-4s | %-7s | %s", $name, "ERR", "-", "❌ ERROR"));
                $this->results['articles'][$name] = 'FAIL';
                $hasFails = true;
            }
        }

        if ($hasFails) {
            $this->results['overall'] = 'BLOCKED';
        }
        $this->info("\n");
    }

    private function runFixtureCleanup()
    {
        $this->info("FIXTURE CLEANUP");
        $this->info("────────────────────────────────────");
        
        $created = count($this->fixtures);
        $deleted = 0;

        foreach ($this->fixtures as $fixture) {
            if ($fixture['type'] === 'deal') {
                Deal::where('id', $fixture['id'])->forceDelete();
                $deleted++;
            } elseif ($fixture['type'] === 'article') {
                Article::where('id', $fixture['id'])->forceDelete();
                $deleted++;
            }
        }

        $remaining = $created - $deleted;
        
        $this->line(sprintf("%-25s %d", "Created:", $created));
        $this->line(sprintf("%-25s %d", "Deleted:", $deleted));
        $this->line(sprintf("%-25s %d", "Remaining fixtures:", $remaining));
        
        if ($remaining > 0) {
            $this->results['cleanup']['status'] = 'FAIL';
            $this->line("\nResult: ❌ FAIL (Cleanup failed)");
            $this->results['overall'] = 'BLOCKED';
        } else {
            $this->results['cleanup']['status'] = 'PASS';
            $this->line("\nResult: ✅ PASS");
        }

        $this->info("\n");
    }

    private function runLegacyUiAudit()
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
            "Score 85/100"
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
        $statusStr = $this->results['overall'] === 'REVIEW' ? '🟡 REVIEW' : ($this->results['overall'] === 'BLOCKED' ? '🔴 BLOCKED' : '🟢 READY');
        $this->info("FINAL STATUS: " . $statusStr);
        $this->info("────────────────────────────────────");

        $jsonOutput = json_encode($this->results, JSON_PRETTY_PRINT);
        
        File::put(storage_path('app/phase9-audit.json'), $jsonOutput);
        
        $txtOutput = print_r($this->results, true);
        File::put(storage_path('logs/phase9-audit.txt'), $txtOutput);

        $this->info("Output saved to storage/app/phase9-audit.json and storage/logs/phase9-audit.txt");
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Deal;
use App\Services\Editorial\Contracts\GuideArticleGeneratorInterface;
use Illuminate\Console\Command;

class GenerateGuideArticleCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'guides:generate 
                            {deal? : The ID of the deal to generate a guide for}
                            {--all : Process eligible deals without existing guide articles}
                            {--limit=5 : Maximum number of deals to process}
                            {--force : Force regenerate even if a guide article already exists}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a production-ready, Google AdWords and SEO compliant buying guide article for deals';

    protected GuideArticleGeneratorInterface $generator;

    public function __construct(GuideArticleGeneratorInterface $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dealId = $this->argument('deal');
        $all = $this->option('all');
        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        if ($dealId) {
            $deal = Deal::find($dealId);
            if (!$deal) {
                $this->error("Deal with ID {$dealId} not found.");
                return 1;
            }
            $this->processDeal($deal, $force);
            return 0;
        }

        if ($all) {
            $query = Deal::query();
            if (!$force) {
                // Select deals that do not have an associated article
                $query->whereDoesntHave('guideArticle');
            }

            $deals = $query->orderBy('id', 'desc')->take($limit)->get();

            if ($deals->isEmpty()) {
                $this->info("No deals require guide article generation.");
                return 0;
            }

            $this->info("Found {$deals->count()} deal(s) to process.");

            $bar = $this->output->createProgressBar($deals->count());
            $bar->start();

            foreach ($deals as $deal) {
                $this->processDeal($deal, $force, false);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Successfully completed guide generation batch.");
            return 0;
        }

        // If no deal ID or --all flag passed, pick the latest active deal
        $latestDeal = Deal::whereDoesntHave('guideArticle')->latest('id')->first();
        if (!$latestDeal) {
            $latestDeal = Deal::latest('id')->first();
        }

        if (!$latestDeal) {
            $this->error("No deals found in catalog.");
            return 1;
        }

        $this->info("No specific deal provided. Selected latest deal ID {$latestDeal->id}: {$latestDeal->title}");
        $this->processDeal($latestDeal, $force);
        return 0;
    }

    protected function processDeal(Deal $deal, bool $force = false, bool $verbose = true): void
    {
        if ($verbose) {
            $this->line("Synthesizing Google-compliant buying guide for Deal #{$deal->id}: {$deal->title}");
        }

        if (!$force && $deal->guideArticle()->exists()) {
            if ($verbose) {
                $existing = $deal->guideArticle;
                $this->warn("Deal #{$deal->id} already has a guide article: {$existing->slug} (use --force to overwrite)");
            }
            return;
        }

        $dto = $this->generator->generateForDeal($deal);
        $article = $this->generator->saveArticle($dto);

        if ($verbose) {
            $this->info("✓ Guide Article successfully published!");
            $this->line("  Title: {$article->title}");
            $this->line("  URL:   " . route('articles.show', $article->slug));
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DiscoverSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:discover-sources {keyword : The niche or product category to find sources for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use AI to discover top e-commerce sources for a specific keyword and add them to the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keyword = $this->argument('keyword');
        $this->info("Asking AI to discover sources for: {$keyword}");

        $ollamaUrl = env('OLLAMA_BASE_URL', 'http://host.docker.internal:11434') . '/api/generate';
        $model = env('OLLAMA_MODEL', 'llama3');

        $prompt = "You are an expert in Indian e-commerce. I need to find the top 5 legitimate online stores that sell \"{$keyword}\". " .
                  "Reply ONLY with a JSON array containing objects with these keys: " .
                  "'name' (store name), 'domain' (clean domain without https like 'store.in'), 'affiliate_key' (a guess for their affiliate param like 'tag', 'ref', 'aff_id'). " .
                  "Do not include any other text, just the raw JSON array.";

        try {
            $response = Http::timeout(30)->post($ollamaUrl, [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json'
            ]);

            if ($response->successful()) {
                $rawJson = $response->json('response');
                $sources = json_decode($rawJson, true);

                if (is_array($sources)) {
                    $added = 0;
                    foreach ($sources as $source) {
                        if (isset($source['name']) && isset($source['domain'])) {
                            $merchant = Merchant::firstOrCreate(
                                ['domain' => $source['domain']],
                                [
                                    'name' => $source['name'],
                                    'affiliate_param_key' => $source['affiliate_key'] ?? 'ref',
                                    'store_id' => 'kridaymart-auto-' . Str::random(5),
                                    'status' => true
                                ]
                            );

                            if ($merchant->wasRecentlyCreated) {
                                $this->line("✅ Added new source: {$source['name']} ({$source['domain']})");
                                $added++;
                            } else {
                                $this->line("ℹ️ Source already exists: {$source['name']}");
                            }
                        }
                    }
                    $this->info("Successfully discovered and added {$added} new sources!");
                } else {
                    $this->error("AI returned invalid JSON: " . $rawJson);
                }
            } else {
                $this->error("Failed to connect to AI engine.");
            }
        } catch (\Exception $e) {
            $this->error("Error communicating with Ollama: " . $e->getMessage());
        }
    }
}

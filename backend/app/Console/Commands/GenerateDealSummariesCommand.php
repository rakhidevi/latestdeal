<?php

namespace App\Console\Commands;

use App\Models\Deal;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateDealSummariesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deals:summarize {--limit=10 : The number of deals to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate AI Pros & Cons summaries for deals without them.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $autoSummarize = Setting::where('key', 'ai_auto_summarize')->value('value');

        if ($autoSummarize === 'disabled') {
            $this->info('AI Auto-Summarize is currently disabled in the Admin Settings.');
            return;
        }

        // Find deals that don't have features/verdict yet
        $deals = Deal::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('features')
                      ->orWhereNull('verdict');
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        if ($deals->isEmpty()) {
            $this->info("No active deals currently need AI summarization.");
            return;
        }

        $this->info("Found {$deals->count()} deals needing AI summaries.");

        $ollamaBaseUrl = Setting::where('key', 'ollama_base_url')->value('value') ?? env('OLLAMA_BASE_URL', 'https://ai.latestdeal.in');
        $ollamaUrl = rtrim($ollamaBaseUrl, '/') . '/api/generate';
        $model = Setting::where('key', 'ollama_model')->value('value') ?? env('OLLAMA_MODEL', 'llama3');

        $processedCount = 0;

        foreach ($deals as $deal) {
            $this->info("Generating summary for Deal ID: {$deal->id} - {$deal->title}");
            
            $prompt = "You are an expert consumer electronics and shopping reviewer. \n" .
                      "Deal Title: {$deal->title}\n" .
                      "Price: ₹{$deal->discounted_price} (Original: ₹{$deal->original_price})\n" .
                      "Brand: {$deal->brand}\n" .
                      "URL: {$deal->url}\n\n" .
                      "Task: Give me a short summary of this product. \n" .
                      "Reply ONLY with a raw JSON object containing these exact keys:\n" .
                      "{\n" .
                      "  \"pros\": [\"pro 1\", \"pro 2\", \"pro 3\"],\n" .
                      "  \"cons\": [\"con 1\", \"con 2\"],\n" .
                      "  \"verdict\": \"A one sentence verdict on whether this is a good buy at this price.\"\n" .
                      "}\n" .
                      "Do NOT include markdown formatting like ```json, just the raw JSON brackets.";

            try {
                $response = Http::timeout(25)->post($ollamaUrl, [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'format' => 'json'
                ]);

                if ($response->successful()) {
                    $jsonString = $response->json('response');
                    $result = json_decode($jsonString, true);

                    if ($result && isset($result['pros']) && isset($result['cons']) && isset($result['verdict'])) {
                        // Store pros & cons in the JSON 'features' column
                        $features = [
                            'pros' => $result['pros'],
                            'cons' => $result['cons']
                        ];
                        
                        $deal->features = $features; // Laravel casts array to JSON automatically if configured
                        $deal->verdict = $result['verdict'];
                        $deal->saveQuietly(); // Use saveQuietly so we don't trigger events/timestamps unnecessarily
                        
                        $this->line("✅ Successfully summarized deal {$deal->id}");
                        $processedCount++;
                    } else {
                        $this->error("Failed to parse JSON from AI for deal {$deal->id}. Raw output: " . substr($jsonString, 0, 100));
                        Log::warning("AI Summarize failed JSON parse for deal {$deal->id}", ['raw' => $jsonString]);
                    }
                } else {
                    $this->error("Ollama HTTP Error: " . $response->status());
                }
            } catch (\Exception $e) {
                $this->error("Failed to connect to AI for deal {$deal->id}: " . $e->getMessage());
                Log::error("Failed to evaluate deal ID {$deal->id} with AI: " . $e->getMessage());
            }
        }

        $this->info("Finished! Successfully summarized {$processedCount} deals.");
    }
}

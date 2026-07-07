<?php

namespace App\Console\Commands;

use App\Models\Deal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishDealsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deals:publish {--threshold=30 : The minimum discount percentage to auto-publish}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically publish pending deals that meet a high discount threshold.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pendingDeals = Deal::where('status', 'pending')->get();
        $publishedCount = 0;
        
        $ollamaUrl = env('OLLAMA_BASE_URL', 'http://host.docker.internal:11434') . '/api/generate';
        $model = env('OLLAMA_MODEL', 'llama3');

        foreach ($pendingDeals as $deal) {
            $discountPct = 0;
            if ($deal->original_price > 0) {
                $discountPct = (($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100;
            }

            $prompt = "You are an expert e-commerce deal evaluator. \n" .
                      "Deal Title: {$deal->title}\n" .
                      "Original Price: {$deal->original_price}\n" .
                      "Discounted Price: {$deal->discounted_price}\n" .
                      "Calculated Discount: {$discountPct}%\n\n" .
                      "Evaluate this deal out of 10 for genuineness and value. Many sellers inflate the Original Price. " .
                      "Reply ONLY with a JSON object in this format: {\"score\": number}";

            try {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->post($ollamaUrl, [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'format' => 'json'
                ]);

                if ($response->successful()) {
                    $result = json_decode($response->json('response'), true);
                    $score = $result['score'] ?? 0;

                    if ($score >= 7) {
                        $deal->status = 'active';
                        $deal->save();
                        $publishedCount++;
                        Log::info("AI Auto-published deal ID {$deal->id} with score {$score}/10");
                    } else {
                        Log::info("AI Rejected deal ID {$deal->id}. Score: {$score}/10");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to evaluate deal ID {$deal->id} with AI: " . $e->getMessage());
            }
        }

        $this->info("AI Evaluator published {$publishedCount} genuine deals.");
    }
}

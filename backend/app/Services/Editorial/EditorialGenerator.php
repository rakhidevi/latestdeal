<?php

namespace App\Services\Editorial;

use App\Models\Deal;
use App\Models\DealAiGeneration;
use App\Services\AI\AIRouter;
use Exception;
use Illuminate\Support\Facades\Log;

class EditorialGenerator
{
    protected AIRouter $aiRouter;

    public function __construct(AIRouter $aiRouter)
    {
        $this->aiRouter = $aiRouter;
    }

    public function generateForDeal(Deal $deal): bool
    {
        if ($deal->editorial_status !== 'DRAFT') {
            Log::warning("Deal {$deal->id} is not in DRAFT status. Skipping.");
            return false;
        }

        // Atomically set to AI_GENERATING
        $updated = Deal::where('id', $deal->id)
            ->where('editorial_status', 'DRAFT')
            ->update(['editorial_status' => 'AI_GENERATING']);

        if (!$updated) {
            Log::warning("Deal {$deal->id} could not be transitioned to AI_GENERATING atomically.");
            return false;
        }

        $deal->refresh();
        $deal->loadMissing('category');
        
        $sourceFacts = [
            'title' => $deal->title,
            'brand' => $deal->brand ?: 'Unspecified Brand',
            'category' => $deal->category ? $deal->category->name : 'Unspecified Category',
            'original_price' => (float)$deal->original_price,
            'discounted_price' => (float)$deal->discounted_price,
            'amount_saved' => (float)($deal->original_price - $deal->discounted_price),
            'discount_percentage' => (float)$deal->calculated_discount_percent,
        ];
        
        $sourceCompleteness = $this->calculateSourceCompleteness($sourceFacts);

        $qaFeedback = null;
        $maxAttempts = 3;

        $baseGenerationNumber = DealAiGeneration::where('deal_id', $deal->id)->max('generation_number') ?? 0;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $genNumber = $baseGenerationNumber + $attempt;
            $prompt = $this->buildPrompt($sourceFacts, $qaFeedback);

            $messages = [
                ['role' => 'system', 'content' => 'You are an expert consumer electronics and shopping reviewer. You strictly output JSON without markdown formatting. You adhere absolutely strictly to source facts and never hallucinate.'],
                ['role' => 'user', 'content' => $prompt]
            ];

            try {
                $response = $this->aiRouter->chat($messages, [
                    'capabilities' => ['JSON'],
                    'temperature' => 0.1
                ]);

                $jsonString = $response['content'];
                $provider = $response['provider'] ?? 'unknown';
                $model = $response['model'] ?? 'unknown';

                $parsed = json_decode($jsonString, true);
                if (is_array($parsed)) {
                    $parsed = array_change_key_case($parsed, CASE_LOWER);
                }

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $qaNotes = ["Failed to parse JSON."];
                    $this->logGeneration($deal->id, $genNumber, $model, $provider, 'INVALID_JSON', $jsonString, $sourceFacts, $qaNotes, $sourceCompleteness, null, null);
                    $qaFeedback = implode(" ", $qaNotes);
                    continue;
                }

                if (!$this->passesSchemaQa($parsed)) {
                    $qaNotes = ["Missing required JSON fields."];
                    $this->logGeneration($deal->id, $genNumber, $model, $provider, 'QA_FAILED', $jsonString, $sourceFacts, $qaNotes, $sourceCompleteness, null, null);
                    $qaFeedback = implode(" ", $qaNotes);
                    continue;
                }

                // Deterministic QA
                $detQaNotes = $this->deterministicQa($parsed, $sourceFacts);
                if (!empty($detQaNotes)) {
                    $this->logGeneration($deal->id, $genNumber, $model, $provider, 'FACTUALITY_FAILED', $jsonString, $sourceFacts, $detQaNotes, $sourceCompleteness, null, null);
                    $qaFeedback = implode(" ", $detQaNotes);
                    continue;
                }

                // Semantic QA
                $semQaNotes = $this->semanticQa($parsed, $sourceFacts);
                if (!empty($semQaNotes)) {
                    $this->logGeneration($deal->id, $genNumber, $model, $provider, 'SEMANTIC_QA_FAILED', $jsonString, $sourceFacts, $semQaNotes, $sourceCompleteness, null, null);
                    $qaFeedback = implode(" ", $semQaNotes);
                    continue;
                }

                // Success!
                $contentConfidence = 'HIGH'; // If it passes strict semantic QA and deterministic QA, confidence is HIGH
                if ($sourceCompleteness === 'LOW') {
                    $contentConfidence = 'MEDIUM'; // Cap confidence if source is sparse
                }

                $deal->editorial_summary = $parsed['editorial_summary'] ?? null;
                $deal->editorial_verdict = $parsed['editorial_verdict'] ?? null;
                $deal->pros = $parsed['pros'] ?? [];
                $deal->cons = $parsed['cons'] ?? [];
                $deal->best_for = $parsed['best_for'] ?? [];
                $deal->not_for = $parsed['not_for'] ?? [];
                
                // Transition through QUALITY_CHECK to respect state machine
                $deal->editorial_status = 'QUALITY_CHECK';
                $deal->save();

                $deal->editorial_status = 'IN_REVIEW';
                $deal->save();

                $this->logGeneration($deal->id, $genNumber, $model, $provider, 'SUCCESS', $jsonString, $sourceFacts, [], $sourceCompleteness, $contentConfidence, null, true);
                return true;

            } catch (\Throwable $e) {
                $qaNotes = ["Exception occurred during generation: " . $e->getMessage()];
                $this->logGeneration($deal->id, $genNumber, 'unknown', 'unknown', 'FAILED', null, $sourceFacts, $qaNotes, $sourceCompleteness, null, $e->getMessage());
                $qaFeedback = "An error occurred, please try again.";
                continue;
            }
        }

        // All attempts failed
        $this->rollbackToDraft($deal);
        return false;
    }
    
    protected function calculateSourceCompleteness(array $facts): string 
    {
        $score = 0;
        if (!empty($facts['title'])) $score++;
        if (!empty($facts['brand'])) $score++;
        if (!empty($facts['original_price'])) $score++;
        if (!empty($facts['discounted_price'])) $score++;
        if (!empty($facts['discount_percentage'])) $score++;
        
        if ($score === 5) return 'HIGH';
        if ($score >= 3) return 'MEDIUM';
        return 'LOW';
    }

    protected function buildPrompt(array $facts, ?string $qaFeedback): string
    {
        $feedbackSection = "";
        if ($qaFeedback) {
            $feedbackSection = "\n\nPREVIOUS QA FAILURE FEEDBACK (DO NOT REPEAT THESE MISTAKES):\n" . $qaFeedback . "\n";
        }

        return <<<PROMPT
Product: {$facts['title']}
Brand: {$facts['brand']}
Category: {$facts['category']}
Price: ₹{$facts['discounted_price']} (Original: ₹{$facts['original_price']}, {$facts['discount_percentage']}% off)
{$feedbackSection}
Task: Write a concise editorial review for this deal.
CRITICAL RULES:
1. Every factual or evaluative claim MUST be directly supported by source_facts. If it cannot be supported, omit it entirely.
2. Use every relevant verified source fact. If a fact isn't available, omit it. Do not infer or invent.
3. Do NOT invent personal experience (e.g. "I've tested this").
4. Do NOT invent historical prices or competitor comparisons without source data.
5. Do NOT invent warranty claims or specifications not present in the source.
6. Do NOT hallucinate subjective evaluations like "stylish", "excellent value", "premium", or "budget-friendly" unless mathematically proven by the discount.
7. best_for and not_for MUST return [] (empty array) if the available evidence isn't sufficient to make a claim. Do NOT guess.

Reply ONLY with a raw JSON object containing these exact keys (do not include markdown formatting):
{
  "editorial_summary": "A 2-3 sentence strictly factual description of the deal incorporating all available source facts.",
  "editorial_verdict": "A one sentence factual verdict.",
  "pros": ["Fact-based pro 1", "Fact-based pro 2"],
  "cons": ["Fact-based con 1"],
  "best_for": [],
  "not_for": []
}
PROMPT;
    }

    protected function passesSchemaQa(array $parsed): bool
    {
        $requiredKeys = ['editorial_summary', 'editorial_verdict', 'pros', 'cons', 'best_for', 'not_for'];
        foreach ($requiredKeys as $key) {
            if (!isset($parsed[$key])) {
                return false;
            }
        }
        return true;
    }
    
    protected function deterministicQa(array $parsed, array $facts): array
    {
        $notes = [];
        $content = json_encode($parsed, JSON_UNESCAPED_UNICODE);
        
        // 1. Math / Price Verification (Match ₹, Rs., INR, price is, price of)
        preg_match_all('/(?:₹|Rs\.?|INR|price(?: of| is)?)\s*([0-9,]+(?:\.[0-9]{1,2})?)/i', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $priceString) {
                $price = (float)str_replace(',', '', $priceString);
                if ($price != $facts['original_price'] && $price != $facts['discounted_price'] && $price != $facts['amount_saved']) {
                    $notes[] = "Removed unsupported claim: Invented price or amount {$priceString}. Must use exact source prices or savings.";
                }
            }
        }
        
        // 2. Discount Verification
        preg_match_all('/([0-9]+(?:\.[0-9]+)?)\s*(?:%|percent)/i', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $discountString) {
                $discount = (float)$discountString;
                $expectedRounded = round($facts['discount_percentage']);
                if (abs($discount - $facts['discount_percentage']) > 0.1 && $discount != $expectedRounded) {
                    $notes[] = "Removed unsupported claim: Invented discount {$discountString}%. Must use {$facts['discount_percentage']}% exactly or rounded to {$expectedRounded}%.";
                }
            }
        }

        return $notes;
    }
    
    protected function semanticQa(array $parsed, array $facts): array
    {
        $notes = [];
        
        $claims = [
            'Summary' => $parsed['editorial_summary'],
            'Verdict' => $parsed['editorial_verdict'],
            'Pros' => implode("; ", $parsed['pros']),
            'Cons' => implode("; ", $parsed['cons']),
            'Best For' => implode("; ", $parsed['best_for']),
            'Not For' => implode("; ", $parsed['not_for']),
        ];
        
        $claimsJson = json_encode($claims, JSON_PRETTY_PRINT);
        $factsJson = json_encode($facts, JSON_PRETTY_PRINT);
        
        $prompt = <<<PROMPT
You are a strict QA Fact Checker.
Source Facts:
{$factsJson}

Claims to Verify:
{$claimsJson}

Task: Verify if EVERY evaluative or factual claim in the provided text can be strictly and undeniably proven by the Source Facts.
If a claim says "stylish", "excellent value", "premium", "casual wear" etc., and it is NOT explicitly in the Source Facts, it is UNSUPPORTED.
If a claim is unsupported, return a reason starting with "Removed unsupported claim: [claim]".
If all claims are perfectly supported or are just restatements of the facts, return an empty array for notes.

Output strictly JSON:
{
  "pass": true or false,
  "notes": ["Removed unsupported claim: 'stylish'", "Removed unsupported claim: 'excellent value'"]
}
PROMPT;

        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        try {
            $response = $this->aiRouter->chat($messages, [
                'capabilities' => ['JSON'],
                'temperature' => 0.0 // Maximum determinism
            ]);
            
            $result = json_decode($response['content'], true);
            if (json_last_error() === JSON_ERROR_NONE && isset($result['pass'])) {
                if ($result['pass'] === false) {
                    $notes = $result['notes'] ?? ['Contains unsupported semantic claims.'];
                }
            } else {
                $notes[] = "QA Model failed to output valid JSON for verification.";
            }
        } catch (\Throwable $e) {
             $notes[] = "QA Model threw an exception during verification: " . $e->getMessage();
        }

        return $notes;
    }

    protected function rollbackToDraft(Deal $deal): void
    {
        // Don't trigger events
        Deal::where('id', $deal->id)->update(['editorial_status' => 'DRAFT']);
    }

    protected function logGeneration(
        int $dealId,
        int $genNumber,
        string $model,
        string $provider,
        string $status,
        ?string $content,
        array $sourceFacts,
        array $qaNotes,
        string $sourceCompleteness,
        ?string $contentConfidence,
        ?string $error = null,
        bool $qaResult = false
    ): void {
        DealAiGeneration::create([
            'deal_id' => $dealId,
            'generation_number' => $genNumber,
            'generation_target' => 'editorial_review',
            'model' => $model,
            'provider' => $provider,
            'status' => $status,
            'content' => $content ? json_decode($content, true) : null,
            'source_facts' => $sourceFacts,
            'qa_result' => $qaResult,
            'qa_notes' => empty($qaNotes) ? null : $qaNotes,
            'source_completeness' => $sourceCompleteness,
            'content_confidence' => $contentConfidence,
            'error' => $error
        ]);
    }
}

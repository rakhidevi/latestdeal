<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Editorial\EditorialGenerator;
use App\Services\AI\AIRouter;

$generator = new class(app(AIRouter::class)) extends EditorialGenerator {
    protected function deterministicQa(array $parsed, array $facts): array
    {
        $notes = [];
        $content = json_encode($parsed, JSON_UNESCAPED_UNICODE);
        
        // 1. Math / Price Verification
        preg_match_all('/(?:₹|Rs\.?|INR|price(?: of| is)?|at)\s*([0-9,]+(\.[0-9]{1,2})?)/i', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $priceString) {
                $price = (float)str_replace(',', '', $priceString);
                if ($price != $facts['original_price'] && $price != $facts['discounted_price'] && $price != $facts['amount_saved']) {
                    $notes[] = "Removed unsupported claim: Invented price or amount {$priceString}. Must use exact source prices or savings.";
                }
            }
        }
        
        // 2. Discount Verification
        preg_match_all('/([0-9]+(\.[0-9]+)?)\s*(?:%|percent)/i', $content, $matches);
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

    public function testDetQa() {
        $parsed = [
            'editorial_summary' => 'Price: ₹1,999',
        ];
        $facts = [
            'original_price' => 5499,
            'discounted_price' => 2099,
            'amount_saved' => 3400,
            'discount_percentage' => 61.83
        ];
        return $this->deterministicQa($parsed, $facts);
    }
    
    public function testSemQa() {
        $parsed = [
            'editorial_summary' => 'This shoe features excellent cushioning for your runs.',
            'editorial_verdict' => 'Great deal.',
            'pros' => [],
            'cons' => [],
            'best_for' => [],
            'not_for' => [],
        ];
        $facts = [
            'title' => 'Men Scorch Mark Running Shoe',
            'brand' => 'Puma',
            'original_price' => 5499,
            'discounted_price' => 2099,
            'amount_saved' => 3400,
            'discount_percentage' => 61.83
        ];
        return $this->semanticQa($parsed, $facts);
    }
};

echo "Testing Deterministic QA:\n";
$res1 = $generator->testDetQa();
print_r($res1);

echo "\nTesting Semantic QA:\n";
$res2 = $generator->testSemQa();
print_r($res2);

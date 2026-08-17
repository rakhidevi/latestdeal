<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\PolicySimulatorService;

class PolicySimulatorServiceTest extends TestCase
{
    protected PolicySimulatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PolicySimulatorService();
    }

    public function test_it_simulates_policy_rules_against_payloads()
    {
        $payloads = [
            ['id' => 1, 'discount_percentage' => 40, 'brand' => 'Nike'],
            ['id' => 2, 'discount_percentage' => 20, 'brand' => 'Nike'],
            ['id' => 3, 'discount_percentage' => 50, 'brand' => 'BannedBrand'],
        ];

        // Rule: min_discount: 30 AND brand_allowed: Nike
        // Payload 1: Pass
        // Payload 2: Fail (discount too low)
        // Payload 3: Fail (banned brand)
        
        $policyRules = "min_discount: 30\nbrand_allowed: Nike";
        
        $result = $this->service->simulate($policyRules, $payloads);

        $this->assertEquals(3, $result->total_payloads_processed);
        $this->assertEquals(1, $result->passed_count);
        $this->assertEquals(2, $result->failed_count);
        $this->assertEquals(33.33, $result->pass_rate_percentage);
        
        $this->assertCount(1, $result->sample_passes);
        $this->assertCount(2, $result->sample_failures);
    }
}

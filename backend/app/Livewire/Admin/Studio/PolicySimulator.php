<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;

#[Middleware('studio.admin')]
class PolicySimulator extends Component
{
    protected StudioAPI $studio;

    // Inputs
    public string $policyRules = "min_discount: 30\nbrand_allowed: Nike";
    public string $payloadSource = 'mock_recent'; // mock_recent, historical_trace
    public string $traceId = '';

    // Results
    public ?array $simulationResult = null;
    public bool $isRunning = false;

    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function runSimulation()
    {
        $this->isRunning = true;
        
        // Generate mock payloads based on source selection
        $payloads = $this->generateTestPayloads();

        // Run simulation
        $resultDTO = $this->studio->simulator()->simulate($this->policyRules, $payloads);
        
        $this->simulationResult = (array) $resultDTO;
        $this->isRunning = false;
    }

    private function generateTestPayloads(): array
    {
        if ($this->payloadSource === 'historical_trace' && !empty($this->traceId)) {
            // Simulated fetch of a specific payload from a trace
            return [
                ['trace_id' => $this->traceId, 'discount_percentage' => 45, 'brand' => 'Nike', 'price' => 2000],
                ['trace_id' => $this->traceId, 'discount_percentage' => 10, 'brand' => 'Adidas', 'price' => 3000]
            ];
        }

        // Default mock payloads for sandbox testing
        return [
            ['id' => 1, 'discount_percentage' => 40, 'brand' => 'Nike', 'price' => 1500],
            ['id' => 2, 'discount_percentage' => 20, 'brand' => 'Nike', 'price' => 2500],
            ['id' => 3, 'discount_percentage' => 50, 'brand' => 'BannedBrand', 'price' => 1000],
            ['id' => 4, 'discount_percentage' => 35, 'brand' => 'Puma', 'price' => 1800],
            ['id' => 5, 'discount_percentage' => 15, 'brand' => 'Adidas', 'price' => 2200],
            ['id' => 6, 'discount_percentage' => 60, 'brand' => 'Nike', 'price' => 900],
            ['id' => 7, 'discount_percentage' => 30, 'brand' => 'UnderArmour', 'price' => 3100],
            ['id' => 8, 'discount_percentage' => 5, 'brand' => 'Nike', 'price' => 4000],
            ['id' => 9, 'discount_percentage' => 45, 'brand' => 'Reebok', 'price' => 1200],
            ['id' => 10, 'discount_percentage' => 25, 'brand' => 'Nike', 'price' => 2100],
        ];
    }

    public function render()
    {
        return view('livewire.admin.studio.policy-simulator')->layout('layouts.admin');
    }
}

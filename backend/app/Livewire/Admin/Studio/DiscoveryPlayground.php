<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;

#[Middleware('studio.admin')]
class DiscoveryPlayground extends Component
{
    protected StudioAPI $studio;

    // Inputs
    public string $provider = 'Amazon';
    public array $constraints = [
        'brand' => '',
        'node_id' => '',
        'min_discount' => ''
    ];

    // Execution Results
    public ?array $simulationResult = null;
    public bool $isRunning = false;

    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function runDiscovery()
    {
        $this->isRunning = true;
        
        // Ensure at least one constraint is provided to avoid crawling the whole site
        $activeConstraints = array_filter($this->constraints);
        if (empty($activeConstraints)) {
            session()->flash('error', 'Please provide at least one constraint (Brand, Node, or Discount).');
            $this->isRunning = false;
            return;
        }

        $this->simulationResult = $this->studio->playground()->simulateDiscovery($this->provider, $activeConstraints);
        $this->isRunning = false;
    }

    public function clearConstraints()
    {
        $this->constraints = [
            'brand' => '',
            'node_id' => '',
            'min_discount' => ''
        ];
        $this->simulationResult = null;
    }

    public function render()
    {
        return view('livewire.admin.studio.discovery-playground')->layout('layouts.admin');
    }
}

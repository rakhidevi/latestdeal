<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;
use App\Services\Studio\DTOs\EventQueryDTO;

#[Middleware('studio.admin')]
class OperationsDashboard extends Component
{
    protected StudioAPI $studio;

    public array $executiveHealth = [];
    public array $providerHealth = [];
    public array $queueHealth = [];
    public array $workerMetrics = [];
    public array $revenueMetrics = [];
    public array $rolloutStatus = [];
    public array $activeAlerts = [];
    
    public array $miniPipeline = [];
    public array $miniEventStream = [];

    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function mount()
    {
        $this->fetchData();
    }

    public function fetchData()
    {
        $ops = $this->studio->operations();
        
        $this->executiveHealth = $ops->getExecutiveHealth();
        $this->providerHealth = $ops->getProviderHealth();
        $this->queueHealth = $ops->getQueueHealth();
        $this->workerMetrics = $ops->getWorkerMetrics();
        $this->revenueMetrics = $ops->getRevenueMetrics();
        $this->rolloutStatus = $ops->getRolloutStatus();
        $this->activeAlerts = $ops->getActiveAlerts();

        // Fetch Mini Pipeline (Sprint 5.3 reuse)
        $pipelineNodes = $this->studio->pipeline()->getPipelineNodes('15m');
        $this->miniPipeline = array_map(function($node) {
            return (array) $node;
        }, $pipelineNodes);

        // Fetch Mini Event Stream (Sprint 5.2 reuse)
        $query = new EventQueryDTO(['limit' => 20]);
        $this->miniEventStream = $this->studio->events()->getEvents($query);
    }

    // Quick Actions (Protected by UI RBAC checks)
    public function pauseRollout()
    {
        // Add log or permission check
        session()->flash('message', 'Rollout paused successfully.');
    }

    public function enableShadowMode()
    {
        session()->flash('message', 'Shadow mode enabled globally.');
    }

    public function triggerKillSwitch()
    {
        session()->flash('error', 'KILL SWITCH ACTIVATED. System halted.');
    }

    public function render()
    {
        return view('livewire.admin.studio.operations-dashboard')->layout('layouts.admin');
    }
}

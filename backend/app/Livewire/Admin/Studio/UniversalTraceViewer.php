<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;

#[Middleware('studio.admin')]
class UniversalTraceViewer extends Component
{
    public string $searchQuery = '';
    
    public ?string $activeTraceId = null;
    public ?array $activeTrace = null;
    public array $searchResults = [];
    public ?string $expandedStage = null;
    public array $traceEvents = [];

    protected StudioAPI $studio;

    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function mount(?string $id = null)
    {
        if ($id) {
            $this->searchQuery = $id;
            $this->performSearch();
        }
    }

    public function performSearch()
    {
        if (empty($this->searchQuery)) return;
        
        $this->searchResults = $this->studio->search()->resolve($this->searchQuery);
        
        if (count($this->searchResults) === 1) {
            $this->selectTrace($this->searchResults[0]['trace_id']);
        } else {
            $this->activeTraceId = null;
            $this->activeTrace = null;
        }
    }
    
    public function selectTrace(string $traceId)
    {
        $this->activeTraceId = $traceId;
        $this->activeTrace = $this->studio->timeline()->reconstruct($traceId);
        
        // Mock UCDP event stream based on graph nodes
        $this->traceEvents = [];
        foreach ($this->activeTrace['nodes'] ?? [] as $node) {
            $this->traceEvents[] = [
                'timestamp' => $node['timestamp'],
                'event' => $node['type'] . '_COMPLETED',
                'uuid' => $node['id']
            ];
        }
    }

    public function toggleStage(string $stageId)
    {
        if ($this->expandedStage === $stageId) {
            $this->expandedStage = null;
        } else {
            $this->expandedStage = $stageId;
        }
    }

    public function triggerReplay()
    {
        $this->dispatch('notify', ['message' => 'Replay Engine Launched for ' . $this->activeTraceId, 'type' => 'success']);
    }

    public function triggerComparison()
    {
        $this->dispatch('notify', ['message' => 'Regression Comparison Started for ' . $this->activeTraceId, 'type' => 'info']);
    }

    public function triggerFork()
    {
        $this->dispatch('notify', ['message' => 'Forking trace ' . $this->activeTraceId . ' for experimentation.', 'type' => 'warning']);
    }

    public function render()
    {
        return view('livewire.admin.studio.universal-trace-viewer')->layout('layouts.admin');
    }
}

<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;

#[Middleware('studio.admin')]
class PipelineViewer extends Component
{
    public array $pipelineNodes = [];
    public string $timeframe = '1h'; // Live, 15m, 1h, 24h, 7d
    public bool $polling = true;

    protected StudioAPI $studio;

    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function mount()
    {
        $this->fetchPipelineData();
    }

    public function fetchPipelineData()
    {
        // $this->studio->pipeline()->getPipelineNodes() returns PipelineNodeDTOs
        // Livewire needs arrays for easy blade rendering, so we cast them.
        $nodes = $this->studio->pipeline()->getPipelineNodes($this->timeframe);
        
        $this->pipelineNodes = array_map(function($node) {
            return (array) $node;
        }, $nodes);
    }

    public function setTimeframe(string $timeframe)
    {
        $this->timeframe = $timeframe;
        $this->polling = ($timeframe === 'live' || $timeframe === '15m' || $timeframe === '1h');
        $this->fetchPipelineData();
    }

    public function render()
    {
        return view('livewire.admin.studio.pipeline-viewer')->layout('layouts.admin');
    }
}

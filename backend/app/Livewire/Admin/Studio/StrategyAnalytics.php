<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;

#[Middleware('studio.admin')]
class StrategyAnalytics extends Component
{
    protected StudioAPI $studio;

    // Filters
    public string $groupBy = 'strategy'; // strategy, provider, brand, profile
    public string $timePeriod = 'this_week'; // today, this_week, this_month

    // Data
    public array $automatedInsights = [];
    public array $funnelMetrics = [];

    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function mount()
    {
        $this->fetchData();
    }

    public function updatedGroupBy()
    {
        $this->fetchData();
    }

    public function updatedTimePeriod()
    {
        $this->fetchData();
    }

    public function fetchData()
    {
        $this->automatedInsights = $this->studio->analytics()->getAutomatedInsights();
        $this->funnelMetrics = $this->studio->analytics()->getFunnelMetrics($this->groupBy);
    }

    public function render()
    {
        return view('livewire.admin.studio.strategy-analytics')->layout('layouts.admin');
    }
}

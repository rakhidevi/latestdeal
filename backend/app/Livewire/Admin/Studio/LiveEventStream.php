<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;
use App\Services\Studio\DTOs\EventQueryDTO;

#[Middleware('studio.admin')]
class LiveEventStream extends Component
{
    public array $events = [];
    public int $limit = 50;
    
    // Filters
    public string $filterType = '';
    public string $filterProvider = '';
    public string $filterSeverity = '';
    public string $filterTraceId = '';
    
    // UI State
    public bool $polling = true;
    public string $mode = 'live'; // live, 10m, 1h, yesterday
    public array $metrics = [];

    protected StudioAPI $studio;

    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function mount()
    {
        $this->fetchEvents();
    }

    public function fetchEvents()
    {
        if (!$this->polling && $this->mode === 'live' && !empty($this->events)) {
            return; // If paused, don't fetch new events unless filters change
        }

        $queryData = [
            'limit' => $this->limit,
            'eventType' => $this->filterType ?: null,
            'provider' => $this->filterProvider ?: null,
            'severity' => $this->filterSeverity ?: null,
            'traceId' => $this->filterTraceId ?: null,
        ];

        // Apply historical mode
        if ($this->mode === '10m') {
            $queryData['from'] = now()->subMinutes(10)->toISOString();
        } elseif ($this->mode === '1h') {
            $queryData['from'] = now()->subHour()->toISOString();
        } elseif ($this->mode === 'yesterday') {
            $queryData['from'] = now()->subDay()->startOfDay()->toISOString();
            $queryData['to'] = now()->subDay()->endOfDay()->toISOString();
            $this->polling = false; // Never poll yesterday
        }

        $query = new EventQueryDTO($queryData);
        $this->events = $this->studio->events()->getEvents($query);
        $this->metrics = $this->studio->events()->getPerformanceMetrics();
    }

    public function updated($propertyName)
    {
        // Whenever a filter changes, refetch immediately
        if (in_array($propertyName, ['filterType', 'filterProvider', 'filterSeverity', 'filterTraceId', 'mode'])) {
            $this->fetchEvents();
        }
    }

    public function togglePolling()
    {
        $this->polling = !$this->polling;
    }

    public function clearStream()
    {
        $this->events = [];
    }

    public function setMode(string $mode)
    {
        $this->mode = $mode;
        $this->polling = ($mode === 'live');
        $this->fetchEvents();
    }

    public function render()
    {
        return view('livewire.admin.studio.live-event-stream')->layout('layouts.admin');
    }
}

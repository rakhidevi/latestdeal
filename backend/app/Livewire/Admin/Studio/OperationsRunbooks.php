<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;

#[Middleware('studio.admin')]
class OperationsRunbooks extends Component
{
    protected StudioAPI $studio;

    public array $catalog = [];
    public ?array $activeRunbook = null;
    public ?array $diagnosticResults = null;
    public bool $isRunningDiagnostics = false;

    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function mount()
    {
        $this->catalog = $this->studio->runbooks()->getRunbookCatalog();
    }

    public function selectRunbook(string $id)
    {
        $this->activeRunbook = $this->studio->runbooks()->getRunbook($id);
        $this->diagnosticResults = null;
        $this->isRunningDiagnostics = false;
    }

    public function runDiagnostics()
    {
        if (!$this->activeRunbook) return;

        $this->isRunningDiagnostics = true;
        
        // Simulate a slight delay for realism
        sleep(1);
        
        $this->diagnosticResults = $this->studio->runbooks()->runDiagnostics($this->activeRunbook['id'] ?? '');
        $this->isRunningDiagnostics = false;
    }

    public function render()
    {
        return view('livewire.admin.studio.operations-runbooks')->layout('layouts.admin');
    }
}

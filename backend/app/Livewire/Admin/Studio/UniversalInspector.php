<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;

#[Middleware('studio.admin')]
class UniversalInspector extends Component
{
    public string $entityId = '';
    public ?array $entityData = null;
    public ?string $entityType = null;
    
    protected StudioAPI $studio;

    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function mount(?array $nodeData = null)
    {
        if ($nodeData) {
            $this->entityId = $nodeData['id'] ?? 'unknown';
            $this->entityType = $nodeData['type'] ?? 'GenericEntity';
            $this->entityData = $nodeData;
        }
    }

    public function render()
    {
        return view('livewire.admin.studio.universal-inspector')->layout('layouts.admin');
    }
}

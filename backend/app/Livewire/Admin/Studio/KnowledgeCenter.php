<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;

#[Middleware('studio.admin')]
class KnowledgeCenter extends Component
{
    protected StudioAPI $studio;

    // Sidebar State
    public array $entityTree = [];
    public ?string $selectedEntityId = null;

    // Editor State
    public ?array $activeFile = null;
    public string $editorContent = '';
    
    // Workflow & Impact Analysis
    public bool $showImpactModal = false;
    public ?array $impactAnalysis = null;

    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function mount()
    {
        $this->entityTree = $this->studio->knowledge()->getEntities();
    }

    public function selectEntity(string $id)
    {
        $this->selectedEntityId = $id;
        $this->activeFile = $this->studio->knowledge()->getKnowledgeFile($id);
        $this->editorContent = $this->activeFile['content'] ?? '';
    }

    public function handleWorkflowAction(string $action)
    {
        if (!$this->selectedEntityId) return;

        // If the user attempts to publish/compile, we first calculate Impact Analysis
        if (in_array($action, ['compile', 'publish'])) {
            $this->impactAnalysis = $this->studio->knowledge()->getImpactAnalysis($this->selectedEntityId, $this->editorContent);
            $this->showImpactModal = true;
            return; // We wait for explicit confirmation from the modal
        }

        // Standard actions (validate, diff, preview)
        $response = $this->studio->knowledge()->transitionWorkflow($this->selectedEntityId, $action, $this->editorContent);
        
        $this->activeFile['status'] = $response['status'];
        session()->flash('message', "Workflow step '{$action}' completed.");
    }

    public function confirmPublish()
    {
        // Proceed with actual publish after reviewing impact
        $response = $this->studio->knowledge()->transitionWorkflow($this->selectedEntityId, 'publish', $this->editorContent);
        
        $this->activeFile['status'] = $response['status'];
        $this->showImpactModal = false;
        
        session()->flash('message', "Knowledge version published successfully.");
    }

    public function render()
    {
        return view('livewire.admin.studio.knowledge-center')->layout('layouts.admin');
    }
}

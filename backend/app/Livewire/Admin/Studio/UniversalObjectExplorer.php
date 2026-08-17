<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;

#[Middleware('studio.admin')]
class UniversalObjectExplorer extends Component
{
    public string $searchQuery = '';
    public ?array $relationshipGraph = null;
    public ?string $selectedNodeId = null;

    protected StudioAPI $studio;

    // Listen to query strings in the URL to allow deep-linking from anywhere
    protected $queryString = ['searchQuery' => ['as' => 'query']];

    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function mount()
    {
        if ($this->searchQuery) {
            $this->performSearch();
        }
    }

    public function performSearch()
    {
        if (empty($this->searchQuery)) {
            $this->relationshipGraph = null;
            $this->selectedNodeId = null;
            return;
        }

        $this->relationshipGraph = $this->studio->explorer()->resolveGraph($this->searchQuery);
        // Auto-select the root node
        if ($this->relationshipGraph) {
            $this->selectedNodeId = $this->relationshipGraph['id'];
        }
    }

    public function selectNode(string $nodeId)
    {
        $this->selectedNodeId = $nodeId;
    }

    public function getSelectedNodeData(): ?array
    {
        if (!$this->relationshipGraph || !$this->selectedNodeId) return null;
        
        return $this->findNodeInGraph($this->relationshipGraph, $this->selectedNodeId);
    }

    private function findNodeInGraph(array $node, string $targetId): ?array
    {
        if ($node['id'] === $targetId) {
            return $node;
        }
        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $found = $this->findNodeInGraph($child, $targetId);
                if ($found) return $found;
            }
        }
        return null;
    }

    public function render()
    {
        return view('livewire.admin.studio.universal-object-explorer', [
            'selectedNodeData' => $this->getSelectedNodeData()
        ])->layout('layouts.admin');
    }
}

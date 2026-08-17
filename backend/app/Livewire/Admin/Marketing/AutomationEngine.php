<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use Livewire\Attributes\Middleware;

#[Middleware('studio.admin')]
class AutomationEngine extends Component
{
    public array $workflows = [];

    public function mount()
    {
        $this->workflows = [
            [
                'id' => 'wf-1',
                'name' => 'Diwali Campaign Auto-Publisher',
                'trigger' => 'Schedule (Every 4 hours)',
                'action' => 'Publish Premium Electronics',
                'frequency_cap' => 'Max 3 per day',
                'status' => 'active'
            ],
            [
                'id' => 'wf-2',
                'name' => 'Fashion Clearance',
                'trigger' => 'Event (New Deal Extracted)',
                'action' => 'Queue for Evening Publish',
                'frequency_cap' => 'Max 5 per week',
                'status' => 'paused'
            ]
        ];
    }

    public function toggleWorkflow($id)
    {
        foreach ($this->workflows as &$wf) {
            if ($wf['id'] === $id) {
                $wf['status'] = $wf['status'] === 'active' ? 'paused' : 'active';
            }
        }
        session()->flash('message', 'Workflow status updated.');
    }

    public function render()
    {
        return view('livewire.admin.marketing.automation-engine')->layout('layouts.admin');
    }
}

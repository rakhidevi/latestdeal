<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use App\Services\Studio\StudioAPI;

#[Middleware('studio.admin')]
class AdminCenter extends Component
{
    protected StudioAPI $studio;

    public array $platformState = [];

    // Modals
    public bool $showKillSwitchModal = false;
    public string $killSwitchConfirmCode = '';
    
    public function boot(StudioAPI $studio)
    {
        $this->studio = $studio;
    }

    public function mount()
    {
        $this->refreshState();
    }

    public function refreshState()
    {
        $this->platformState = $this->studio->admin()->getPlatformState();
    }

    public function toggleControl(string $category, string $key, bool $currentState)
    {
        // Enforce RBAC (simulated via Auth check)
        if (!auth()->check() || !auth()->user()->can('toggle_platform_controls')) {
            session()->flash('error', 'Unauthorized action.');
            return;
        }

        $newState = !$currentState;
        $this->studio->admin()->toggleControl($category, $key, $newState, auth()->id());
        
        $this->platformState[$category][$key]['enabled'] = $newState;
        session()->flash('message', "Toggled {$this->platformState[$category][$key]['name']}.");
    }

    public function updateRollout(int $percentage)
    {
        if (!auth()->user()->can('manage_rollouts')) return;
        
        $this->studio->admin()->updateRollout($percentage, auth()->id());
        $this->platformState['rollout']['canary_percentage'] = $percentage;
        session()->flash('message', "Canary Rollout updated to {$percentage}%.");
    }

    public function triggerKillSwitch()
    {
        if ($this->killSwitchConfirmCode !== 'TERMINATE') {
            session()->flash('error', 'Invalid confirmation code. Kill switch aborted.');
            $this->showKillSwitchModal = false;
            return;
        }

        $this->studio->admin()->activateKillSwitch(auth()->id());
        $this->platformState['features']['kill_switch']['enabled'] = true;
        
        session()->flash('error', 'EMERGENCY: PLATFORM KILL SWITCH ACTIVATED. ALL WORKERS HALTED.');
        $this->showKillSwitchModal = false;
        $this->killSwitchConfirmCode = '';
    }

    public function render()
    {
        return view('livewire.admin.studio.admin-center')->layout('layouts.admin');
    }
}

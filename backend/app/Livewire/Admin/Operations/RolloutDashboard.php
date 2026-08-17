<?php

namespace App\Livewire\Admin\Operations;

use Livewire\Component;

class RolloutDashboard extends Component
{
    public $globalPercentage = 5;
    public $canaryStatus = 'ACTIVE';
    public $providerHealth = [
        'amazon' => 99.2,
        'flipkart' => 100.0,
    ];
    public $rollbackHistory = [];
    public $revenueTrend = 'Up 12%';
    
    public function render()
    {
        return view('livewire.admin.operations.rollout-dashboard')->layout('layouts.admin');
    }
}

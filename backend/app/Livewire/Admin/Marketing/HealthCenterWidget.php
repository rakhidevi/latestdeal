<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use App\Services\Marketing\HealthService;
use App\Livewire\Traits\AuthorizesMarketing;

class HealthCenterWidget extends Component
{
    use AuthorizesMarketing;

    public function render(HealthService $healthService)
    {
        $this->authorizeMarketing('marketing.health.view');
        
        return view('livewire.admin.marketing.health-center-widget', [
            'health' => $healthService->getMetrics()
        ]);
    }
}

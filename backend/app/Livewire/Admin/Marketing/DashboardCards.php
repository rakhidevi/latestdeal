<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use App\Services\Marketing\DashboardService;
use App\Livewire\Traits\AuthorizesMarketing;

class DashboardCards extends Component
{
    use AuthorizesMarketing;

    public function render(DashboardService $dashboardService)
    {
        $this->authorizeMarketing('marketing.dashboard.view');
        return view('livewire.admin.marketing.dashboard-cards', [
            'dashboard' => $dashboardService->getDashboard()
        ]);
    }
}

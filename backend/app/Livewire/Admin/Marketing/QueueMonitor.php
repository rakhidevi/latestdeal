<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use App\Services\Marketing\QueueMonitorService;
use App\Livewire\Traits\AuthorizesMarketing;

class QueueMonitor extends Component
{
    use AuthorizesMarketing;

    public function render(QueueMonitorService $queueMonitorService)
    {
        $this->authorizeMarketing('marketing.queue.view');
        
        return view('livewire.admin.marketing.queue-monitor', [
            'metrics' => $queueMonitorService->getMetrics()
        ]);
    }
}

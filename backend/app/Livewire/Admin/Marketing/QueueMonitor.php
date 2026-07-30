<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use App\Livewire\Traits\AuthorizesMarketing;
use Illuminate\Support\Facades\DB;

class QueueMonitor extends Component
{
    use AuthorizesMarketing;

    public function render()
    {
        $this->authorizeMarketing('marketing.queue.view');

        $metrics = [
            'jobs_waiting'    => DB::table('jobs')->count(),
            'jobs_failed'     => DB::table('failed_jobs')->count(),
            'marketing_jobs'  => DB::table('jobs')->where('queue', 'marketing_emails')->count(),
            'default_jobs'    => DB::table('jobs')->where('queue', 'default')->count(),
        ];

        return view('livewire.admin.marketing.queue-monitor', compact('metrics'));
    }
}

<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use App\Models\EmailCampaign;
use App\Livewire\Traits\AuthorizesMarketing;
use Illuminate\Support\Facades\DB;

class DashboardCards extends Component
{
    use AuthorizesMarketing;

    public function render()
    {
        $this->authorizeMarketing('marketing.dashboard.view');

        $metrics = [
            'active_campaigns'    => EmailCampaign::whereIn('status', ['Queued', 'Sending', 'running'])->count(),
            'scheduled_campaigns' => EmailCampaign::whereIn('status', ['Draft', 'draft', 'Scheduled', 'scheduled'])->count(),
            'total_campaigns'     => EmailCampaign::count(),
            'sent_today'          => EmailCampaign::whereDate('updated_at', today())->whereIn('status', ['Sent', 'sent', 'Completed', 'completed'])->count(),
        ];

        $queueHealth = [
            'jobs_waiting' => DB::table('jobs')->count(),
            'jobs_failed'  => DB::table('failed_jobs')->count(),
        ];

        return view('livewire.admin.marketing.dashboard-cards', compact('metrics', 'queueHealth'));
    }
}

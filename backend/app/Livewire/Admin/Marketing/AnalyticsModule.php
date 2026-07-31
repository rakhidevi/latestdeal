<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;

class AnalyticsModule extends Component
{
    public $timeRange = '30_days';
    public $campaignType = 'all';

    public function setTimeRange($range)
    {
        $this->timeRange = $range;
    }

    public function render()
    {
        // Dummy Data for the analytics UI
        $metrics = [
            'sent' => 14520,
            'delivered' => 14100,
            'opened' => 6842,
            'clicked' => 1240,
            'bounced' => 420,
            'unsubscribed' => 54,
        ];

        return view('livewire.admin.marketing.analytics-module', [
            'metrics' => $metrics
        ])->layout('admin.layout');
    }
}

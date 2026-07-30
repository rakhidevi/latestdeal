<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class QueueMonitor extends Component
{
    public function render()
    {
        $oldestJob = DB::table('jobs')->orderBy('created_at', 'asc')->first();
        $oldestTimestamp = $oldestJob ? $oldestJob->created_at : null;
        
        // Approximate latency in minutes based on oldest job
        $latency = 0;
        if ($oldestTimestamp) {
            $latency = max(0, round((time() - $oldestTimestamp) / 60));
        }

        $metrics = [
            'jobs_waiting'    => DB::table('jobs')->count(),
            'jobs_failed'     => DB::table('failed_jobs')->count(),
            'latency_mins'    => $latency,
            'throughput_min'  => rand(800, 1500), // Placeholder for aggregation
            'oldest_job_age'  => $latency > 0 ? $latency . 'm ago' : 'No delay',
            'active_workers'  => 4, // Placeholder for supervisor/horizon stats
        ];

        return view('livewire.admin.marketing.queue-monitor', compact('metrics'))
            ->extends('admin.layout')
            ->section('content');
    }
}

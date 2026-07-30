<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthCenter extends Component
{
    public function render()
    {
        // Hybrid Metrics Approach
        $metrics = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
        ];

        // Database Connectivity
        try {
            DB::connection()->getPdo();
            $metrics['db_status'] = 'healthy';
        } catch (\Exception $e) {
            $metrics['db_status'] = 'error';
        }

        // Cache Connectivity
        try {
            Cache::put('health_check', 'ok', 10);
            $metrics['cache_status'] = Cache::get('health_check') === 'ok' ? 'healthy' : 'error';
        } catch (\Exception $e) {
            $metrics['cache_status'] = 'error';
        }

        // Storage Check
        $metrics['storage_status'] = is_writable(storage_path()) ? 'healthy' : 'error';

        // Disk Space
        $totalDisk = disk_total_space('/');
        $freeDisk = disk_free_space('/');
        $usedDisk = $totalDisk - $freeDisk;
        $diskPercentage = $totalDisk > 0 ? round(($usedDisk / $totalDisk) * 100) : 0;
        
        $metrics['disk_percentage'] = $diskPercentage;
        $metrics['disk_status'] = $diskPercentage > 90 ? 'warning' : 'healthy';

        return view('livewire.admin.marketing.health-center', compact('metrics'))
            ->extends('admin.layout')
            ->section('content');
    }
}

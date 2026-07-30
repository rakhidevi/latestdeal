<?php

namespace App\Services\Marketing;

use App\DTOs\Marketing\HealthMetricsDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class HealthService
{
    public function getMetrics(): HealthMetricsDTO
    {
        return Cache::remember('marketing.health_metrics', 30, function () {
            
            $dbConnection = 'Disconnected';
            try {
                DB::connection()->getPdo();
                $dbConnection = 'Connected';
            } catch (\Exception $e) {}

            $cacheConnection = 'Disconnected';
            try {
                Cache::store()->get('ping');
                $cacheConnection = 'Connected';
            } catch (\Exception $e) {}
            
            $storageWritable = false;
            try {
                $storageWritable = is_writable(storage_path());
            } catch (\Exception $e) {}

            $schedulerLastRunDt = Cache::get('marketing.scheduler.last_run');
            $schedulerLastRun = $schedulerLastRunDt ? $schedulerLastRunDt->diffForHumans() : 'Never';
            $schedulerRunning = $schedulerLastRunDt && $schedulerLastRunDt->diffInMinutes(now()) < 10;
            
            $mailProvider = Setting::getValue('marketing.mail_provider', 'Sendmail');
            $rateLimit = Setting::getValue('marketing.rate_limit', '100 / hour');
            
            $mailLastSuccessDt = Cache::get('marketing.mail.last_success');
            $mailLastFailureDt = Cache::get('marketing.mail.last_failure');
            $mailConsecutiveFailures = Cache::get('marketing.mail.consecutive_failures', 0);
            
            $workerStatus = 'Offline';
            try {
                $activeWorkers = DB::table('worker_heartbeats')->where('last_heartbeat', '>=', now()->subMinutes(2))->count();
                $workerStatus = $activeWorkers > 0 ? 'Running' : 'Offline';
            } catch (\Exception $e) {}
            
            $diskUsage = 'Unknown';
            try {
                $diskFree = disk_free_space(storage_path());
                $diskTotal = disk_total_space(storage_path());
                if ($diskTotal > 0) {
                    $diskUsage = round((($diskTotal - $diskFree) / $diskTotal) * 100, 2) . '%';
                }
            } catch (\Exception $e) {}

            return new HealthMetricsDTO(
                workerStatus: $workerStatus,
                mailProvider: $mailProvider,
                rateLimit: $rateLimit,
                databaseConnection: $dbConnection,
                cacheConnection: $cacheConnection,
                queueConnection: 'Connected', // Handled heavily by queue monitor later
                storageWritable: $storageWritable,
                schedulerRunning: $schedulerRunning,
                schedulerLastRun: $schedulerLastRun,
                mailLastSuccess: $mailLastSuccessDt ? $mailLastSuccessDt->diffForHumans() : 'Never',
                mailLastFailure: $mailLastFailureDt ? $mailLastFailureDt->diffForHumans() : 'Never',
                mailConsecutiveFailures: $mailConsecutiveFailures,
                phpVersion: phpversion(),
                laravelVersion: app()->version(),
                diskUsage: $diskUsage,
                memoryUsage: round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                environment: app()->environment(),
                applicationVersion: config('app.version', '1.0.0')
            );
        });
    }
}

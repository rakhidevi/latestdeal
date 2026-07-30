<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TrackSchedulerHealthCommand extends Command
{
    protected $signature = 'marketing:track-scheduler-health';
    protected $description = 'Update the last run timestamp of the scheduler for health monitoring';

    public function handle()
    {
        Cache::put('marketing.scheduler.last_run', now());
        $this->info('Scheduler health tracked.');
    }
}

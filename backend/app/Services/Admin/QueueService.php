<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Artisan;

class QueueService
{
    public function workQueue()
    {
        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--max-jobs' => 10,
            '--max-time' => 10,
        ]);
    }

    public function clearFailedJobs()
    {
        Artisan::call('queue:flush');
    }
}

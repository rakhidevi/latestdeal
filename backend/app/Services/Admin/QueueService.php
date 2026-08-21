<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Artisan;

class QueueService
{
    public function workQueue()
    {
        Artisan::call('queue:work', ['--stop-when-empty' => true]);
    }

    public function clearFailedJobs()
    {
        Artisan::call('queue:flush');
    }
}

<?php

namespace App\Listeners\Marketing;

use Illuminate\Queue\Events\WorkerStarting;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkerEventSubscriber
{
    protected string ;

    public function __construct()
    {
        // Generate a unique name for this worker instance
        ->workerName = gethostname() . \'-\' . getmypid() . \'-\' . Str::random(6);
    }

    public function handleWorkerStarting(WorkerStarting ): void
    {
        DB::table(\'worker_heartbeats\')->insert([
            \'worker_name\' => ->workerName,
            \'queue\' => ->worker->name ?? \'default\',
            \'host\' => gethostname(),
            \'pid\' => getmypid(),
            \'last_heartbeat\' => now(),
            \'status\' => \'started\',
            \'memory_usage\' => memory_get_usage(true),
            \'laravel_version\' => app()->version(),
            \'app_version\' => config(\'app.version\', \'1.0.0\'),
            \'created_at\' => now(),
            \'updated_at\' => now()
        ]);
    }

    public function handleLooping(Looping ): void
    {
        DB::table(\'worker_heartbeats\')
            ->where(\'worker_name\', ->workerName)
            ->update([
                \'last_heartbeat\' => now(),
                \'status\' => \'running\',
                \'memory_usage\' => memory_get_usage(true),
                \'updated_at\' => now()
            ]);
    }

    public function handleWorkerStopping(WorkerStopping ): void
    {
        DB::table(\'worker_heartbeats\')
            ->where(\'worker_name\', ->workerName)
            ->update([
                \'status\' => \'stopped\',
                \'updated_at\' => now()
            ]);
    }

    public function subscribe(): array
    {
        return [
            WorkerStarting::class => \'handleWorkerStarting\',
            Looping::class => \'handleLooping\',
            WorkerStopping::class => \'handleWorkerStopping\',
        ];
    }
}

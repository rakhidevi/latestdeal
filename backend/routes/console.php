<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// REQ-OPS-003: Maintenance Commands
Artisan::command('deals:prune {--days=30}', function () {
    $days = $this->option('days');
    $count = \App\Models\Deal::where('status', 'expired')
        ->where('updated_at', '<', now()->subDays($days))
        ->delete();
    $this->info("Pruned {$count} expired deals older than {$days} days.");
})->purpose('Prune expired deals');

// Schedule the commands
Schedule::command('deals:prune --days=30')->daily();
Schedule::command('queue:prune-failed --hours=168')->daily();

// Deal Automation Scripts
Schedule::command(\App\Console\Commands\PublishDealsCommand::class)->hourly();
Schedule::command(\App\Console\Commands\ExpireDealsCommand::class)->daily();
Schedule::command(\App\Console\Commands\CheckDeadLinksCommand::class)->twiceDaily(1, 13);
Schedule::command(\App\Console\Commands\AutoHuntCommand::class)->cron('0 */1 * * *'); // Run every 1 hour (closest to 1.5 without complex crons)

// Aggressive Image Pruning
Artisan::command('images:prune {--hours=72}', function () {
    $hours = $this->option('hours');
    $files = \Illuminate\Support\Facades\Storage::disk('public')->files('deals');
    $count = 0;
    foreach ($files as $file) {
        $lastModified = \Illuminate\Support\Facades\Storage::disk('public')->lastModified($file);
        if ($lastModified < now()->subHours($hours)->timestamp) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
            $count++;
        }
    }
    $this->info("Pruned {$count} images older than {$hours} hours.");
})->purpose('Prune old deal images to save disk space');

Schedule::command('images:prune --hours=72')->daily();

// Shared Hosting Queue Worker Workaround
// Runs the queue worker every minute and stops when empty.
// This requires the standard cPanel cron (* * * * * php artisan schedule:run) to be active.
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();

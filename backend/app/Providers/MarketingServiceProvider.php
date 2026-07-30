<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use App\Listeners\Marketing\WorkerEventSubscriber;
use App\Contracts\QueueProviderInterface;
use App\Services\Queue\DatabaseQueueProvider;

class MarketingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(QueueProviderInterface::class, function () {
            // Fallback to database for now.
            return new DatabaseQueueProvider();
        });
    }

    public function boot(): void
    {
        Event::subscribe(WorkerEventSubscriber::class);
        Event::listen(\App\Events\Marketing\CampaignStateChanged::class, \App\Listeners\Marketing\CampaignStateAuditListener::class);

        // Define Marketing Gates
        Gate::define('marketing.dashboard.view', fn($user) => true); // Stubbed to true for MVP
        Gate::define('marketing.health.view', fn($user) => true);
        Gate::define('marketing.queue.view', fn($user) => true);
        Gate::define('marketing.queue.manage', fn($user) => true);
        Gate::define('marketing.campaign.create', fn($user) => true);
        Gate::define('marketing.settings.edit', fn($user) => true);
    }
}

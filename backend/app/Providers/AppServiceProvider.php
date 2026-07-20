<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\DealDiscovered::class,
            \App\Listeners\ProcessDiscoveredDeal::class,
        );
        
        // Also register DealIngested legacy chain just in case
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\DealIngested::class,
            \App\Listeners\CheckPriceAlerts::class,
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\DealIngested::class,
            \App\Listeners\CheckPublisherRules::class,
        );
    }
}

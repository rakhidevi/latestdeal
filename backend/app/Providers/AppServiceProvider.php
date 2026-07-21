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
        // Domain Event Listener Registrations
        \Illuminate\Support\Facades\Event::listen(
            [\App\Events\DealCreated::class, \App\Events\DealUpdated::class],
            [\App\Listeners\DiscountCalculatorListener::class, 'handle']
        );
        \Illuminate\Support\Facades\Event::listen(
            [\App\Events\DealCreated::class, \App\Events\DealUpdated::class],
            [\App\Listeners\BrandSyncListener::class, 'handle']
        );
        \Illuminate\Support\Facades\Event::listen(
            [\App\Events\DealCreated::class, \App\Events\DealDeleted::class],
            [\App\Listeners\CatalogCounterListener::class, 'handle']
        );
        \Illuminate\Support\Facades\Event::listen(
            [\App\Events\DealCreated::class, \App\Events\DealUpdated::class, \App\Events\DealDeleted::class],
            [\App\Listeners\NavigationCacheListener::class, 'handle']
        );

        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $view->with('nav', app(\App\Services\NavigationService::class)->getNavigationTree());
        });
        
        \Illuminate\Support\Facades\View::composer('welcome', function ($view) {
            $view->with('categories', \App\Models\Category::where('deal_count', '>', 0)->limit(8)->get());
        });
    }
}

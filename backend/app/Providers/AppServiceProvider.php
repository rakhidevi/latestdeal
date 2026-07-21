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
        // Auto-run pending migrations in web environment if new schema columns are missing
        if (!\Illuminate\Support\Facades\App::runningInConsole()) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('categories') && !\Illuminate\Support\Facades\Schema::hasColumn('categories', 'deal_count')) {
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                }
            } catch (\Throwable $e) {
                // Log exception silently without breaking request execution
                \Illuminate\Support\Facades\Log::warning('Auto-migration failed: ' . $e->getMessage());
            }
        }

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
            $catQuery = \App\Models\Category::where('slug', '!=', 'general')->where('name', '!=', 'General');
            if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'deal_count')) {
                $catQuery->orderBy('deal_count', 'desc');
            }
            $categories = $catQuery->take(7)->get();
            $view->with('categories', $categories);
        });
    }
}

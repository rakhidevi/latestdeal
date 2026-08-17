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
        $this->app->bind(
            \App\Contracts\MailProviderInterface::class,
            \App\Services\Mail\SendmailProvider::class
        );

        $this->app->bind(
            \App\Contracts\Communications\AssetStorageInterface::class,
            \App\Services\Communications\Storage\LocalStorageProvider::class
        );
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
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\UserInteracted::class,
            [\App\Listeners\RecordInteractionAnalytics::class, 'handle']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\UserInteracted::class,
            [\App\Listeners\UpdateRecommendationProfile::class, 'handle']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\UserInteracted::class,
            [\App\Listeners\TriggerRealTimeNotifications::class, 'handle']
        );

        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $view->with('nav', app(\App\Services\NavigationService::class)->getNavigationTree());
        });
        
        \Illuminate\Support\Facades\View::composer('welcome', function ($view) {
            try {
                // Cache the homepage data to reduce database load on every visit.
                $welcomeData = \Illuminate\Support\Facades\Cache::remember('welcome_page_data', 300, function () {
                    $categoriesTableExists = \Illuminate\Support\Facades\Schema::hasTable('categories');
                    $dealsTableExists = \Illuminate\Support\Facades\Schema::hasTable('deals');

                    $categories = collect();
                    if ($categoriesTableExists) {
                        $catQuery = \App\Models\Category::where('slug', '!=', 'general')->where('name', '!=', 'General');
                        if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'deal_count')) {
                            $catQuery->orderBy('deal_count', 'desc');
                        }
                        $categories = $catQuery->take(7)->get();
                    }

                    $heroDeals = collect();
                    if ($dealsTableExists) {
                        $heroDeals = \App\Models\Deal::where('status', 'active')
                            ->where('discounted_price', '>', 0)
                            ->with(['merchant', 'category'])
                            ->orderBy('ai_score', 'desc')
                            ->orderBy('clicks_count', 'desc')
                            ->take(6)
                            ->get();
                    }

                    return ['categories' => $categories, 'heroDeals' => $heroDeals];
                });

                $view->with('categories', $welcomeData['categories']);
                $view->with('heroDeals', $welcomeData['heroDeals']);
            } catch (\Throwable $e) {
                // If queries fail, log the error and return empty collections to prevent site crash.
                \Illuminate\Support\Facades\Log::error('Failed to load welcome page data: ' . $e->getMessage());
                $view->with('categories', collect());
                $view->with('heroDeals', collect());
            }
        });
    }
}

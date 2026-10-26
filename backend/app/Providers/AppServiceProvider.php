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
        $this->configureDynamicMail();

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
                        $catQuery = \App\Models\Category::whereNotIn('slug', ['general', 'uncategorized', 'all-other-categories'])
                            ->whereNotIn('name', ['General', 'Uncategorized', 'uncategorized', 'All Other Categories']);
                        if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'deal_count')) {
                            $catQuery->where('deal_count', '>', 0)->orderBy('deal_count', 'desc');
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
    /**
     * Dynamically override mail configuration from database settings if configured by admin.
     */
    protected function configureDynamicMail(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $mailSettings = \App\Models\Setting::where('category', 'Mail')->pluck('value', 'key')->toArray();

                if (!empty($mailSettings)) {
                    $driver = !empty($mailSettings['mail_mailer']) ? $mailSettings['mail_mailer'] : 'smtp';
                    config(['mail.default' => $driver]);

                    if (!empty($mailSettings['mail_host'])) {
                        config(['mail.mailers.smtp.host' => $mailSettings['mail_host']]);
                    }
                    if (!empty($mailSettings['mail_port'])) {
                        config(['mail.mailers.smtp.port' => (int) $mailSettings['mail_port']]);
                    }
                    if (!empty($mailSettings['mail_username'])) {
                        config(['mail.mailers.smtp.username' => $mailSettings['mail_username']]);
                    }
                    if (!empty($mailSettings['mail_password'])) {
                        config(['mail.mailers.smtp.password' => $mailSettings['mail_password']]);
                    }
                    if (!empty($mailSettings['mail_encryption'])) {
                        $enc = strtolower(trim($mailSettings['mail_encryption']));
                        config([
                            'mail.mailers.smtp.scheme' => ($enc === 'ssl') ? 'smtps' : null,
                            'mail.mailers.smtp.encryption' => ($enc === 'tls') ? 'tls' : (($enc === 'ssl') ? 'ssl' : null),
                        ]);
                    }
                    if (!empty($mailSettings['mail_from_address'])) {
                        config(['mail.from.address' => $mailSettings['mail_from_address']]);
                    }
                    if (!empty($mailSettings['mail_from_name'])) {
                        config(['mail.from.name' => $mailSettings['mail_from_name']]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fail gracefully during migrations or install
        }
    }

}

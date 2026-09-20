<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedirectController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\ShopperAuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');


// Fallback for old integer IDs — serve directly, NO redirects (avoids infinite loops)
Route::get('/go/{id}', function (\Illuminate\Http\Request $request, $id) {
    $deal = \App\Models\Deal::findOrFail($id);
    return app(\App\Http\Controllers\RedirectController::class)->redirect($request, $deal);
})->where('id', '[0-9]+');

Route::get('/deal/{id}', function ($id) {
    $deal = \App\Models\Deal::findOrFail($id);
    return app(\App\Http\Controllers\DealController::class)->show($deal);
})->where('id', '[0-9]+');

// The Redirect Engine Endpoint
Route::get('/go/{deal:hash_id}', [\App\Http\Controllers\RedirectController::class, 'redirect'])->name('deal.redirect');

// Deal Detail Page
Route::get('/deal/{deal:slug}', [\App\Http\Controllers\DealController::class, 'show'])->name('deal.show');

// Compliance & Information Pages
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/privacy-policy', 'pages.privacy')->name('privacy');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/affiliate-disclosure', 'pages.disclosure')->name('affiliate.disclosure');

// AI Shopping Assistant
Route::get('/assistant', function () {
    $deals = \Illuminate\Support\Facades\Cache::remember('deals.assistant', 300, function () {
        return \App\Models\Deal::with(['merchant', 'category'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(120)
            ->get()
            ->map(function ($deal) {
                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'price' => (float) $deal->discounted_price,
                    'original_price' => (float) $deal->original_price,
                    'discount_pct' => $deal->original_price > 0 ? round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) : 0,
                    'url' => $deal->url,
                    'image_url' => $deal->image_url,
                    'image_path' => $deal->image_url,
                    'merchant' => $deal->merchant->name ?? 'Marketplace',
                    'category' => $deal->category->name ?? 'General',
                ];
            });
    });

    return view('shopper.assistant', compact('deals'));
})->name('shopper.assistant');

// The frontend Vue/Blade entrypoint
use App\Http\Controllers\Frontend\BrowseController;

// Directory Routes (View All)
Route::get('/categories', [\App\Http\Controllers\DirectoryController::class, 'categories'])->name('directory.categories');
Route::get('/brands', [\App\Http\Controllers\DirectoryController::class, 'brands'])->name('directory.brands');
Route::get('/merchants', [\App\Http\Controllers\DirectoryController::class, 'merchants'])->name('directory.merchants');

// SEO Routing
Route::get('/', [BrowseController::class, 'index'])->name('home');
Route::get('/deal/{slug}', [BrowseController::class, 'show'])->name('deals.show');
Route::get('/categories/{slug}', [BrowseController::class, 'byCategory'])->name('deals.category');
Route::get('/brands/{slug}', [BrowseController::class, 'byBrand'])->name('deals.brand');
Route::get('/merchants/{slug}', [BrowseController::class, 'byMerchant'])->name('deals.merchant');
Route::get('/deals/{range}', [BrowseController::class, 'byDiscount'])->name('deals.discount');
Route::get('/author/{slug}', [\App\Http\Controllers\Frontend\AuthorController::class, 'show'])->name('author.show');

// --- SEO & Static Pages ---
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);

// Trust Pages (Phase 1)
Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/terms', 'terms')->name('terms');
Route::view('/cookie-policy', 'cookie-policy')->name('cookie');
Route::view('/editorial-policy', 'editorial-policy')->name('editorial.policy');
Route::view('/how-it-works', 'how-it-works')->name('how.it.works');
Route::view('/affiliate-disclosure', 'affiliate-disclosure')->name('affiliate.disclosure');
Route::view('/editorial-team', 'editorial-team')->name('editorial.team');

// --- Phase 4 & Phase 9: Editorial Content Hub ---
use App\Http\Controllers\ArticleController;
Route::get('/guides', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/guides/{slug}', [ArticleController::class, 'show'])->name('articles.show');









// One-time env setup for AI keys







// Temporary: Fix APP_URL in production .env so images load correctly



// Newsletter Subscription
Route::post('/subscribe', [\App\Http\Controllers\Api\SubscriptionController::class, 'store'])->name('subscribe');

// Price Alerts
Route::post('/price-alerts', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|email',
        'keyword' => 'required|string',
        'price' => 'required|numeric'
    ]);

    $subscriber = \App\Models\Subscriber::firstOrCreate(['email' => $request->email]);
    
    \App\Models\PriceAlert::create([
        'subscriber_id' => $subscriber->id,
        'keyword' => $request->keyword,
        'target_price' => $request->price
    ]);

    if (auth()->check()) {
        app(\App\Services\User\InteractionService::class)->record('price_alert_created', 'dashboard', null, [
            'keyword' => $request->keyword,
            'target_price' => $request->price
        ]);
    }

    return back()->with('success', 'Price alert set successfully!');
});

// Publisher Auth Module
Route::get('/publisher/login', [\App\Http\Controllers\PublisherAuthController::class, 'loginView'])->name('login'); // Wait, named login might conflict with shopper login if we don't separate guards, but we'll use same guard.
Route::post('/publisher/login', [\App\Http\Controllers\PublisherAuthController::class, 'login']);
Route::get('/publisher/register', [\App\Http\Controllers\PublisherAuthController::class, 'registerView']);
Route::post('/publisher/register', [\App\Http\Controllers\PublisherAuthController::class, 'register']);

// Shopper Auth Module
Route::get('/login', [\App\Http\Controllers\ShopperAuthController::class, 'loginView'])->name('shopper.login');
Route::post('/login', [\App\Http\Controllers\ShopperAuthController::class, 'login']);
Route::get('/register', [\App\Http\Controllers\ShopperAuthController::class, 'registerView'])->name('shopper.register');
Route::post('/register', [\App\Http\Controllers\ShopperAuthController::class, 'register']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\ShopperAuthController::class, 'dashboard'])->name('shopper.dashboard');
    Route::post('/logout', [\App\Http\Controllers\ShopperAuthController::class, 'logout'])->name('logout');
    
    // GDPR Account Deletion
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Saved Deals logic
    Route::post('/deals/{deal}/save', [\App\Http\Controllers\DealController::class, 'saveDeal'])->name('deal.save');

    // Manage Price Alerts
    Route::delete('/price-alerts/{id}', function (\Illuminate\Http\Request $request, $id) {
        $user = \Illuminate\Support\Facades\Auth::user();
        $alert = \App\Models\PriceAlert::whereHas('subscriber', function($q) use ($user) {
            $q->where('email', $user->email);
        })->findOrFail($id);
        
        $alert->delete();
        return back()->with('success', 'Price alert removed.');
    })->name('price-alerts.destroy');
    
    // Watchlist
    Route::post('/watchlist/toggle', [\App\Http\Controllers\ShopperAuthController::class, 'toggleWatchlist'])->name('watchlist.toggle');

    Route::get('/publisher/dashboard', [\App\Http\Controllers\PublisherAuthController::class, 'dashboard']);
    Route::post('/publisher/logout', [\App\Http\Controllers\PublisherAuthController::class, 'logout']);
    
    // API Tokens
    Route::post('/publisher/tokens', [\App\Http\Controllers\PublisherTokenController::class, 'store'])->name('publisher.tokens.store');
    Route::delete('/publisher/tokens/{id}', [\App\Http\Controllers\PublisherTokenController::class, 'destroy'])->name('publisher.tokens.destroy');
    
    // Publisher Rules
    Route::post('/publisher/rules', [\App\Http\Controllers\PublisherRuleController::class, 'store'])->name('publisher.rules.store');
    Route::delete('/publisher/rules/{rule}', [\App\Http\Controllers\PublisherRuleController::class, 'destroy'])->name('publisher.rules.destroy');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/insights', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.insights');
    Route::get('/catalog/health', [\App\Http\Controllers\Admin\CatalogHealthController::class, 'show'])->name('admin.catalog.health');

    // Deal Management (Full CRUD)
    Route::get('/deals', [\App\Http\Controllers\Admin\DealController::class, 'index'])->name('admin.deals');
    Route::get('/deals/create', [\App\Http\Controllers\Admin\DealController::class, 'create'])->name('admin.deals.create');
    Route::post('/deals', [\App\Http\Controllers\Admin\DealController::class, 'store'])->name('admin.deals.store');
    Route::get('/deals/{deal}/edit', [\App\Http\Controllers\Admin\DealController::class, 'edit'])->name('admin.deals.edit');
    Route::put('/deals/{deal}', [\App\Http\Controllers\Admin\DealController::class, 'update'])->name('admin.deals.update');
    Route::post('/deals/{deal}/duplicate', [\App\Http\Controllers\Admin\DealController::class, 'duplicate'])->name('admin.deals.duplicate');
    Route::put('/deals/{deal}/status', [\App\Http\Controllers\Admin\DealController::class, 'updateStatus'])->name('admin.deals.status');
    Route::delete('/deals/{deal}', [\App\Http\Controllers\Admin\DealController::class, 'destroy'])->name('admin.deals.destroy');
    Route::delete('/deals-purge-illegal', [\App\Http\Controllers\Admin\DealController::class, 'purgeIllegal'])->name('admin.deals.purge-illegal');

    // Admin Review Queue
    Route::get('/deals/review-queue', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'index'])->name('admin.deals.review-queue');
    Route::post('/deals/review-queue/{id}/approve', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'approve'])->name('admin.deals.approve');
    Route::post('/deals/review-queue/{id}/reject', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'reject'])->name('admin.deals.reject');
    Route::post('/deals/review-queue/{id}/regenerate', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'regenerate'])->name('admin.deals.regenerate');

    // Taxonomy: Categories
    Route::get('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('admin.categories');
    Route::post('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Taxonomy: Brands
    Route::get('/brands', [\App\Http\Controllers\Admin\BrandController::class, 'index'])->name('admin.brands');
    Route::post('/brands', [\App\Http\Controllers\Admin\BrandController::class, 'store'])->name('admin.brands.store');
    Route::put('/brands/{brand}', [\App\Http\Controllers\Admin\BrandController::class, 'update'])->name('admin.brands.update');
    Route::delete('/brands/{brand}', [\App\Http\Controllers\Admin\BrandController::class, 'destroy'])->name('admin.brands.destroy');

    // Marketing & Newsletters (Working System)
    Route::prefix('marketing')->name('admin.marketing.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\NewsletterController::class, 'dispatchView'])->name('dashboard');
        Route::get('/campaigns', [\App\Http\Controllers\Admin\NewsletterController::class, 'dispatchView'])->name('campaigns');
        Route::post('/campaigns/trigger', [\App\Http\Controllers\Admin\NewsletterController::class, 'triggerCampaign'])->name('campaigns.trigger');
        Route::get('/templates', [\App\Http\Controllers\Admin\NewsletterController::class, 'templates'])->name('templates');
        Route::get('/templates/preview/{templateKey}', [\App\Http\Controllers\Admin\NewsletterController::class, 'previewPage'])->name('templates.preview');
        Route::get('/templates/render/{templateKey}', [\App\Http\Controllers\Admin\NewsletterController::class, 'renderPreviewHtml'])->name('templates.render');
        Route::post('/templates/test', [\App\Http\Controllers\Admin\NewsletterController::class, 'sendTestEmail'])->name('templates.test');
        Route::get('/subscribers', [\App\Http\Controllers\Admin\NewsletterController::class, 'subscribers'])->name('subscribers');
        Route::get('/subscribers/export', [\App\Http\Controllers\Admin\NewsletterController::class, 'exportSubscribers'])->name('subscribers.export');
        Route::post('/subscribers/{id}/toggle', [\App\Http\Controllers\Admin\NewsletterController::class, 'toggleSubscriber'])->name('subscribers.toggle');
        Route::delete('/subscribers/{id}', [\App\Http\Controllers\Admin\NewsletterController::class, 'destroySubscriber'])->name('subscribers.destroy');
    });

    // User Intelligence Center (UIC)
    Route::prefix('uic')->name('admin.uic.')->group(function () {
        Route::get('/user-intelligence', [\App\Http\Controllers\Admin\UicController::class, 'userIntelligence'])->name('user-intelligence');
        Route::get('/user-detail/{uuid}', [\App\Http\Controllers\Admin\UicController::class, 'userDetail'])->name('user-detail');
        Route::get('/traffic-sources', [\App\Http\Controllers\Admin\UicController::class, 'trafficSources'])->name('traffic-sources');
        Route::get('/ai-conversations', [\App\Http\Controllers\Admin\UicController::class, 'aiConversations'])->name('ai-conversations');
        Route::get('/affiliate-analytics', [\App\Http\Controllers\Admin\UicController::class, 'affiliateAnalytics'])->name('affiliate-analytics');
        Route::get('/search-analytics', [\App\Http\Controllers\Admin\UicController::class, 'searchAnalytics'])->name('search-analytics');
        Route::get('/conversion-funnel', [\App\Http\Controllers\Admin\UicController::class, 'conversionFunnel'])->name('conversion-funnel');
        Route::get('/geographic-insights', [\App\Http\Controllers\Admin\UicController::class, 'geographicInsights'])->name('geographic-insights');
    });
    
    // Scraper Operations
    Route::get('/actions', [\App\Http\Controllers\Admin\ScraperController::class, 'actions'])->name('admin.actions');
    Route::post('/actions/run', [\App\Http\Controllers\Admin\ScraperController::class, 'runAction'])->name('admin.actions.run');
    Route::post('/scraper/start', [\App\Http\Controllers\Admin\ScraperController::class, 'startScraper'])->name('admin.scraper.start');
    Route::post('/scraper/stop', [\App\Http\Controllers\Admin\ScraperController::class, 'stopScraper'])->name('admin.scraper.stop');
    Route::get('/scraper/status', [\App\Http\Controllers\Admin\ScraperController::class, 'scraperStatus'])->name('admin.scraper.status');
    Route::post('/scraper/scrape', [\App\Http\Controllers\Admin\ScraperController::class, 'scrapeUrl'])->name('admin.scraper.scrape');
    Route::post('/scraper/hunt', [\App\Http\Controllers\Admin\ScraperController::class, 'customHunt'])->name('admin.scraper.hunt');
    
    // Settings & System
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings');
    Route::post('/settings/save', [\App\Http\Controllers\Admin\SettingController::class, 'save'])->name('admin.settings.save');
    Route::post('/settings/toggle', [\App\Http\Controllers\Admin\SettingController::class, 'toggle'])->name('admin.settings.toggle');
    Route::post('/settings/test-smtp', [\App\Http\Controllers\Admin\SettingController::class, 'testSmtp'])->name('admin.settings.test-smtp');

    // Merchants
    Route::get('/merchants', [\App\Http\Controllers\Admin\MerchantController::class, 'index'])->name('admin.merchants');
    Route::post('/merchants', [\App\Http\Controllers\Admin\MerchantController::class, 'store'])->name('admin.merchants.store');
    Route::put('/merchants/{merchant}', [\App\Http\Controllers\Admin\MerchantController::class, 'update'])->name('admin.merchants.update');
    Route::delete('/merchants/{merchant}', [\App\Http\Controllers\Admin\MerchantController::class, 'destroy'])->name('admin.merchants.destroy');
    
    // Discovery Profiles
    Route::get('/discovery-profiles', [\App\Http\Controllers\Admin\DiscoveryProfileController::class, 'index'])->name('admin.discovery-profiles');
    Route::post('/discovery-profiles', [\App\Http\Controllers\Admin\DiscoveryProfileController::class, 'store'])->name('admin.discovery-profiles.store');
    Route::put('/discovery-profiles/{profile}', [\App\Http\Controllers\Admin\DiscoveryProfileController::class, 'update'])->name('admin.discovery-profiles.update');
    Route::delete('/discovery-profiles/{profile}', [\App\Http\Controllers\Admin\DiscoveryProfileController::class, 'destroy'])->name('admin.discovery-profiles.destroy');
    Route::put('/discovery-profiles/{profile}/toggle', [\App\Http\Controllers\Admin\DiscoveryProfileController::class, 'toggle'])->name('admin.discovery-profiles.toggle');

    // Users
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    
    // Links & Social
    Route::get('/links', [\App\Http\Controllers\Admin\LinkController::class, 'index'])->name('admin.links');
    Route::post('/links/generate', [\App\Http\Controllers\Admin\LinkController::class, 'generate'])->name('admin.links.generate');
    Route::get('/social-accounts', [\App\Http\Controllers\Admin\SocialAccountController::class, 'index'])->name('admin.social-accounts');
    Route::post('/social-accounts', [\App\Http\Controllers\Admin\SocialAccountController::class, 'store'])->name('admin.social-accounts.store');
    Route::delete('/social-accounts/{socialAccount}', [\App\Http\Controllers\Admin\SocialAccountController::class, 'destroy'])->name('admin.social-accounts.delete');
    Route::put('/social-accounts/{socialAccount}/toggle', [\App\Http\Controllers\Admin\SocialAccountController::class, 'toggle'])->name('admin.social-accounts.toggle');

    // Queue Utilities
    Route::post('/queue/work', [\App\Http\Controllers\Admin\QueueController::class, 'work'])->name('admin.queue.work');
    Route::post('/queue/clear', [\App\Http\Controllers\Admin\QueueController::class, 'clear'])->name('admin.queue.clear');

    // Admin maintenance routes
    Route::match(['get', 'post'], '/clear-cache', function() {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        return back()->with('success', 'Application cache cleared successfully.');
    })->name('admin.clear-cache');
});

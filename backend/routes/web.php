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

Route::get('/setup-scraper', function () {
    \App\Models\Category::firstOrCreate(['id' => 1], ['name' => 'Electronics', 'slug' => 'electronics']);
    \App\Models\Merchant::firstOrCreate(['id' => 1], [
        'name' => 'Amazon', 
        'domain' => 'amazon.in',
        'affiliate_param_key' => 'tag',
        'store_id' => 'kridaymart-21'
    ]);
    return "Category #1 and Merchant #1 created! Your Python Worker will now work perfectly.";
});


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

// Operations & Catalog Health Dashboard
Route::get('/admin/catalog/health', [\App\Http\Controllers\Admin\CatalogHealthController::class, 'show'])->name('admin.catalog.health');








// One-time env setup for AI keys






Route::get('/clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    return "Cache cleared.";
});

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

    // User Intelligence Center (UIC) Platform Routes
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
    
    Route::get('/actions', [\App\Http\Controllers\Admin\ScraperController::class, 'actions'])->name('admin.actions');
    Route::post('/actions/run', [\App\Http\Controllers\Admin\ScraperController::class, 'runAction'])->name('admin.actions.run');
    
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings');
    Route::post('/settings/save', [\App\Http\Controllers\Admin\SettingController::class, 'save'])->name('admin.settings.save');
    Route::post('/settings/toggle', [\App\Http\Controllers\Admin\SettingController::class, 'toggle'])->name('admin.settings.toggle');
    
    Route::get('/deals', [\App\Http\Controllers\Admin\DealController::class, 'index'])->name('admin.deals');
    Route::put('/deals/{deal}/status', [\App\Http\Controllers\Admin\DealController::class, 'updateStatus'])->name('admin.deals.status');
    Route::delete('/deals/{deal}', [\App\Http\Controllers\Admin\DealController::class, 'destroy'])->name('admin.deals.destroy');
    Route::delete('/deals-purge-illegal', [\App\Http\Controllers\Admin\DealController::class, 'purgeIllegal'])->name('admin.deals.purge-illegal');
    
    // Phase 11.4 - Admin Review Queue
    Route::get('/deals/review-queue', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'index'])->name('admin.deals.review-queue');
    Route::post('/deals/review-queue/{id}/approve', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'approve'])->name('admin.deals.approve');
    Route::post('/deals/review-queue/{id}/reject', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'reject'])->name('admin.deals.reject');
    Route::post('/deals/review-queue/{id}/regenerate', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'regenerate'])->name('admin.deals.regenerate');

    Route::get('/merchants', [\App\Http\Controllers\Admin\MerchantController::class, 'index'])->name('admin.merchants');
    Route::post('/merchants', [\App\Http\Controllers\Admin\MerchantController::class, 'store'])->name('admin.merchants.store');
    Route::put('/merchants/{merchant}', [\App\Http\Controllers\Admin\MerchantController::class, 'update'])->name('admin.merchants.update');
    
    // Phase 13 - Discovery Profiles
    Route::get('/discovery-profiles', [\App\Http\Controllers\Admin\DiscoveryProfileController::class, 'index'])->name('admin.discovery-profiles');
    Route::post('/discovery-profiles', [\App\Http\Controllers\Admin\DiscoveryProfileController::class, 'store'])->name('admin.discovery-profiles.store');
    Route::put('/discovery-profiles/{profile}', [\App\Http\Controllers\Admin\DiscoveryProfileController::class, 'update'])->name('admin.discovery-profiles.update');
    Route::delete('/discovery-profiles/{profile}', [\App\Http\Controllers\Admin\DiscoveryProfileController::class, 'destroy'])->name('admin.discovery-profiles.destroy');
    Route::put('/discovery-profiles/{profile}/toggle', [\App\Http\Controllers\Admin\DiscoveryProfileController::class, 'toggle'])->name('admin.discovery-profiles.toggle');

    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    
    Route::get('/links', [\App\Http\Controllers\Admin\LinkController::class, 'index'])->name('admin.links');
    Route::post('/links/generate', [\App\Http\Controllers\Admin\LinkController::class, 'generate'])->name('admin.links.generate');
    
    Route::post('/queue/work', [\App\Http\Controllers\Admin\QueueController::class, 'work'])->name('admin.queue.work');
    Route::post('/queue/clear', [\App\Http\Controllers\Admin\QueueController::class, 'clear'])->name('admin.queue.clear');
    
    Route::post('/scraper/start', [\App\Http\Controllers\Admin\ScraperController::class, 'startScraper'])->name('admin.scraper.start');
    Route::post('/scraper/stop', [\App\Http\Controllers\Admin\ScraperController::class, 'stopScraper'])->name('admin.scraper.stop');
    Route::get('/scraper/status', [\App\Http\Controllers\Admin\ScraperController::class, 'scraperStatus'])->name('admin.scraper.status');
    Route::post('/scraper/scrape', [\App\Http\Controllers\Admin\ScraperController::class, 'scrapeUrl'])->name('admin.scraper.scrape');
    Route::post('/scraper/hunt', [\App\Http\Controllers\Admin\ScraperController::class, 'customHunt'])->name('admin.scraper.hunt');

    Route::get('/social-accounts', [\App\Http\Controllers\Admin\SocialAccountController::class, 'index'])->name('admin.social-accounts');
    Route::post('/social-accounts', [\App\Http\Controllers\Admin\SocialAccountController::class, 'store'])->name('admin.social-accounts.store');
    Route::delete('/social-accounts/{socialAccount}', [\App\Http\Controllers\Admin\SocialAccountController::class, 'destroy'])->name('admin.social-accounts.delete');
    Route::put('/social-accounts/{socialAccount}/toggle', [\App\Http\Controllers\Admin\SocialAccountController::class, 'toggle'])->name('admin.social-accounts.toggle');

    // Marketing Center
    Route::prefix('marketing')->name('admin.marketing.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MarketingController::class, 'dashboard'])->name('dashboard');
        Route::get('/campaigns', [\App\Http\Controllers\Admin\MarketingController::class, 'campaigns'])->name('campaigns');
        
        // Modules (Placeholders for now)
        Route::get('/templates', \App\Livewire\Admin\Marketing\TemplateLibrary::class)->name('templates');
        Route::get('/templates/create', \App\Livewire\Admin\Marketing\TemplateEditor::class)->name('templates.create');
        Route::get('/templates/{id}/edit', \App\Livewire\Admin\Marketing\TemplateEditor::class)->name('templates.edit');
        Route::get('/themes', \App\Livewire\Admin\Marketing\ThemesModule::class)->name('themes');
        Route::get('/assets', \App\Livewire\Admin\Marketing\AssetsModule::class)->name('assets');
        Route::get('/subscribers', \App\Livewire\Admin\Marketing\SubscribersModule::class)->name('subscribers');
        Route::get('/segments', \App\Livewire\Admin\Marketing\SegmentsModule::class)->name('segments');
        Route::get('/analytics', \App\Livewire\Admin\Marketing\AnalyticsModule::class)->name('analytics');
        Route::get('/preview-center', \App\Livewire\Admin\Marketing\PreviewCenter::class)->name('preview-center');
        Route::get('/module/{module}', [\App\Http\Controllers\Admin\MarketingController::class, 'placeholder'])->name('placeholder');

        
        // Operations
        Route::get('/health', \App\Livewire\Admin\Marketing\HealthCenter::class)->name('health');
        Route::get('/queue', \App\Livewire\Admin\Marketing\QueueMonitor::class)->name('queue');
        Route::get('/timeline', [\App\Http\Controllers\Admin\MarketingController::class, 'placeholder'])->defaults('module', 'activity-timeline')->name('timeline');
        Route::get('/audit', [\App\Http\Controllers\Admin\MarketingController::class, 'placeholder'])->defaults('module', 'audit-logs')->name('audit');
        
        Route::get('/settings', [\App\Http\Controllers\Admin\MarketingController::class, 'settings'])->name('settings');
    });
});

// Setup Route for initializing SQLite Database on Server




// Direct storage file server route for production hosts where public/storage symlink is missing
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . ltrim($path, '/'));
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        // Fallback placeholder image
        $placeholder = public_path('images/logo.png');
        if (file_exists($placeholder)) {
            return response()->file($placeholder, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-cache',
            ]);
        }
        abort(404);
    }
    $mimeType = @mime_content_type($fullPath) ?: 'image/jpeg';
    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*');
Route::get('/admin/studio/knowledge-center', function () { return 'dummy'; })->name('admin.studio.knowledge-center');
Route::get('/debug-latest-deals', function() { return \App\Models\Deal::orderBy('id', 'desc')->limit(10)->get(['id', 'title', 'editorial_status', 'status']); });
Route::get('/debug-is-indexable/{id}', function($id) { $deal = \App\Models\Deal::find($id); return ['isPublishable' => $deal->isPublishable(), 'isIndexable' => $deal->isIndexable(), 'editorial_status' => $deal->editorial_status, 'summary' => $deal->editorial_summary, 'verdict' => $deal->editorial_verdict, 'pros' => $deal->pros, 'cons' => $deal->cons, 'status' => $deal->status]; });

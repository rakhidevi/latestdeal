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


// The Redirect Engine Endpoint
Route::get('/go/{deal}', [RedirectController::class, 'redirect'])->name('deal.redirect');

// Deal Detail Page
Route::get('/deal/{deal}', [\App\Http\Controllers\DealController::class, 'show'])->name('deal.show');

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
                    'image_path' => $deal->image_path,
                    'merchant' => $deal->merchant->name ?? 'Marketplace',
                    'category' => $deal->category->name ?? 'General',
                ];
            });
    });

    return view('shopper.assistant', compact('deals'));
})->name('shopper.assistant');

// The frontend Vue/Blade entrypoint
Route::get('/', function (\Illuminate\Http\Request $request) {
    // Generate a unique cache key based on the query parameters
    $cacheKey = 'deals.welcome.' . md5(json_encode($request->query()));
    
    // Cache the entire payload for 5 minutes
    $payload = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($request) {
        $query = \App\Models\Deal::where('status', 'active');

        if ($request->has('q') && $request->q) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        if ($request->has('tag') && $request->tag) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        if ($request->has('category') && $request->category && $request->category !== 'all') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('brand') && $request->brand) {
            $query->where('brand', $request->brand);
        }

        if ($request->has('merchant') && $request->merchant) {
            $query->whereHas('merchant', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->merchant . '%');
            });
        }

        if ($request->has('sort') && $request->sort === 'discount') {
            $query->orderByRaw('(original_price - discounted_price) DESC');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $deals = $query->paginate(15)->withQueryString();
        
        // For the Sidebar
        $categories = \App\Models\Category::has('deals')->get();
        $brands = \App\Models\Deal::whereNotNull('brand')->select('brand')->distinct()->pluck('brand');
        $tags = \App\Models\Tag::has('deals')->get();

        return compact('deals', 'categories', 'brands', 'tags');
    });

    return view('welcome', $payload);
});

// SEO Engine
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);

// Legal Pages
Route::view('/terms', 'terms')->name('terms');
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/run-migrations', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return "Migrations ran: " . \Illuminate\Support\Facades\Artisan::output();
});


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

    Route::get('/publisher/dashboard', [\App\Http\Controllers\PublisherAuthController::class, 'dashboard']);
    Route::post('/publisher/logout', [\App\Http\Controllers\PublisherAuthController::class, 'logout']);
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/actions', [\App\Http\Controllers\AdminController::class, 'actions'])->name('admin.actions');
    Route::post('/settings/toggle', [\App\Http\Controllers\AdminController::class, 'toggleSetting'])->name('admin.settings.toggle');
    Route::get('/deals', [\App\Http\Controllers\AdminController::class, 'deals'])->name('admin.deals');
    Route::put('/deals/{deal}/status', [\App\Http\Controllers\AdminController::class, 'updateDealStatus'])->name('admin.deals.status');
    Route::get('/merchants', [\App\Http\Controllers\AdminController::class, 'merchants'])->name('admin.merchants');
    Route::post('/merchants', [\App\Http\Controllers\AdminController::class, 'storeMerchant'])->name('admin.merchants.store');
    Route::put('/merchants/{merchant}', [\App\Http\Controllers\AdminController::class, 'updateMerchant'])->name('admin.merchants.update');
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::get('/links', [\App\Http\Controllers\AdminController::class, 'links'])->name('admin.links');
    Route::post('/links/generate', [\App\Http\Controllers\AdminController::class, 'generateLink'])->name('admin.links.generate');
    
    Route::post('/queue/work', [\App\Http\Controllers\AdminController::class, 'workQueue'])->name('admin.queue.work');
    Route::post('/queue/clear', [\App\Http\Controllers\AdminController::class, 'clearFailedJobs'])->name('admin.queue.clear');
    
    Route::post('/settings/toggle', [\App\Http\Controllers\AdminController::class, 'toggleSetting'])->name('admin.settings.toggle');

    Route::post('/scraper/start', [\App\Http\Controllers\AdminController::class, 'startScraper'])->name('admin.scraper.start');
    Route::post('/scraper/stop', [\App\Http\Controllers\AdminController::class, 'stopScraper'])->name('admin.scraper.stop');
    Route::get('/scraper/status', [\App\Http\Controllers\AdminController::class, 'scraperStatus'])->name('admin.scraper.status');
    Route::post('/scraper/scrape', [\App\Http\Controllers\AdminController::class, 'scrapeUrl'])->name('admin.scraper.scrape');

    Route::get('/social-accounts', [\App\Http\Controllers\AdminController::class, 'socialAccounts'])->name('admin.social-accounts');
    Route::post('/social-accounts', [\App\Http\Controllers\AdminController::class, 'storeSocialAccount'])->name('admin.social-accounts.store');
});

// Setup Route for initializing SQLite Database on Server
Route::get('/setup-db', function () {
    $dbPath = database_path('database.sqlite');
    if (!file_exists($dbPath)) {
        touch($dbPath);
    }
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return 'Database initialized and migrated successfully!';
});

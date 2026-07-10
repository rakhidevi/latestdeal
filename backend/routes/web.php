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


// Fallback for old integer IDs
Route::get('/go/{id}', function ($id) {
    $deal = \App\Models\Deal::findOrFail($id);
    return redirect()->route('deal.redirect', ['deal' => $deal->hash_id], 301);
})->where('id', '[0-9]+');

Route::get('/deal/{id}', function ($id) {
    $deal = \App\Models\Deal::findOrFail($id);
    return redirect()->route('deal.show', ['deal' => $deal->slug], 301);
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

        if ($request->filled('min_price') && is_numeric($request->min_price)) {
            $query->where('discounted_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price') && is_numeric($request->max_price)) {
            $query->where('discounted_price', '<=', $request->max_price);
        }

        if ($request->filled('min_discount') && is_numeric($request->min_discount)) {
            $query->where('original_price', '>', 0)
                  ->whereRaw('((original_price - discounted_price) * 100.0 / original_price) >= ?', [$request->min_discount]);
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

    if ($request->ajax()) {
        $html = view('partials.deals_grid', ['deals' => $payload['deals']])->render();
        return response()->json([
            'html' => $html,
            'next_page' => $payload['deals']->nextPageUrl(),
            'has_more' => $payload['deals']->hasMorePages()
        ]);
    }

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

Route::get('/run-backfill', function() {
    $deals = \App\Models\Deal::all();
    $count = 0;
    foreach ($deals as $deal) {
        $updated = false;
        if (empty($deal->slug)) {
            $baseSlug = \Illuminate\Support\Str::slug($deal->title);
            $slug = $baseSlug;
            $c = 1;
            while (\App\Models\Deal::where('slug', $slug)->where('id', '!=', $deal->id)->exists()) {
                $slug = $baseSlug . '-' . $c++;
            }
            $deal->slug = $slug;
            $updated = true;
        }
        if (empty($deal->hash_id)) {
            $hash = \Illuminate\Support\Str::random(6);
            while (\App\Models\Deal::where('hash_id', $hash)->where('id', '!=', $deal->id)->exists()) {
                $hash = \Illuminate\Support\Str::random(6);
            }
            $deal->hash_id = $hash;
            $updated = true;
        }
        if ($updated) {
            $deal->save();
            $count++;
        }
    }
    return "Backfilled $count deals.";
});

Route::get('/debug-env', function () {
    return [
        'DB_CONNECTION' => config('database.default'),
        'DB_DATABASE' => config('database.connections.' . config('database.default') . '.database'),
        'APP_ENV' => config('app.env'),
        'APP_DEBUG' => config('app.debug'),
    ];
});

Route::get('/migrate-fresh', function () {
    $dbPath = database_path('database.sqlite');
    if (file_exists($dbPath)) {
        unlink($dbPath);
    }
    touch($dbPath);
    // Delete old duplicate migration files left on the server
    $allowed = [
        '0001_01_01_000000_create_users_table.php',
        '0001_01_01_000001_create_cache_table.php',
        '0001_01_01_000002_create_jobs_table.php',
        '2026_01_01_000001a_create_categories_table.php',
        '2026_01_01_000001_create_merchants_table.php',
        '2026_01_01_000002_create_deals_table.php',
        '2026_01_01_000003_create_price_history_table.php',
        '2026_01_01_000004_create_clicks_table.php',
        '2026_01_01_000005_create_subscribers_table.php',
        '2026_01_01_000006_create_price_alerts_table.php',
        '2026_01_01_000007_create_publisher_integrations_table.php',
        '2026_07_03_103016_add_role_to_users_table.php',
        '2026_07_03_172230_add_promo_code_to_deals_table.php',
        '2026_07_03_172231_create_tags_tables.php',
        '2026_07_03_172232_create_publisher_rules_table.php',
        '2026_07_04_000000_create_saved_deals_table.php',
        '2026_07_04_000001_create_social_accounts_table.php',
        '2026_07_04_000003_add_user_id_to_social_accounts_table.php',
        '2026_07_04_215436_add_brand_to_deals_table.php',
        '2026_07_04_224608_add_short_url_to_deals_table.php',
        '2026_07_05_130000_add_ai_metadata_to_deals_table.php',
        '2026_07_07_021400_add_status_to_merchants_table.php',
        '2026_07_07_022412_create_settings_table.php',
        '2026_07_07_145258_create_scraper_jobs_table.php',
        '2026_07_08_000000_add_ai_score_to_deals_table.php'
    ];
    
    $files = scandir(database_path('migrations'));
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..' && !in_array($f, $allowed)) {
            @unlink(database_path('migrations/' . $f));
        }
    }

    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'RealDealSeeder', '--force' => true]);
    
    // Create Admin User
    $u = \App\Models\User::firstOrNew(['email'=>'admin@latestdeal.in']);
    $u->name = 'Admin';
    $u->password = \Illuminate\Support\Facades\Hash::make('password123');
    $u->role = 'admin';
    $u->save();

    return "Database recreated and seeded! Admin: admin@latestdeal.in / password123. \n" . \Illuminate\Support\Facades\Artisan::output();
});

Route::get('/debug-logs', function () {
    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        return "No log file found.";
    }
    // Return last 200 lines
    $lines = file($logFile);
    return implode("", array_slice($lines, -200));
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

    // Manage Price Alerts
    Route::delete('/price-alerts/{id}', function (\Illuminate\Http\Request $request, $id) {
        $user = \Illuminate\Support\Facades\Auth::user();
        $alert = \App\Models\PriceAlert::whereHas('subscriber', function($q) use ($user) {
            $q->where('email', $user->email);
        })->findOrFail($id);
        
        $alert->delete();
        return back()->with('success', 'Price alert removed.');
    })->name('price-alerts.destroy');

    Route::get('/publisher/dashboard', [\App\Http\Controllers\PublisherAuthController::class, 'dashboard']);
    Route::post('/publisher/logout', [\App\Http\Controllers\PublisherAuthController::class, 'logout']);
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/actions', [\App\Http\Controllers\AdminController::class, 'actions'])->name('admin.actions');
    Route::post('/actions/run', [\App\Http\Controllers\AdminController::class, 'runAction'])->name('admin.actions.run');
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
    Route::post('/scraper/hunt', [\App\Http\Controllers\AdminController::class, 'customHunt'])->name('admin.scraper.hunt');

    Route::get('/social-accounts', [\App\Http\Controllers\AdminController::class, 'socialAccounts'])->name('admin.social-accounts');
    Route::post('/social-accounts', [\App\Http\Controllers\AdminController::class, 'storeSocialAccount'])->name('admin.social-accounts.store');
    Route::delete('/social-accounts/{socialAccount}', [\App\Http\Controllers\AdminController::class, 'deleteSocialAccount'])->name('admin.social-accounts.delete');
    Route::put('/social-accounts/{socialAccount}/toggle', [\App\Http\Controllers\AdminController::class, 'toggleSocialAccount'])->name('admin.social-accounts.toggle');
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
Route::get("/run-queue", function () { try { $exitCode = \Illuminate\Support\Facades\Artisan::call("queue:work", ["--stop-when-empty" => true]); return "Queue executed. Exit code: " . $exitCode . "<br>Output:<br><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>"; } catch (\Exception $e) { return "Error: " . $e->getMessage(); } });
Route::get("/debug-failed-jobs", function () { 
    try { 
        $failed = \Illuminate\Support\Facades\DB::table("failed_jobs")->orderBy("id", "desc")->first(); 
        if ($failed) {
            return "<pre>" . $failed->exception . "</pre>"; 
        }
        return "No failed jobs found.";
    } catch (\Exception $e) { 
        return "Error: " . $e->getMessage(); 
    } 
});

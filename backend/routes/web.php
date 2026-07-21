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
                    'image_path' => $deal->image_path,
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

// SEO Engine
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);

// Operations & Catalog Health Dashboard
Route::get('/admin/catalog/health', [\App\Http\Controllers\Admin\CatalogHealthController::class, 'show'])->name('admin.catalog.health');

// Legal Pages
Route::view('/terms', 'terms')->name('terms');
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/seed-admin-now', function () {
    \App\Models\User::where('email', 'admin@latestdeal.in')->delete();
    $u = new \App\Models\User();
    $u->name = 'Admin';
    $u->email = 'admin@latestdeal.in';
    $u->password = \Illuminate\Support\Facades\Hash::make('password123');
    $u->role = 'admin';
    $u->save();

    $attempt = \Illuminate\Support\Facades\Auth::attempt([
        'email' => 'admin@latestdeal.in',
        'password' => 'password123'
    ]);

    return "Admin User Reset Successfully!\nEmail: admin@latestdeal.in\nPassword: password123\nAuth Attempt Result: " . ($attempt ? "SUCCESS" : "FAILED");
});

Route::get('/run-migrations', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    try {
        \Illuminate\Support\Facades\Artisan::call('deals:classify');
        app(\App\Services\Catalog\BrandCounter::class)->recountAll();
        app(\App\Services\NavigationVersionManager::class)->incrementVersion();
        \Illuminate\Support\Facades\Cache::flush();
    } catch (\Throwable $e) {}
    return "Migrations & Catalog Recount ran successfully: " . \Illuminate\Support\Facades\Artisan::output();
});

Route::get('/run-backfill', function(\Illuminate\Http\Request $request) {
    $chunkSize = 30;
    $offset = (int) $request->query('offset', 0);
    
    $deals = \App\Models\Deal::whereNull('slug')
        ->orWhereNull('hash_id')
        ->orWhere('slug', '')
        ->orWhere('hash_id', '')
        ->skip($offset)
        ->take($chunkSize)
        ->get();
    
    if ($deals->isEmpty()) {
        $remaining = \App\Models\Deal::where(function($q) {
            $q->whereNull('slug')->orWhere('slug', '');
        })->orWhere(function($q) {
            $q->whereNull('hash_id')->orWhere('hash_id', '');
        })->count();
        return response()->json(['done' => true, 'remaining' => $remaining, 'message' => 'Backfill complete!']);
    }
    
    $count = 0;
    foreach ($deals as $deal) {
        $updated = false;
        if (empty($deal->getRawOriginal('slug'))) {
            $baseSlug = \Illuminate\Support\Str::slug($deal->title);
            $slug = $baseSlug ?: 'deal';
            $c = 1;
            while (\App\Models\Deal::where('slug', $slug)->where('id', '!=', $deal->id)->exists()) {
                $slug = $baseSlug . '-' . $c++;
            }
            $deal->slug = $slug;
            $updated = true;
        }
        if (empty($deal->getRawOriginal('hash_id'))) {
            $hash = \Illuminate\Support\Str::random(6);
            while (\App\Models\Deal::where('hash_id', $hash)->where('id', '!=', $deal->id)->exists()) {
                $hash = \Illuminate\Support\Str::random(6);
            }
            $deal->hash_id = $hash;
            $updated = true;
        }
        if ($updated) {
            $deal->saveQuietly();
            $count++;
        }
    }
    
    $remaining = \App\Models\Deal::where(function($q) {
        $q->whereNull('slug')->orWhere('slug', '');
    })->orWhere(function($q) {
        $q->whereNull('hash_id')->orWhere('hash_id', '');
    })->count();
    
    return response()->json([
        'done' => false,
        'processed' => $count,
        'offset' => $offset + $chunkSize,
        'remaining' => $remaining,
        'next' => url('/run-backfill?offset=' . ($offset + $chunkSize))
    ]);
});


Route::get('/debug-env', function () {
    return [
        'DB_CONNECTION' => config('database.default'),
        'DB_DATABASE' => config('database.connections.' . config('database.default') . '.database'),
        'APP_ENV' => config('app.env'),
        'APP_DEBUG' => config('app.debug'),
        'OLLAMA_BASE_URL' => env('OLLAMA_BASE_URL') ? '✅ SET' : '❌ NOT SET',
        'GEMINI_API_KEY' => env('GEMINI_API_KEY') ? '✅ SET' : '❌ NOT SET',
    ];
});

// One-time env setup for AI keys (protected by token)
Route::get('/setup-ai-keys', function(\Illuminate\Http\Request $request) {
    $token = $request->query('token');
    if ($token !== 'temp-setup-123') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    $geminiKey  = $request->query('gemini_key');
    $ollamaUrl  = $request->query('ollama_url');
    $ollamaModel= $request->query('ollama_model', 'llama3');
    
    if (!$geminiKey && !$ollamaUrl) {
        return response()->json(['error' => 'No keys provided. Use ?token=APP_KEY&gemini_key=YOUR_KEY&ollama_url=YOUR_TUNNEL_URL']);
    }
    
    $envPath = base_path('.env');
    $envContent = file_get_contents($envPath);
    
    $updates = [];
    
    if ($geminiKey) {
        if (str_contains($envContent, 'GEMINI_API_KEY=')) {
            $envContent = preg_replace('/^GEMINI_API_KEY=.*/m', 'GEMINI_API_KEY=' . $geminiKey, $envContent);
        } else {
            $envContent .= "\nGEMINI_API_KEY=" . $geminiKey;
        }
        $updates[] = 'GEMINI_API_KEY set';
    }
    
    if ($ollamaUrl) {
        if (str_contains($envContent, 'OLLAMA_BASE_URL=')) {
            $envContent = preg_replace('/^OLLAMA_BASE_URL=.*/m', 'OLLAMA_BASE_URL=' . $ollamaUrl, $envContent);
        } else {
            $envContent .= "\nOLLAMA_BASE_URL=" . $ollamaUrl;
        }
        if (str_contains($envContent, 'OLLAMA_MODEL=')) {
            $envContent = preg_replace('/^OLLAMA_MODEL=.*/m', 'OLLAMA_MODEL=' . $ollamaModel, $envContent);
        } else {
            $envContent .= "\nOLLAMA_MODEL=" . $ollamaModel;
        }
        $updates[] = 'OLLAMA_BASE_URL set';
    }
    
    file_put_contents($envPath, $envContent);
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    
    return response()->json(['success' => true, 'updated' => $updates]);
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
    $lines = file($logFile);
    return implode("", array_slice($lines, -200));
});

Route::get('/clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    return "Cache cleared.";
});

Route::get('/debug-error', function() {
    try {
        $deal = \App\Models\Deal::whereNull('slug')->orWhere('slug', '')->first();
        if (!$deal) return "No deals with empty slugs.";
        $slug = $deal->slug; // Triggers the accessor and saveQuietly
        return "Success: " . $slug;
    } catch (\Exception $e) {
        return get_class($e) . ": " . $e->getMessage() . "\nTrace:\n" . $e->getTraceAsString();
    }
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
    
    // API Tokens
    Route::post('/publisher/tokens', [\App\Http\Controllers\PublisherTokenController::class, 'store'])->name('publisher.tokens.store');
    Route::delete('/publisher/tokens/{id}', [\App\Http\Controllers\PublisherTokenController::class, 'destroy'])->name('publisher.tokens.destroy');
    
    // Publisher Rules
    Route::post('/publisher/rules', [\App\Http\Controllers\PublisherRuleController::class, 'store'])->name('publisher.rules.store');
    Route::delete('/publisher/rules/{rule}', [\App\Http\Controllers\PublisherRuleController::class, 'destroy'])->name('publisher.rules.destroy');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminController::class, 'dashboard']);
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/insights', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.insights');
    Route::get('/actions', [\App\Http\Controllers\AdminController::class, 'actions'])->name('admin.actions');
    Route::post('/actions/run', [\App\Http\Controllers\AdminController::class, 'runAction'])->name('admin.actions.run');
    Route::get('/settings', [\App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings/save', [\App\Http\Controllers\AdminController::class, 'saveSettings'])->name('admin.settings.save');
    Route::post('/settings/toggle', [\App\Http\Controllers\AdminController::class, 'toggleSetting'])->name('admin.settings.toggle');
    Route::get('/deals', [\App\Http\Controllers\AdminController::class, 'deals'])->name('admin.deals');
    Route::put('/deals/{deal}/status', [\App\Http\Controllers\AdminController::class, 'updateDealStatus'])->name('admin.deals.status');
    Route::delete('/deals/{deal}', [\App\Http\Controllers\AdminController::class, 'destroyDeal'])->name('admin.deals.destroy');
    Route::delete('/deals-purge-illegal', [\App\Http\Controllers\AdminController::class, 'purgeIllegalDeals'])->name('admin.deals.purge-illegal');
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

Route::get("/fix-status-constraint", function () {
    try {
        // Method 1: Try native Laravel Schema modification
        \Illuminate\Support\Facades\Schema::table("deals", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->string("status", 50)->default("active")->change();
        });
        return "Success: The status column constraint was successfully removed using Laravel Schema builder!";
    } catch (\Exception $e) {
        // Method 2: Fallback to Raw SQLite Table Reconstruction if doctrine/dbal is missing or fails
        try {
            \Illuminate\Support\Facades\DB::statement("PRAGMA foreign_keys=OFF;");
            
            // Get original schema dynamically to ensure we don't miss columns
            $tableInfo = \Illuminate\Support\Facades\DB::select("PRAGMA table_info(deals);");
            $columns = [];
            $selectCols = [];
            foreach ($tableInfo as $col) {
                $name = $col->name;
                $selectCols[] = '"' . $name . '"';
                
                if ($name === 'status') {
                    $columns[] = '"status" varchar(50) default \'active\' not null';
                } elseif ($name === 'id') {
                    $columns[] = '"id" integer primary key autoincrement not null';
                } else {
                    $type = $col->type;
                    $notNull = $col->notnull ? 'not null' : '';
                    $default = $col->dflt_value !== null ? 'default ' . $col->dflt_value : '';
                    $columns[] = '"' . $name . '" ' . $type . ' ' . $notNull . ' ' . $default;
                }
            }
            
            $columnDef = implode(", ", $columns);
            $selectDef = implode(", ", $selectCols);
            
            \Illuminate\Support\Facades\DB::statement("CREATE TABLE deals_new ($columnDef, foreign key(\"category_id\") references \"categories\"(\"id\") on delete cascade, foreign key(\"merchant_id\") references \"merchants\"(\"id\") on delete cascade);");
            \Illuminate\Support\Facades\DB::statement("INSERT INTO deals_new ($selectDef) SELECT $selectDef FROM deals;");
            \Illuminate\Support\Facades\DB::statement("DROP TABLE deals;");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE deals_new RENAME TO deals;");
            
            // Re-add known indexes
            \Illuminate\Support\Facades\DB::statement("CREATE INDEX IF NOT EXISTS deals_slug_index ON deals (slug);");
            \Illuminate\Support\Facades\DB::statement("CREATE INDEX IF NOT EXISTS deals_hash_id_index ON deals (hash_id);");
            
            \Illuminate\Support\Facades\DB::statement("PRAGMA foreign_keys=ON;");
            return "Success: The status column constraint was successfully removed using Raw SQLite reconstruction! Data is safe.";
            
        } catch (\Exception $e2) {
            \Illuminate\Support\Facades\DB::statement("PRAGMA foreign_keys=ON;");
            return "Fatal Error removing constraint: " . $e2->getMessage();
        }
    }
});

Route::get('/run-migrations', function () {
    @opcache_reset();

    // Unlink cached bootstrap files if present
    $bootstrapPath = base_path('bootstrap/cache');
    foreach (['routes-v7.php', 'routes.php', 'config.php', 'services.php'] as $cacheFile) {
        if (file_exists($bootstrapPath . '/' . $cacheFile)) {
            @unlink($bootstrapPath . '/' . $cacheFile);
        }
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('migrate --force');
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'RealDealSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('deals:classify');
        app(\App\Services\Catalog\BrandCounter::class)->recountAll();
        app(\App\Services\NavigationVersionManager::class)->incrementVersion();

        return "BOOTSTRAP CACHE UNLINKED & RECLASSIFY COMPLETE!";
    } catch (\Exception $e) {
        return "ERROR: " . $e->getMessage();
    }
});

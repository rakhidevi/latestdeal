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

// The Redirect Engine Endpoint
Route::get('/go/{deal}', [RedirectController::class, 'redirect'])->name('deal.redirect');

// Deal Detail Page
Route::get('/deal/{deal}', [\App\Http\Controllers\DealController::class, 'show'])->name('deal.show');

// The frontend Vue/Blade entrypoint
Route::get('/', function (\Illuminate\Http\Request $request) {
    $query = \App\Models\Deal::where('status', 'active');

    if ($request->has('q') && $request->q) {
        $query->where('title', 'like', '%' . $request->q . '%');
    }

    if ($request->has('tag') && $request->tag) {
        $query->whereHas('tags', function($q) use ($request) {
            $q->where('slug', $request->tag);
        });
    }

    if ($request->has('category') && $request->category) {
        $query->whereHas('category', function($q) use ($request) {
            $q->where('slug', $request->category);
        });
    }

    if ($request->has('brand') && $request->brand) {
        $query->where('brand', $request->brand);
    }

    $deals = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();
    
    // For the Sidebar
    $categories = \App\Models\Category::has('deals')->get();
    $brands = \App\Models\Deal::whereNotNull('brand')->select('brand')->distinct()->pluck('brand');
    $tags = \App\Models\Tag::has('deals')->get();

    return view('welcome', compact('deals', 'categories', 'brands', 'tags'));
});

// SEO Engine
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);

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
    Route::get('/deals', [\App\Http\Controllers\AdminController::class, 'deals'])->name('admin.deals');
    Route::get('/merchants', [\App\Http\Controllers\AdminController::class, 'merchants'])->name('admin.merchants');
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::get('/links', [\App\Http\Controllers\AdminController::class, 'links'])->name('admin.links');
    Route::post('/links/generate', [\App\Http\Controllers\AdminController::class, 'generateLink'])->name('admin.links.generate');
    
    Route::post('/queue/work', [\App\Http\Controllers\AdminController::class, 'workQueue'])->name('admin.queue.work');
    Route::post('/queue/clear', [\App\Http\Controllers\AdminController::class, 'clearFailedJobs'])->name('admin.queue.clear');
    
    Route::get('/social-accounts', [\App\Http\Controllers\AdminController::class, 'socialAccounts'])->name('admin.social-accounts');
    Route::post('/social-accounts', [\App\Http\Controllers\AdminController::class, 'storeSocialAccount'])->name('admin.social-accounts.store');
});

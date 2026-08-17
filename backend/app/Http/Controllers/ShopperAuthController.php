<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\WelcomeShopperMail;
use App\Mail\VerifyEmailMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Verified;

class ShopperAuthController extends Controller
{
    public function loginView()
    {
        if (Auth::check()) {
            return Auth::user()->role === 'admin' 
                ? redirect('/admin/dashboard') 
                : redirect('/dashboard');
        }
        return view('auth.shopper-login');
    }

    public function login(Request $request)
    {
        $request->merge(['email' => trim($request->email)]);
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function registerView()
    {
        if (Auth::check()) {
            return Auth::user()->role === 'admin' 
                ? redirect('/admin/dashboard') 
                : redirect('/dashboard');
        }
        return view('auth.shopper-register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'shopper'
        ]);

        // Generate 60-minute signed email verification link
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Dispatch Welcome & Email Verification Mailables to Transactional Queues
        try {
            Mail::to($user->email)->queue(new WelcomeShopperMail($user, $verificationUrl));
            Mail::to($user->email)->queue(new VerifyEmailMail($user, $verificationUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to queue registration emails: " . $e->getMessage());
        }

        Auth::login($user);
        return redirect('/dashboard')->with('status', 'Registration successful! Please check your email to verify your account.');
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect('/dashboard')->with('status', 'Email verified successfully! Welcome to LatestDeal.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        // 1. Saved Deals
        $savedDeals = $user->savedDeals()->with('merchant')->get();
        
        // 2. Price Alerts
        $priceAlerts = \App\Models\PriceAlert::whereHas('subscriber', function($q) use ($user) {
            $q->where('email', $user->email);
        })->get();
        
        // 3. Watchlists with Intelligence
        $watchlists = \App\Models\Watchlist::with('watchable')->where('user_id', $user->id)->get();
        
        foreach ($watchlists as $watchlist) {
            // Watchlist Intelligence Logic
            $model = $watchlist->watchable;
            if ($model) {
                // Approximate metrics for the UI
                $recentDeals = \App\Models\Deal::where('status', 'active');
                if (get_class($model) === 'App\Models\Brand') {
                    $recentDeals->where('brand_id', $model->id);
                } else if (get_class($model) === 'App\Models\Category') {
                    $recentDeals->where('category_id', $model->id);
                }
                
                $todayDeals = (clone $recentDeals)->whereDate('created_at', today())->count();
                $avgDiscount = (clone $recentDeals)->avg('discount_percentage') ?? 0;
                $bestDiscount = (clone $recentDeals)->max('discount_percentage') ?? 0;
                $dealCount = (clone $recentDeals)->count();
                
                // Heat Score (0-100)
                $heatScore = min(100, ($todayDeals * 10) + ($dealCount > 10 ? 20 : 0) + ($bestDiscount > 50 ? 20 : 0));
                
                $watchlist->intelligence = [
                    'deal_count' => $dealCount,
                    'today_deals' => $todayDeals,
                    'avg_discount' => round($avgDiscount),
                    'best_discount' => round($bestDiscount),
                    'heat_score' => $heatScore,
                ];
            }
        }
        
        // KPIs
        $triggeredAlerts = $priceAlerts->whereNotNull('triggered_at')->count();
        $estimatedSavings = $savedDeals->sum(function($deal) {
            return max(0, $deal->original_price - $deal->discounted_price);
        });
        
        // Activity Timeline
        $timeline = $user->activities()->latest()->take(20)->get();
        
        // Recommendations Engine (Weighted: Watchlists, Saved, Recent Views)
        $recommendedDeals = \App\Models\Deal::where('status', 'active')
            ->orderBy('ai_score', 'desc')
            ->take(10)->get(); // Simplified for MVP: should use complex weighted scoring in production
            
        // Recently Viewed
        $recentlyViewedInteractionIds = $user->interactions()
            ->where('interaction_type', 'deal_view')
            ->latest()
            ->take(20)
            ->pluck('deal_id');
            
        $recentlyViewedDeals = \App\Models\Deal::whereIn('id', $recentlyViewedInteractionIds)->get();

        return view('shopper.dashboard', compact(
            'savedDeals', 'priceAlerts', 'watchlists', 'user', 
            'triggeredAlerts', 'estimatedSavings', 'timeline', 
            'recommendedDeals', 'recentlyViewedDeals'
        ));
    }

    public function toggleWatchlist(Request $request, \App\Services\User\InteractionService $interactionService)
    {
        $request->validate([
            'type' => 'required|in:Brand,Category',
            'id' => 'required|integer'
        ]);

        $user = Auth::user();
        $modelClass = 'App\\Models\\' . $request->type;
        $model = $modelClass::find($request->id);

        $existing = \App\Models\Watchlist::where('user_id', $user->id)
            ->where('watchable_type', $modelClass)
            ->where('watchable_id', $request->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Removed from watchlist.');
        } else {
            \App\Models\Watchlist::create([
                'user_id' => $user->id,
                'watchable_type' => $modelClass,
                'watchable_id' => $request->id
            ]);
            
            $interactionType = strtolower($request->type) === 'brand' ? 'watch_brand' : 'watch_category';
            $interactionService->record($interactionType, 'dashboard', null, [
                'name' => $model ? $model->name : 'Unknown'
            ]);
            
            return back()->with('success', 'Added to watchlist.');
        }
    }
}

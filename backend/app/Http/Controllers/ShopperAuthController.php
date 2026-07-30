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
        $savedDeals = $user->savedDeals()->with('merchant')->get();
        $priceAlerts = \App\Models\PriceAlert::whereHas('subscriber', function($q) use ($user) {
            $q->where('email', $user->email);
        })->get();
        
        return view('shopper.dashboard', compact('savedDeals', 'priceAlerts', 'user'));
    }
}

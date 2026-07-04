<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PublisherIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PublisherAuthController
{
    public function loginView()
    {
        return view('publisher.login');
    }

    public function registerView()
    {
        return view('publisher.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('publisher/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        
        Auth::login($user);

        return redirect('publisher/dashboard');
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $integrations = PublisherIntegration::where('user_id', $user->id)->get();
        
        // Count metrics
        $metricsController = app(\App\Http\Controllers\Api\MetricsController::class);
        $metrics = $metricsController->index(request())->getData();

        return view('publisher.dashboard', compact('user', 'integrations', 'metrics'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

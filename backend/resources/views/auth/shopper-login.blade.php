@extends('layouts.app')
@section('title', 'Shopper Login')
@section('content')
<div class="max-w-5xl mx-auto my-10 bg-white dark:bg-slate-900 rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row border border-gray-100 dark:border-slate-800">
    
    <!-- Left Sidebar (Branding & Benefits) -->
    <div class="hidden lg:flex w-full lg:w-5/12 bg-gradient-to-br from-red-700 via-red-600 to-red-500 text-white p-8 lg:p-12 flex-col justify-between relative overflow-hidden">
        <!-- Abstract background elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-yellow-400/20 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative z-10">
            <h2 class="text-3xl font-black mb-4 tracking-tight leading-tight">Unlock AI-Verified Global Deals</h2>
            <p class="text-red-50 mb-10 text-sm md:text-base opacity-90 leading-relaxed font-medium">
                Join our platform to save thousands. Our AI engine verifies, scores, and tracks price history 24/7.
            </p>

            <ul class="space-y-5 font-semibold text-sm">
                <li class="flex items-center gap-4">
                    <div class="bg-white/20 p-2.5 rounded-xl backdrop-blur-sm shadow-inner">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                    <span>Instant Price Drop Alerts</span>
                </li>
                <li class="flex items-center gap-4">
                    <div class="bg-white/20 p-2.5 rounded-xl backdrop-blur-sm shadow-inner">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                    </div>
                    <span>Access 90+ Quality Scored Deals</span>
                </li>
                <li class="flex items-center gap-4">
                    <div class="bg-white/20 p-2.5 rounded-xl backdrop-blur-sm shadow-inner">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <span>Personalized AI Shopping Assistant</span>
                </li>
            </ul>
        </div>
        
        <div class="relative z-10 mt-12 pt-8 border-t border-white/20">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/70 mb-4">Supported Retailers</p>
            <div class="flex flex-wrap gap-4 items-center opacity-90">
                <span class="font-extrabold text-lg tracking-tight">Amazon</span>
                <span class="font-extrabold text-lg tracking-tight">Udemy</span>
                <span class="font-bold text-base tracking-tight text-white/50">Myntra (Soon)</span>
            </div>
        </div>
    </div>

    <!-- Right Side (Form) -->
    <div class="w-full lg:w-7/12 p-8 lg:p-14 xl:p-20 flex flex-col justify-center bg-white dark:bg-slate-900">
        <div class="max-w-md w-full mx-auto">
            <div class="text-center mb-10">
                <div class="w-16 h-16 bg-red-50 dark:bg-red-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-red-100 dark:border-red-500/20">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Welcome Back!</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2 font-medium">Enter your details to access your dashboard.</p>
            </div>
            
            @if($errors->any())
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-8 text-sm border border-red-100 flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-medium">{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('shopper.login') }}" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                        </div>
                        <input type="email" name="email" required placeholder="you@example.com" class="block w-full pl-11 rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all p-3.5 shadow-sm text-gray-900 dark:text-white font-medium placeholder-gray-400">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 flex justify-between items-center">
                        Password
                        <a href="#" class="text-xs text-red-600 hover:text-red-700 font-bold transition-colors">Forgot Password?</a>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" name="password" required placeholder="••••••••" class="block w-full pl-11 rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all p-3.5 shadow-sm text-gray-900 dark:text-white font-medium placeholder-gray-400">
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white py-4 rounded-xl font-bold tracking-wide shadow-lg shadow-red-500/30 hover:shadow-xl hover:shadow-red-500/40 hover:-translate-y-0.5 transition-all flex justify-center items-center gap-2 mt-2">
                    Access Account
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>

            <div class="mt-10 pt-8 border-t border-gray-100 dark:border-slate-800 text-center text-sm font-medium text-gray-600 dark:text-gray-400">
                Don't have an account yet? <a href="{{ route('shopper.register') }}" class="font-bold text-red-600 hover:text-red-700 hover:underline transition-colors ml-1">Create one now</a>
            </div>
        </div>
    </div>
</div>
@endsection

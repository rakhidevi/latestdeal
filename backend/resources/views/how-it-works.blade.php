@extends('layouts.app')

@section('meta')
    <title>How LatestDeal Works | LatestDeal.in</title>
    <meta name="description" content="Discover the autonomous technology and editorial process behind LatestDeal's deal discovery, historical price validation, and multi-store intelligence.">
    <link rel="canonical" href="{{ url('/how-it-works') }}">
@endsection

@section('content')
<div class="py-6 sm:py-10 font-sans">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        
        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 dark:text-slate-500 mb-6" aria-label="Breadcrumb">
            <a href="/" class="hover:text-red-600 dark:hover:text-red-400 transition-colors">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-600 dark:text-slate-400">Platform</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 dark:text-white font-bold" aria-current="page">How It Works</span>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Autonomous Architecture
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                How <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">LatestDeal</span> Works
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed">
                From real-time discovery and historical price analysis to multi-store comparison and editorial oversight.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                <span>Verified by LatestDeal Engineering & Editorial Desk</span>
                <span>•</span>
                <span>Updated {{ date('F Y') }}</span>
            </div>
        </div>

        <!-- 4-Step Process Layout -->
        <div class="space-y-6 sm:space-y-8">
            
            <!-- Step 1 -->
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/40 dark:shadow-none flex flex-col sm:flex-row gap-6 items-start">
                <div class="p-3.5 rounded-2xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-red-600 dark:text-red-400 tracking-wider uppercase">Stage 01</span>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1 mb-3">Autonomous Deal Discovery</h3>
                    <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed">
                        Our autonomous scraping daemons continuously monitor top e-commerce platforms in India, capturing price changes across electronics, home appliances, smartphones, and fashion. When an item experiences a sharp price drop, our pipeline triggers an immediate evaluation job.
                    </p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/40 dark:shadow-none flex flex-col sm:flex-row gap-6 items-start">
                <div class="p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400 tracking-wider uppercase">Stage 02</span>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1 mb-3">Historical Baseline Verification</h3>
                    <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed">
                        A "70% OFF" badge is often misleading if the original MRP was artificially inflated right before a festive sale. We cross-reference every candidate deal against up to 180 days of price history. If the item sold for the same price last week, it is rejected. Only genuine price drops pass this barrier.
                    </p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/40 dark:shadow-none flex flex-col sm:flex-row gap-6 items-start">
                <div class="p-3.5 rounded-2xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-purple-600 dark:text-purple-400 tracking-wider uppercase">Stage 03</span>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1 mb-3">Multi-Store Intelligence Engine</h3>
                    <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed">
                        Our self-hosted search intelligence tool queries competitor retail storefronts in real time—including Amazon, Flipkart, Croma, and Reliance Digital. This verifies whether the deal is a market-wide low or simply price-matched by all competitors.
                    </p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/40 dark:shadow-none flex flex-col sm:flex-row gap-6 items-start">
                <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 tracking-wider uppercase">Stage 04</span>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1 mb-3">Editorial Oversight & Publishing</h3>
                    <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed">
                        Deals that pass all algorithmic filters are sent to our editorial desk. Experienced editors review product specifications, identify who should buy or skip the item, verify coupon codes, and publish the deal to our website and instant Telegram alerts.
                    </p>
                </div>
            </div>

        </div>

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="mt-10 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Learn more about our standards and team:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('about') }}" class="text-red-600 dark:text-red-400 hover:underline">About Us →</a>
                <a href="{{ route('editorial.team') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Team →</a>
                <a href="{{ route('editorial.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Policy →</a>
                <a href="{{ route('corrections.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Corrections Policy →</a>
            </div>
        </div>

    </div>
</div>
@endsection

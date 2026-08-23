@extends('layouts.app')

@section('meta')
    <title>About Us | LatestDeal</title>
    <meta name="description" content="Learn about LatestDeal, our mission to help you find the best verified deals, and our intelligence engine.">
    <link rel="canonical" href="{{ url('/about') }}">
@endsection

@section('content')
<!-- Decorative Background -->
<div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
    <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-red-500/5 blur-[120px]"></div>
    <div class="absolute top-[20%] -right-[10%] w-[40%] h-[40%] rounded-full bg-rose-500/5 blur-[100px]"></div>
</div>

<div class="relative z-10 min-h-screen pt-20 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Hero Section -->
        <div class="text-center max-w-3xl mx-auto mb-20">
            <span class="inline-flex items-center gap-2 py-1.5 px-4 rounded-full bg-red-50 border border-red-100 text-red-600 text-xs font-black tracking-widest uppercase mb-6 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                About LatestDeal
            </span>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-gray-900 tracking-tight mb-6 leading-tight">
                We decode the noise.<br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-500">You save the money.</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-500 font-medium leading-relaxed">
                LatestDeal is an autonomous shopping intelligence platform built to expose fake discounts, track historical price drops, and deliver mathematically verified deals.
            </p>
        </div>

        <!-- Metrics Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-24">
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm text-center">
                <div class="text-3xl font-black text-gray-900 mb-1">24/7</div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Price Monitoring</div>
            </div>
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm text-center">
                <div class="text-3xl font-black text-gray-900 mb-1">100%</div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Free to Use</div>
            </div>
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm text-center">
                <div class="text-3xl font-black text-gray-900 mb-1">Math</div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Verified Discounts</div>
            </div>
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm text-center">
                <div class="text-3xl font-black text-gray-900 mb-1">Zero</div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Fake Promotions</div>
            </div>
        </div>

        <!-- Four Pillars Grid -->
        <div class="grid md:grid-cols-2 gap-8 mb-24 max-w-5xl mx-auto">
            
            <!-- Mission -->
            <div class="bg-white p-8 md:p-10 rounded-[2rem] border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-4">Our Mission</h3>
                <p class="text-gray-500 leading-relaxed font-medium">
                    Online shopping has become a maze of fake discounts, inflated MSRPs, and sponsored noise. Our mission is simple: cut through the marketing spin and highlight only the genuine deals that mathematically prove their worth.
                </p>
            </div>

            <!-- Intelligence -->
            <div class="bg-white p-8 md:p-10 rounded-[2rem] border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-4">The Intelligence Engine</h3>
                <p class="text-gray-500 leading-relaxed font-medium">
                    We track millions of products across top e-commerce platforms. Our proprietary algorithms monitor price drops 24/7. When a drop is detected, the system validates it against historical price data to ensure the discount is mathematically real.
                </p>
            </div>

            <!-- Human Editorial -->
            <div class="bg-white p-8 md:p-10 rounded-[2rem] border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-4">Human Approval</h3>
                <p class="text-gray-500 leading-relaxed font-medium">
                    AI assists us, but it doesn't decide what gets published. Our Editorial Team reviews the best offers to ensure they make sense. We never let AI hallucinate specifications—every technical detail is sourced directly from verified merchant data.
                </p>
            </div>

            <!-- Transparency -->
            <div class="bg-white p-8 md:p-10 rounded-[2rem] border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-4">Absolute Transparency</h3>
                <p class="text-gray-500 leading-relaxed font-medium">
                    LatestDeal is completely free. To keep the servers running, we participate in affiliate programs. If you buy through our links, we may earn a small commission at <strong>no extra cost to you</strong>. This never influences our deal scoring or editorial rankings.
                </p>
            </div>
            
        </div>

        <!-- CTA -->
        <div class="max-w-4xl mx-auto bg-gray-900 rounded-[2.5rem] p-10 md:p-16 text-center relative overflow-hidden shadow-2xl">
            <div class="absolute top-0 right-0 w-64 h-64 bg-red-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10">
                <h2 class="text-3xl md:text-4xl font-black text-white mb-6">Stop guessing. Start saving.</h2>
                <p class="text-gray-400 font-medium mb-10 max-w-2xl mx-auto">
                    Join thousands of shoppers who trust LatestDeal to find the absolute lowest prices on the products they actually want.
                </p>
                <a href="/search" class="inline-flex items-center gap-2 bg-white text-gray-900 hover:bg-gray-50 px-8 py-4 rounded-xl font-black text-lg transition-colors shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Start Searching
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

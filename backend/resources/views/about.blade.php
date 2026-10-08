@extends('layouts.app')

@section('meta')
    <title>About Us | LatestDeal.in</title>
    <meta name="description" content="Learn about LatestDeal.in, our mission to decode genuine deals, avoid fake MRP drops, and our independent editorial review team.">
    <link rel="canonical" href="{{ url('/about') }}">
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
            <span class="text-slate-900 dark:text-white font-bold" aria-current="page">About Us</span>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                About Our Platform
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                We Decode Deals So You <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">Save More</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed">
                LatestDeal is an autonomous shopping intelligence platform dedicated to bringing you verified discounts, historical price trends, and honest buying advice.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                <span>Verified by LatestDeal Editorial Desk</span>
                <span>•</span>
                <span>Updated {{ date('F Y') }}</span>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-10 md:p-12 shadow-xl shadow-slate-200/40 dark:shadow-none">
            <div class="prose prose-slate dark:prose-invert max-w-none prose-headings:font-black prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:tracking-tight prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-p:leading-relaxed prose-li:text-slate-600 dark:prose-li:text-slate-300 prose-strong:text-slate-900 dark:prose-strong:text-white prose-a:text-red-600 dark:prose-a:text-red-400 hover:prose-a:underline">
                
                <h2>Our Mission</h2>
                <p>
                    Online shopping has become a maze of fake discounts, artificially inflated MRPs, and sponsored product placements. Our mission is to cut through the noise. We combine autonomous data collection, real-time price verification, and human editorial oversight to highlight only the <strong>genuine deals</strong> that are truly worth your hard-earned money.
                </p>

                <!-- Value Pillar Highlights -->
                <div class="not-prose my-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-xl bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="font-black text-sm text-slate-900 dark:text-white mb-1">Zero Fake Discounts</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Every price is verified against 90-day historical retailer data to filter out synthetic drops.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h4 class="font-black text-sm text-slate-900 dark:text-white mb-1">Multi-Store Intelligence</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Live cross-store price comparisons across Amazon, Flipkart, Croma, and Reliance Digital.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h4 class="font-black text-sm text-slate-900 dark:text-white mb-1">Human Curation</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Experienced editors test products, evaluate specs, and author comprehensive buying guides.</p>
                    </div>
                </div>

                <h2>How We Find Deals</h2>
                <p>
                    We track millions of products across leading e-commerce platforms in India. Our proprietary crawler ecosystem scans price variations 24/7. When a noteworthy price drop is detected, our pipeline runs a series of sanity checks:
                </p>
                <ul>
                    <li><strong>Historical Baseline Check:</strong> Is the current price lower than the typical price over the last 30 to 180 days?</li>
                    <li><strong>Seller Integrity Check:</strong> Is the seller verified with acceptable fulfillment and return metrics?</li>
                    <li><strong>Competitor Scan:</strong> Does another merchant offer the identical item for less?</li>
                </ul>

                <h2>Our Editorial Standards</h2>
                <p>
                    While our bots discover the price anomalies, our human editorial team evaluates the products. We write in-depth buying guides, curate category recommendations, and highlight the pros and cons of each deal. We never allow automated systems to fabricate specifications or user reviews.
                </p>

                <h2>Affiliate Model & Independence</h2>
                <p>
                    LatestDeal is 100% free to access. To sustain our operations and infrastructure, we participate in affiliate programs (including the Amazon Associates Program). When you buy through our links, we may earn an affiliate commission at <strong>no additional cost to you</strong>. Our editorial ratings and deal qualifications are never influenced by affiliate fee structures.
                </p>
            </div>
        </div>

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="mt-10 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Explore more about our platform standards:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('how.it.works') }}" class="text-red-600 dark:text-red-400 hover:underline">How It Works →</a>
                <a href="{{ route('editorial.team') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Team →</a>
                <a href="{{ route('editorial.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Policy →</a>
                <a href="{{ route('affiliate.disclosure') }}" class="text-red-600 dark:text-red-400 hover:underline">Affiliate Disclosure →</a>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('meta')
    <title>About Us | LatestDeal.in</title>
    <meta name="description" content="Learn about LatestDeal.in, our mission to decode genuine deals, avoid fake MRP drops, and our independent editorial review team.">
    <link rel="canonical" href="{{ url('/about') }}">
@endsection

@section('content')
<div class="w-full max-w-4xl mx-auto py-8 sm:py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">About LatestDeal</h1>
        
        <p class="text-sm font-semibold text-gray-500 dark:text-slate-400 mb-6">Verified by LatestDeal Editorial Desk &bull; Updated {{ date('F Y') }}</p>

        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                LatestDeal is an autonomous shopping intelligence platform dedicated to bringing you verified discounts, historical price trends, and honest buying advice. We cut through the noise of inflated MRPs and synthetic price drops.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Our Mission</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                Online shopping has become a maze of fake discounts, artificially inflated MRPs, and sponsored product placements. Our mission is simple: to combine autonomous data collection, real-time multi-store price verification, and human editorial oversight to highlight only the <strong class="text-gray-900 dark:text-white font-semibold">genuine deals</strong> that are truly worth your hard-earned money.
            </p>

            <!-- Value Pillars -->
            <div class="not-prose my-8 grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-base text-gray-900 dark:text-white mb-2">Zero Fake Discounts</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Every price is verified against 90-day historical retailer data to filter out synthetic drops.</p>
                </div>

                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="font-bold text-base text-gray-900 dark:text-white mb-2">Multi-Store Intelligence</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Live cross-store price comparisons across Amazon, Flipkart, Croma, and Reliance Digital.</p>
                </div>

                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-base text-gray-900 dark:text-white mb-2">Human Curation</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Experienced editors test products, evaluate specs, and author comprehensive buying guides.</p>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">How We Find Deals</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                We track millions of products across leading e-commerce platforms in India. Our proprietary crawler ecosystem scans price variations 24/7. When a noteworthy price drop is detected, our pipeline runs a series of sanity checks:
            </p>
            <ul class="list-disc pl-6 space-y-2 mt-4 mb-6 text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                <li><strong class="text-gray-900 dark:text-white font-semibold">Historical Baseline Check:</strong> Is the current price lower than the typical price over the last 30 to 180 days?</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Seller Integrity Check:</strong> Is the seller verified with acceptable fulfillment and return metrics?</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Competitor Scan:</strong> Does another merchant offer the identical item for less?</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Our Editorial Standards</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                While our bots discover the price anomalies, our human editorial team evaluates the products. We write in-depth buying guides, curate category recommendations, and highlight the pros and cons of each deal. We never allow automated systems to fabricate specifications or user reviews.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Affiliate Model & Independence</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                LatestDeal is 100% free to access. To sustain our operations and infrastructure, we participate in affiliate programs (including the Amazon Associates Program). When you buy through our links, we may earn an affiliate commission at <strong class="text-gray-900 dark:text-white font-semibold">no additional cost to you</strong>. Our editorial ratings and deal qualifications are never influenced by affiliate fee structures.
            </p>
        </div>
    </div>
</div>
@endsection

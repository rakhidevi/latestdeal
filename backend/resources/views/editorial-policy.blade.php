@extends('layouts.app')

@section('meta')
    <title>Editorial Policy | LatestDeal.in</title>
    <meta name="description" content="Discover LatestDeal's strict editorial standards, our commitment to unbiased deal analysis, review methodology, and independent consumer advocacy.">
    <link rel="canonical" href="{{ url('/editorial-policy') }}">
@endsection

@section('content')
<div class="py-6 sm:py-10 font-sans">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        
        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 dark:text-slate-500 mb-6" aria-label="Breadcrumb">
            <a href="/" class="hover:text-red-600 dark:hover:text-red-400 transition-colors">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-600 dark:text-slate-400">Legal & Trust</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 dark:text-white font-bold" aria-current="page">Editorial Policy</span>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Editorial Independence
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                Editorial <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">Policy</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed">
                Our editorial standards, review methodology, and unwavering commitment to independent consumer advocacy.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                <span>Published by LatestDeal Editorial Desk</span>
                <span>•</span>
                <span>Updated {{ date('F Y') }}</span>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-10 md:p-12 shadow-xl shadow-slate-200/40 dark:shadow-none">
            <div class="prose prose-slate dark:prose-invert max-w-none prose-headings:font-black prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:tracking-tight prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-p:leading-relaxed prose-li:text-slate-600 dark:prose-li:text-slate-300 prose-strong:text-slate-900 dark:prose-strong:text-white prose-a:text-red-600 dark:prose-a:text-red-400 hover:prose-a:underline">
                
                <h2>1. Core Principles of Editorial Independence</h2>
                <p>
                    LatestDeal.in was founded to solve a pervasive problem: misleading discounts, artificial price spikes, and undisclosed promotional bias in online retail. Our primary duty is to the consumer. Our editorial desk operates with complete independence from our affiliate and advertising partnerships. Retailers cannot pay for preferential editorial placement or inflate deal scores.
                </p>

                <h2>2. 4-Point Deal Evaluation Methodology</h2>
                <p>Before any product is published as a qualified deal or featured in an evergreen buying guide, our editorial team evaluates four essential pillars:</p>
                <ul>
                    <li><strong>Historical Price Validation:</strong> We verify the price against 30, 90, and 180-day price trends across verified retailer APIs to ensure the discount is real and not calculated against an inflated MRP.</li>
                    <li><strong>Retailer & Seller Credibility:</strong> We filter out unrated marketplace sellers, counterfeit risks, and grey-market imports. We favor authorized retailer storefronts with reliable warranty backing.</li>
                    <li><strong>Verified Specifications:</strong> We source technical specifications directly from official manufacturer documentation—never relying on automated approximations or unsubstantiated marketing buzzwords.</li>
                    <li><strong>Real Consumer Utility:</strong> A massive discount on an inferior product is never a good deal. We evaluate build quality, practical battery life, software update records, and user feedback before recommending any item.</li>
                </ul>

                <h2>3. Responsible AI Integration</h2>
                <p>
                    LatestDeal leverages automated tools and machine-learning models to scan thousands of retailer listings per minute, detect price changes, and identify candidate deals. However:
                </p>
                <ul>
                    <li>AI tools are used strictly for data discovery, sorting, and volatility detection.</li>
                    <li>All editorial conclusions, buying advice, and verdict ratings are reviewed and approved by human editors.</li>
                    <li>We prohibit automated systems from synthesizing customer testimonials or generating fictional product reviews.</li>
                </ul>

                <h2>4. Zero Pay-for-Play Guarantee</h2>
                <p>
                    We do not accept payment, free review samples in exchange for positive coverage, or commercial incentives to feature specific products. If a retailer offers an affiliate commission, that commission has zero bearing on our deal selection or editorial verdicts.
                </p>

                <h2>5. Corrections & Transparent Updates</h2>
                <p>
                    If an inaccuracy is identified in our product descriptions, pricing history, or buying guides, we update it swiftly under our public <a href="{{ route('corrections.policy') }}" class="font-bold">Corrections Policy</a>. We encourage readers to report errors directly to <a href="mailto:support@latestdeal.in">support@latestdeal.in</a>.
                </p>
            </div>
        </div>

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="mt-10 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Related policies and editorial resources:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('corrections.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Corrections Policy →</a>
                <a href="{{ route('editorial.team') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Team →</a>
                <a href="{{ route('affiliate.disclosure') }}" class="text-red-600 dark:text-red-400 hover:underline">Affiliate Disclosure →</a>
                <a href="{{ route('privacy') }}" class="text-red-600 dark:text-red-400 hover:underline">Privacy Policy →</a>
            </div>
        </div>

    </div>
</div>
@endsection

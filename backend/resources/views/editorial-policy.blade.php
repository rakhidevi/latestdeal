@extends('layouts.app')

@section('meta')
    <title>Editorial Policy | LatestDeal.in</title>
    <meta name="description" content="Discover LatestDeal's strict editorial standards, our commitment to unbiased deal analysis, review methodology, and independent consumer advocacy.">
    <link rel="canonical" href="{{ url('/editorial-policy') }}">
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Editorial Policy</h1>
        
        <p class="text-sm font-semibold text-gray-500 dark:text-slate-400 mb-6">Published by LatestDeal Editorial Desk &bull; Updated {{ date('F Y') }}</p>

        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                Our editorial standards, review methodology, and unwavering commitment to independent consumer advocacy.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">1. Core Principles of Editorial Independence</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                LatestDeal.in was founded to solve a pervasive problem: misleading discounts, artificial price spikes, and undisclosed promotional bias in online retail. Our primary duty is to the consumer. Our editorial desk operates with complete independence from our affiliate and advertising partnerships. Retailers cannot pay for preferential editorial placement or inflate deal scores.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">2. 4-Point Deal Evaluation Methodology</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                Before any product is published as a qualified deal or featured in an evergreen buying guide, our editorial team evaluates four essential pillars:
            </p>
            <ul class="list-disc pl-6 space-y-2 mt-4 mb-6 text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                <li><strong class="text-gray-900 dark:text-white font-semibold">Historical Price Validation:</strong> We verify the price against 30, 90, and 180-day price trends across verified retailer APIs to ensure the discount is real and not calculated against an inflated MRP.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Retailer &amp; Seller Credibility:</strong> We filter out unrated marketplace sellers, counterfeit risks, and grey-market imports. We favor authorized retailer storefronts with reliable warranty backing.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Verified Specifications:</strong> We source technical specifications directly from official manufacturer documentation—never relying on automated approximations or unsubstantiated marketing buzzwords.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Real Consumer Utility:</strong> A massive discount on an inferior product is never a good deal. We evaluate build quality, practical battery life, software update records, and user feedback before recommending any item.</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">3. Responsible AI Integration</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                LatestDeal leverages automated tools and machine-learning models to scan thousands of retailer listings per minute, detect price changes, and identify candidate deals. However:
            </p>
            <ul class="list-disc pl-6 space-y-2 mt-4 mb-6 text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                <li>AI tools are used strictly for data discovery, sorting, and volatility detection.</li>
                <li>All editorial conclusions, buying advice, and verdict ratings are reviewed and approved by human editors.</li>
                <li>We prohibit automated systems from synthesizing customer testimonials or generating fictional product reviews.</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">4. Zero Pay-for-Play Guarantee</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                We do not accept payment, free review samples in exchange for positive coverage, or commercial incentives to feature specific products. If a retailer offers an affiliate commission, that commission has zero bearing on our deal selection or editorial verdicts.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">5. Corrections &amp; Transparent Updates</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                If an inaccuracy is identified in our product descriptions, pricing history, or buying guides, we update it swiftly under our public <a href="/corrections-policy" class="text-red-600 hover:underline font-semibold">Corrections Policy</a>. We encourage readers to report errors directly to <a href="mailto:support@latestdeal.in" class="text-red-600 hover:underline font-semibold">support@latestdeal.in</a>.
            </p>
        </div>
    </div>
</div>
@endsection

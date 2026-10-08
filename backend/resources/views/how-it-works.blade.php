@extends('layouts.app')

@section('meta')
    <title>How It Works | LatestDeal.in</title>
    <meta name="description" content="Discover how LatestDeal autonomous crawlers track prices, eliminate artificial discounts, and deliver verified shopping intelligence.">
    <link rel="canonical" href="{{ url('/how-it-works') }}">
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">How LatestDeal Works</h1>
        
        <p class="text-sm font-semibold text-gray-500 dark:text-slate-400 mb-6">Autonomous Scraping Fleet &bull; Deterministic Price Verification &bull; Human Editorial Oversight</p>

        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                Most bargain sites simply repost whatever affiliate links offer the highest commission. LatestDeal works entirely differently: we operate a high-frequency autonomous intelligence platform engineered to eliminate artificial discounts and find real savings.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">The 4-Stage Intelligence Pipeline</h2>

            <!-- Pipeline Step 1 -->
            <div class="my-6 p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 font-bold text-sm flex items-center justify-center">1</span>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Continuous Autonomous Crawling</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">
                    Our distributed crawler fleet tracks product feeds across leading marketplaces (Amazon, Flipkart, Croma, Reliance Digital, Myntra) 24 hours a day, monitoring real-time price fluctuations and stock levels.
                </p>
            </div>

            <!-- Pipeline Step 2 -->
            <div class="my-6 p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 font-bold text-sm flex items-center justify-center">2</span>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Multi-Store Search &amp; Cross-Store Comparison</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">
                    When a price drop occurs, our internal search engine scans identical products across all major competing retailers. If an item is cheaper on another store, our deterministic gate blocks the listing or flags it as &ldquo;Cheaper Elsewhere&rdquo;.
                </p>
            </div>

            <!-- Pipeline Step 3 -->
            <div class="my-6 p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 font-bold text-sm flex items-center justify-center">3</span>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">90-Day Historical Trend Analysis</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">
                    We compare the current drop against 30, 90, and 180-day historical prices to determine whether the discount is genuine or merely an inflated MRP illusion. Deals scoring below threshold are rejected from publication.
                </p>
            </div>

            <!-- Pipeline Step 4 -->
            <div class="my-6 p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 font-bold text-sm flex items-center justify-center">4</span>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Human Editorial Quality Audit</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">
                    Qualified deals receive human editorial review. Editors verify seller integrity, check return policies, test product specs, and author comprehensive evergreen buying guides.
                </p>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Why Trust Our Platform?</h2>
            <ul class="list-disc pl-6 space-y-2 mt-4 mb-6 text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                <li><strong class="text-gray-900 dark:text-white font-semibold">Zero Sponsored Inflations:</strong> Merchants cannot pay to increase deal scores or buy top rankings.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Real-Time Expiry Detection:</strong> When deals expire, our background checkers automatically archive them within minutes.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Unbiased Editorial Voice:</strong> If a deal is subpar, we say so plainly with our deterministic &ldquo;WAIT&rdquo; recommendations.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

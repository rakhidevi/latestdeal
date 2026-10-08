@extends('layouts.app')

@section('meta')
    <title>Affiliate Disclosure | LatestDeal.in</title>
    <meta name="description" content="Read LatestDeal's affiliate disclosure to understand how we earn commissions to keep our shopping intelligence platform 100% free for everyone.">
    <link rel="canonical" href="{{ url('/affiliate-disclosure') }}">
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
            <span class="text-slate-900 dark:text-white font-bold" aria-current="page">Affiliate Disclosure</span>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Commercial Transparency
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                Affiliate <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">Disclosure</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed">
                How LatestDeal keeps its deal discovery engine 100% free for everyone while maintaining complete editorial autonomy.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                <span>Compliance & Partner Transparency</span>
                <span>•</span>
                <span>Updated {{ date('F Y') }}</span>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-10 md:p-12 shadow-xl shadow-slate-200/40 dark:shadow-none">
            <div class="prose prose-slate dark:prose-invert max-w-none prose-headings:font-black prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:tracking-tight prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-p:leading-relaxed prose-li:text-slate-600 dark:prose-li:text-slate-300 prose-strong:text-slate-900 dark:prose-strong:text-white prose-a:text-red-600 dark:prose-a:text-red-400 hover:prose-a:underline">
                
                <h2>1. Commitment to Total Transparency</h2>
                <p>
                    LatestDeal.in was founded to assist shoppers in navigating confusing discount cycles, holiday sales, and volatile pricing. We never charge users for access, never require paid subscriptions, and never hide deal recommendations behind a paywall.
                </p>

                <h2>2. How We Earn Income (Affiliate Links)</h2>
                <p>
                    To sustain our server infrastructure, autonomous scraping daemons, and editorial staff, we participate in affiliate marketing programs with online retail partners:
                </p>
                <ul>
                    <li>When you click on a deal card or link on LatestDeal and subsequently complete a purchase on a merchant site (e.g. Amazon.in, Flipkart, Croma), we may receive an affiliate commission from that retailer.</li>
                    <li>This referral payment comes at <strong>zero extra cost to you</strong>. You pay the exact same price (or lower, if using our verified coupon codes) as any direct shopper on that platform.</li>
                </ul>

                <!-- Official Amazon Notice -->
                <div class="not-prose my-6 p-6 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                    <h4 class="font-black text-amber-900 dark:text-amber-300 text-sm mb-1">Amazon Associates Program Statement</h4>
                    <p class="text-xs text-amber-800 dark:text-amber-400 leading-relaxed">
                        LatestDeal.in is a participant in the Amazon Associates Program, an affiliate advertising initiative designed to provide a means for websites to earn advertising fees by linking to Amazon.in and affiliated marketplaces.
                    </p>
                </div>

                <h2>3. Editorial Independence Guaranteed</h2>
                <p>
                    <strong>Do affiliate commission rates influence our recommendations? No.</strong>
                </p>
                <p>
                    Our deal discovery algorithms evaluate price drops strictly based on mathematical price drops, seller reputation, and historical MRP comparisons. Products with higher affiliate payout rates are never favored over superior products with lower or zero commissions. If a product fails our quality benchmarks, we do not feature it, regardless of potential commercial return.
                </p>

                <h2>4. Clear Product Link Labeling</h2>
                <p>
                    To ensure consumer clarity, our deal buttons (such as "Get Deal Now" or "View Deal") clearly route through our transparent redirect engine (`/go/{hash_id}`). You will always arrive on the merchant's legitimate, official product detail page.
                </p>

                <h2>5. Questions Regarding Partnerships</h2>
                <p>
                    If you have questions about our affiliate relationships or would like to discuss business partnerships, please contact us at <a href="mailto:support@latestdeal.in">support@latestdeal.in</a>.
                </p>
            </div>
        </div>

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="mt-10 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Related policies and editorial resources:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('editorial.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Policy →</a>
                <a href="{{ route('how.it.works') }}" class="text-red-600 dark:text-red-400 hover:underline">How It Works →</a>
                <a href="{{ route('privacy') }}" class="text-red-600 dark:text-red-400 hover:underline">Privacy Policy →</a>
                <a href="{{ route('terms') }}" class="text-red-600 dark:text-red-400 hover:underline">Terms of Service →</a>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('meta')
    <title>Terms of Service | LatestDeal.in</title>
    <meta name="description" content="Review the Terms of Service governing your use of LatestDeal.in, a consumer shopping intelligence and deal discovery platform.">
    <link rel="canonical" href="{{ url('/terms') }}">
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
            <span class="text-slate-900 dark:text-white font-bold" aria-current="page">Terms of Service</span>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Legal Terms & Agreements
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                Terms of <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">Service</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed">
                Please read these terms carefully before accessing or using LatestDeal.in.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                <span>Governed by LatestDeal Operations</span>
                <span>•</span>
                <span>Updated {{ date('F Y') }}</span>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-10 md:p-12 shadow-xl shadow-slate-200/40 dark:shadow-none">
            <div class="prose prose-slate dark:prose-invert max-w-none prose-headings:font-black prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:tracking-tight prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-p:leading-relaxed prose-li:text-slate-600 dark:prose-li:text-slate-300 prose-strong:text-slate-900 dark:prose-strong:text-white prose-a:text-red-600 dark:prose-a:text-red-400 hover:prose-a:underline">
                
                <h2>1. Acceptance of Terms</h2>
                <p>
                    By accessing or using LatestDeal.in (the "Platform"), you agree to be bound by these Terms of Service. If you do not agree to all terms and conditions stated herein, please refrain from using the platform.
                </p>

                <h2>2. Nature of Service</h2>
                <p>
                    LatestDeal is a consumer shopping intelligence engine. We discover, aggregate, score, and analyze deals from third-party retailers (such as Amazon.in, Flipkart, Croma, and Reliance Digital). <strong>We are not a retailer, manufacturer, or direct seller.</strong> We do not take payments, fulfill orders, or manage shipping logistics.
                </p>

                <h2>3. Price Volatility & Availability Disclaimer</h2>
                <p>
                    E-commerce retailers adjust prices, stock levels, and promotional vouchers dynamically. While our autonomous daemons monitor changes around the clock:
                </p>
                <ul>
                    <li>The price and availability displayed on the merchant checkout screen represents the sole binding transaction terms.</li>
                    <li>LatestDeal is not liable for price fluctuations that occur between our scrape cycles or for stock depletion on merchant storefronts.</li>
                    <li>Users are encouraged to use the <strong>"Verify Live Price"</strong> tool on any deal page to check instant live pricing.</li>
                </ul>

                <h2>4. Affiliate Partnerships & Monetization</h2>
                <p>
                    We participate in affiliate marketing programs. When you click our outbound links to partner merchants, we may earn an affiliate commission on qualifying purchases at zero added cost to you. This relationship is governed by our public <a href="{{ route('affiliate.disclosure') }}" class="font-bold">Affiliate Disclosure</a> and never dictates our editorial verdicts.
                </p>

                <h2>5. Intellectual Property</h2>
                <p>
                    All original editorial analyses, buying guides, software tools, website design, and proprietary algorithms on LatestDeal are protected by intellectual property laws. Third-party brand names, logos, and trademarks (such as Amazon, Apple, Samsung) remain the exclusive property of their respective trademark holders and are utilized solely for factual identification.
                </p>

                <h2>6. Acceptable Use Policy</h2>
                <p>You agree not to:</p>
                <ul>
                    <li>Attempt to disrupt, exploit, or launch denial-of-service attacks against our platform infrastructure.</li>
                    <li>Deploy aggressive automated bots to scrape our content in a manner that impairs system performance.</li>
                    <li>Misrepresent affiliation with LatestDeal or distribute unauthorized derivative software.</li>
                </ul>

                <h2>7. Limitation of Liability</h2>
                <p>
                    To the maximum extent permitted by applicable law, LatestDeal and its operators shall not be held liable for any direct, indirect, incidental, or consequential damages resulting from your use of the site or purchases made through third-party retailers.
                </p>
            </div>
        </div>

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="mt-10 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Related legal policies:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('privacy') }}" class="text-red-600 dark:text-red-400 hover:underline">Privacy Policy →</a>
                <a href="{{ route('cookie') }}" class="text-red-600 dark:text-red-400 hover:underline">Cookie Policy →</a>
                <a href="{{ route('affiliate.disclosure') }}" class="text-red-600 dark:text-red-400 hover:underline">Affiliate Disclosure →</a>
                <a href="{{ route('contact') }}" class="text-red-600 dark:text-red-400 hover:underline">Contact Us →</a>
            </div>
        </div>

    </div>
</div>
@endsection

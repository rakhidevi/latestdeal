@extends('layouts.app')

@section('meta')
    <title>Corrections & Updates Policy | LatestDeal.in</title>
    <meta name="description" content="LatestDeal is committed to accuracy, transparency, and rapid corrections. Read our full corrections and updates policy.">
    <link rel="canonical" href="{{ url('/corrections-policy') }}">
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
            <span class="text-slate-900 dark:text-white font-bold" aria-current="page">Corrections Policy</span>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Accuracy & Accountability
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                Corrections & Updates <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">Policy</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed">
                Transparency and factual accuracy are the bedrock of our platform. When an error is made, we correct it promptly and visibly.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                <span>Governed by Editorial Oversight</span>
                <span>•</span>
                <span>Updated {{ date('F Y') }}</span>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-10 md:p-12 shadow-xl shadow-slate-200/40 dark:shadow-none">
            <div class="prose prose-slate dark:prose-invert max-w-none prose-headings:font-black prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:tracking-tight prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-p:leading-relaxed prose-li:text-slate-600 dark:prose-li:text-slate-300 prose-strong:text-slate-900 dark:prose-strong:text-white prose-a:text-red-600 dark:prose-a:text-red-400 hover:prose-a:underline">
                
                <h2>1. Our Commitment to Factual Accuracy</h2>
                <p>
                    At <strong>LatestDeal.in</strong>, our mission is to deliver dependable shopping intelligence. Whether calculating real price drops, comparing historical MRPs, or evaluating product specifications, our editorial and automated pipelines adhere to rigorous verification standards.
                </p>

                <h2>2. How We Handle Corrections</h2>
                <p>When an error occurs—whether a typo, an outdated retailer price, or a misstated specification—we take the following steps:</p>
                <ul>
                    <li><strong>Prompt Verification:</strong> Reported inaccuracies are reviewed against primary retailer data within 12 business hours.</li>
                    <li><strong>Transparent Update:</strong> We correct the record directly in the article or deal summary. For substantial factual adjustments in our buying guides, an editorial revision note is appended at the bottom of the article noting the change and timestamp.</li>
                    <li><strong>Price Volatility Notices:</strong> E-commerce prices fluctuate dynamically. If a deal price changes before our automated price verification loop runs, our system displays real-time price verification status indicators to prevent user deception.</li>
                </ul>

                <h2>3. Price Drops & Expired Deals</h2>
                <p>
                    Deals, coupons, and promotional discounts on partner merchants (Amazon, Flipkart, Croma, etc.) are time-sensitive and subject to merchant inventory limits. If an offer has expired or reverted to full price:
                </p>
                <ul>
                    <li>Users can click <strong>"Verify Live Price"</strong> on any deal page to immediately fetch the latest live price.</li>
                    <li>Expired deals are automatically flagged as inactive once our crawlers detect price restoration or stock depletion.</li>
                </ul>

                <h2>4. Submitting a Correction or Error Report</h2>
                <p>
                    We actively welcome feedback and correction requests from our readers and community. If you notice an inaccuracy in any buying guide, price chart, or deal card, please contact our editorial desk:
                </p>
                
                <div class="not-prose my-6 p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                    <h4 class="font-black text-slate-900 dark:text-white text-base mb-2">Editorial Corrections Desk</h4>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
                        Email: <a href="mailto:support@latestdeal.in" class="font-bold text-red-600 dark:text-red-400 hover:underline">support@latestdeal.in</a><br>
                        Subject line: <em>Correction Request: [URL or Product Title]</em>
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Please include the specific page URL, description of the inaccuracy, and any supporting documentation or retailer links.
                    </p>
                </div>

                <h2>5. Policy Revisions</h2>
                <p>
                    This Corrections Policy is reviewed periodically to reflect evolving e-commerce standards, regulatory guidance, and consumer protection best practices.
                </p>
            </div>
        </div>

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="mt-10 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Related policies and editorial resources:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('editorial.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Policy →</a>
                <a href="{{ route('editorial.team') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Team →</a>
                <a href="{{ route('affiliate.disclosure') }}" class="text-red-600 dark:text-red-400 hover:underline">Affiliate Disclosure →</a>
                <a href="{{ route('contact') }}" class="text-red-600 dark:text-red-400 hover:underline">Contact Desk →</a>
            </div>
        </div>

    </div>
</div>
@endsection

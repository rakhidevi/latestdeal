@extends('layouts.app')

@section('meta')
    <title>Corrections & Updates Policy | LatestDeal.in</title>
    <meta name="description" content="LatestDeal is committed to accuracy, transparency, and rapid corrections. Read our full corrections and updates policy.">
    <link rel="canonical" href="{{ url('/corrections-policy') }}">
@endsection

@section('content')
<div class="relative min-h-screen pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-sm font-bold tracking-widest uppercase mb-4 shadow-sm">
                Commitment to Accuracy
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-slate-800 dark:text-white tracking-tight mb-4">
                Corrections & Updates <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-500">Policy</span>
            </h1>
            <p class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">
                Transparency and factual accuracy are the bedrock of our platform. When an error is made, we correct it promptly and visibly.
            </p>
        </div>

        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-white/80 dark:border-slate-800 rounded-3xl p-8 md:p-12 shadow-2xl shadow-slate-200/50 dark:shadow-none">
            <div class="prose prose-lg prose-slate dark:prose-invert max-w-none prose-headings:font-black prose-headings:text-slate-800 dark:prose-headings:text-white">
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
                <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-6 my-6">
                    <p class="font-semibold text-emerald-900 dark:text-emerald-300 mb-1">Editorial Corrections Desk</p>
                    <p class="text-sm text-emerald-800 dark:text-emerald-400">
                        Email: <a href="mailto:support@latestdeal.in" class="underline font-bold text-emerald-700 dark:text-emerald-300">support@latestdeal.in</a><br>
                        Subject line: <em>Correction Request: [URL or Product Title]</em><br>
                        Please include the specific URL, the perceived error, and any supporting documentation or live links.
                    </p>
                </div>

                <h2>5. Policy Revisions</h2>
                <p>
                    This Corrections Policy is reviewed periodically to reflect evolving e-commerce standards, regulatory guidance, and consumer protection best practices.
                </p>
                <p class="text-sm text-slate-400 mt-8">
                    Last Updated: {{ date('F Y') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

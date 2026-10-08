@extends('layouts.app')

@section('meta')
    <title>Corrections Policy | LatestDeal.in</title>
    <meta name="description" content="LatestDeal's transparent corrections policy, commitments to error remediation, and reader reporting procedures.">
    <link rel="canonical" href="{{ url('/corrections-policy') }}">
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Corrections Policy</h1>
        
        <p class="text-sm font-semibold text-gray-500 dark:text-slate-400 mb-6">Editorial Accountability Desk &bull; Updated {{ date('F Y') }}</p>

        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                LatestDeal is committed to accurate, reliable, and truthful product reporting. When inaccuracies occur, our priority is to correct them quickly and transparently.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">1. Our Commitment to Accuracy</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                Every deal published on LatestDeal undergoes automated price verification against retailer APIs, followed by editorial curation. However, due to the high-velocity nature of e-commerce pricing, flash sales may expire or technical specifications may shift unexpectedly.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">2. Types of Corrections We Handle</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                We triage and resolve issues under three priority tiers:
            </p>
            <ul class="list-disc pl-6 space-y-2 mt-4 mb-6 text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                <li><strong class="text-gray-900 dark:text-white font-semibold">Pricing &amp; Availability Mismatches:</strong> When a product price changes or sells out, our automated crawler verifies and updates or archives the deal status within minutes.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Product Specification Errors:</strong> Inaccuracies in technical specs, model numbers, or compatibility details are reviewed against official manufacturer specifications and corrected immediately.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Buying Guide &amp; Editorial Updates:</strong> Substantive factual changes in our evergreen guides include an editorial revision timestamp at the top of the article.</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">3. How to Report an Error</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                We welcome and appreciate feedback from our community. If you notice an error in any listing or editorial guide:
            </p>
            <ul class="list-disc pl-6 space-y-2 mt-4 mb-6 text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                <li>Email us directly at <a href="mailto:corrections@latestdeal.in" class="text-red-600 hover:underline font-semibold">corrections@latestdeal.in</a> with the page URL.</li>
                <li>Include a brief note describing the observed error (e.g., outdated price, incorrect specification, expired voucher).</li>
                <li>Our editorial desk reviews reports within 24 hours and implements necessary corrections.</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">4. Archival Transparency</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                Deals that are fully expired or discontinued remain archived in our database for historical price intelligence, clearly badged as <strong class="text-gray-900 dark:text-white font-semibold">Expired</strong> so shoppers are never misled into expecting outdated pricing.
            </p>
        </div>
    </div>
</div>
@endsection

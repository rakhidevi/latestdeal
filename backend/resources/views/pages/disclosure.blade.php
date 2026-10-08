@extends('layouts.app')

@section('meta')
    <title>Affiliate Disclosure | LatestDeal.in</title>
    <meta name="description" content="LatestDeal's complete affiliate disclosure, revenue model, and editorial separation policies.">
    <link rel="canonical" href="{{ url('/affiliate-disclosure') }}">
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Affiliate Disclosure</h1>
        
        <p class="text-sm font-semibold text-gray-500 dark:text-slate-400 mb-6">Full Transparency &bull; Updated {{ date('F Y') }}</p>

        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                In compliance with regulatory standards and consumer advocacy principles, LatestDeal provides full disclosure regarding how this website is monetized and how our partnerships work.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">1. How LatestDeal Earns Revenue</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                LatestDeal is 100% free for shoppers. We never charge subscription fees, paywalls, or premium tiers. To sustain our cloud servers, crawling fleet, and editorial team, we participate in affiliate marketing programs.
            </p>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                When you click on certain product links on LatestDeal and make a qualifying purchase, the retailer may pay us a small commission. <strong class="text-gray-900 dark:text-white font-semibold">This comes at zero additional cost to you.</strong> In fact, our deals frequently include exclusive coupon codes or verified pricing lower than standard retail rates.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">2. Amazon Associates Disclosure</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                LatestDeal is a participant in the Amazon Associates Program, an affiliate advertising program designed to provide a means for sites to earn advertising fees by advertising and linking to Amazon.in. As an Amazon Associate, we earn from qualifying purchases.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">3. Strict Separation of Editorial and Commerce</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                We maintain an uncompromised wall between commercial partnerships and editorial choices:
            </p>
            <ul class="list-disc pl-6 space-y-2 mt-4 mb-6 text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                <li><strong class="text-gray-900 dark:text-white font-semibold">No Sponsored Deal Rankings:</strong> Retailers cannot pay for positive reviews, inflated scores, or front-page placement.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Deterministic Price Rules:</strong> Our deals are ranked by price drops, historical lows, and product merit—never affiliate payout rates.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Equal Multi-Store Comparisons:</strong> Our multi-store comparator displays the lowest price among Amazon, Flipkart, Croma, and Reliance Digital regardless of which retailer pays a commission.</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">4. Questions &amp; Inquiries</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                If you have questions about our affiliate disclosure or editorial independence, please email us at <a href="mailto:affiliates@latestdeal.in" class="text-red-600 hover:underline font-semibold">affiliates@latestdeal.in</a>.
            </p>
        </div>
    </div>
</div>
@endsection

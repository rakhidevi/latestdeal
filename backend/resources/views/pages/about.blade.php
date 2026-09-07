@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">About LatestDeal</h1>
        
        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
            <p>Welcome to LatestDeal. Our mission is simple: to help you find the best online discounts, make informed purchasing decisions, and avoid misleading prices.</p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">What We Do</h2>
            <p>The internet is flooded with "deals" that are actually just regular prices disguised with artificially inflated MSRPs. LatestDeal cuts through the noise. We monitor major online retailers, track historical pricing, and highlight genuine discounts that offer actual value to the consumer.</p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">How Deals are Discovered and Selected</h2>
            <p>Our platform uses a combination of automated aggregation and manual editorial review. Our software monitors product feeds and price drops across trusted e-commerce platforms. However, aggregation alone isn't enough. Our editorial team reviews the automated feed to ensure the deals we highlight represent high-quality products at historically significant price lows.</p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Our Editorial Standards</h2>
            <p>We strictly adhere to the following principles:</p>
            <ul class="list-disc pl-6 space-y-2 mt-4">
                <li><strong>No Pay-to-Play:</strong> Brands cannot pay us to artificially boost a poor deal to our front page.</li>
                <li><strong>Verified Pricing:</strong> We rely on historical data, not just the "List Price" provided by the retailer, to calculate savings.</li>
                <li><strong>Transparency:</strong> We clearly disclose our affiliate relationships and how we fund our operations.</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Updates and Accuracy</h2>
            <p>E-commerce pricing is volatile. Deals can expire or sell out in minutes. While we update our database frequently, prices at the retailer may change by the time you click. If you spot an expired or incorrect deal, please let us know via our Contact page so we can update our platform.</p>
            
            <p class="mt-8">Thank you for trusting LatestDeal to guide your shopping.</p>
        </div>
    </div>
</div>
@endsection

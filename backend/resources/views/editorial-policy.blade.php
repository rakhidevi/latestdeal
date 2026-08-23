@extends('layouts.app')

@section('meta')
    <title>Editorial Policy | LatestDeal.in</title>
    <meta name="description" content="Discover LatestDeal's strict editorial policies, our commitment to unbiased deal analysis, and our review methodology.">
    <link rel="canonical" href="{{ url('/editorial-policy') }}">
@endsection

@section('content')
<div class="relative min-h-screen pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-violet-50 border border-violet-100 text-violet-600 text-sm font-bold tracking-widest uppercase mb-4 shadow-sm">
                Editorial Independence
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-slate-800 tracking-tight mb-4">
                Editorial <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-500 to-fuchsia-500">Policy</span>
            </h1>
        </div>

        <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 md:p-12 shadow-2xl shadow-slate-200/50">
            <div class="prose prose-lg prose-slate max-w-none prose-headings:font-black prose-headings:text-slate-800">
                <h2>Our Editorial Standards</h2>
                <p>LatestDeal was built on a simple premise: <strong>Help consumers avoid fake discounts and save real money.</strong> To achieve this, our editorial team operates strictly independently from our affiliate or business operations.</p>

                <h2>Review & Deal Methodology</h2>
                <p>When curating deals or writing buying guides, we evaluate products based on:</p>
                <ul>
                    <li><strong>Historical Price Data:</strong> Is this actually the lowest price in 30, 60, or 90 days?</li>
                    <li><strong>Brand Reputation:</strong> We filter out untrustworthy, fly-by-night sellers.</li>
                    <li><strong>Verified Specifications:</strong> We never invent features. We source data directly from manufacturers.</li>
                    <li><strong>True Value:</strong> A 90% discount on a low-quality item is still a bad deal. We prioritize quality over just the discount percentage.</li>
                </ul>
                
                <h2>AI Assistance Disclosure</h2>
                <p>LatestDeal utilizes advanced AI algorithms to track price volatility, summarize product features, and generate initial deal scores. However, AI does not run our site unchecked. Our editorial team reviews AI-generated insights, ensuring accuracy, preventing hallucinations, and adding human context to buying advice.</p>

                <h2>Corrections Policy</h2>
                <p>We strive for 100% accuracy, but deals expire quickly and prices fluctuate. If you spot an error in our content, please let us know at <a href="mailto:support@latestdeal.in">support@latestdeal.in</a>, and we will correct it immediately.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('meta')
    <title>Editorial Policy | LatestDeal</title>
    <meta name="description" content="Discover LatestDeal's strict editorial policies, our commitment to unbiased deal analysis, and our review methodology.">
    <link rel="canonical" href="{{ url('/editorial-policy') }}">
@endsection

@section('content')
<x-info.page-container>
    <x-info.page-header title="Editorial Policy" label="Editorial Independence">
        How a deal becomes published on LatestDeal.
    </x-info.page-header>
    
    <x-info.last-updated date="{{ date('F d, Y') }}" />

    <!-- Pipeline Visual Section -->
    <div class="mb-16">
        <h2 class="text-2xl font-black text-slate-900 mb-8">The Deal Intelligence Pipeline</h2>
        
        <x-info.pipeline-step number="01" title="Discovery">
            Our automated crawlers track millions of products across top e-commerce platforms, monitoring for sudden price drops 24/7.
        </x-info.pipeline-step>

        <x-info.pipeline-step number="02" title="Product Validation">
            We filter out untrustworthy merchants, fly-by-night sellers, and low-quality brands to ensure the product itself is worth buying.
        </x-info.pipeline-step>
        
        <x-info.pipeline-step number="03" title="Price Verification">
            The system cross-references historical price data. We ensure the discount is mathematically real, not just a fake drop from an artificially inflated MSRP.
        </x-info.pipeline-step>
        
        <x-info.pipeline-step number="04" title="Duplicate Detection">
            We check if the deal is already active or if a better variant exists to avoid spamming our users with redundant listings.
        </x-info.pipeline-step>

        <x-info.pipeline-step number="05" title="AI-Assisted Editorial">
            Our proprietary language models synthesize product specifications, highlight key features, and generate an initial "Our Take" summary.
        </x-info.pipeline-step>

        <x-info.pipeline-step number="06" title="Factuality QA">
            The AI output is strictly validated against raw manufacturer data to prevent hallucinations. The AI is never allowed to invent specifications.
        </x-info.pipeline-step>

        <x-info.pipeline-step number="07" title="Human Review">
            A human editor reviews the final package to ensure it meets our editorial standards and provides genuine value to the consumer.
        </x-info.pipeline-step>

        <x-info.pipeline-step number="08" title="Publication" last="true">
            The deal goes live on the platform, fully vetted and mathematically verified.
        </x-info.pipeline-step>
    </div>

    <x-info.section id="standards" number="" title="Our Editorial Standards">
        <p>LatestDeal was built on a simple premise: <strong>Help consumers avoid fake discounts and save real money.</strong> To achieve this, our editorial team operates strictly independently from our affiliate or business operations.</p>
    </x-info.section>

    <x-info.section id="methodology" number="" title="Review & Deal Methodology">
        <p>When curating deals or writing buying guides, we evaluate products based on:</p>
        <ul>
            <li><strong>Historical Price Data:</strong> Is this actually the lowest price in 30, 60, or 90 days?</li>
            <li><strong>Brand Reputation:</strong> We filter out untrustworthy, fly-by-night sellers.</li>
            <li><strong>Verified Specifications:</strong> We never invent features. We source data directly from manufacturers.</li>
            <li><strong>True Value:</strong> A 90% discount on a low-quality item is still a bad deal. We prioritize quality over just the discount percentage.</li>
        </ul>
    </x-info.section>

    <x-info.section id="corrections" number="" title="Corrections Policy">
        <p>We strive for 100% accuracy, but deals expire quickly and prices fluctuate. If you spot an error in our content, please let us know at <a href="mailto:support@latestdeal.in" class="text-red-600 hover:underline">support@latestdeal.in</a>, and we will correct it immediately.</p>
    </x-info.section>

</x-info.page-container>
@endsection

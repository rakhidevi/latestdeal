@extends('layouts.app')

@section('meta')
    <title>Editorial Accountability | LatestDeal</title>
    <meta name="description" content="Learn who reviews deals, how editorial decisions are made, and what our AI is not allowed to do.">
    <link rel="canonical" href="{{ url('/editorial-team') }}">
@endsection

@section('content')
<x-info.page-container>
    <x-info.page-header title="Editorial Accountability" label="The Humans Behind The AI">
        Who reviews deals and how decisions are made.
    </x-info.page-header>
    
    <x-info.last-updated date="{{ date('F d, Y') }}" />

    <x-info.section id="who-reviews" number="01" title="Who Reviews Deals?">
        <p>While our autonomous bots discover price drops across the web, human editors are responsible for the final publication decision. Our editors are deal hunters and category experts who review historical pricing, brand reputation, and consumer value before allowing any deal to reach the homepage.</p>
    </x-info.section>

    <x-info.section id="decisions" number="02" title="How Editorial Decisions Are Made">
        <p>A deal is only published if it meets three strict criteria:</p>
        <ul>
            <li><strong>Mathematical Verification:</strong> The discount must be real compared to the 90-day historical average, not just a drop from an artificially inflated MSRP.</li>
            <li><strong>Merchant Trust:</strong> We only publish deals from verified, trusted sellers on major platforms (like Amazon or Flipkart). We do not link to fly-by-night operators.</li>
            <li><strong>Intrinsic Value:</strong> A 90% discount on a low-quality, unusable item is still a bad deal. Editors prioritize the quality of the product over the sheer size of the discount.</li>
        </ul>
    </x-info.section>

    <x-info.section id="ai-usage" number="03" title="How AI is Used">
        <p>We use AI extensively in the backend to process massive amounts of data. Specifically, AI is used for:</p>
        <ul>
            <li>Synthesizing hundreds of product specifications into readable summaries.</li>
            <li>Analyzing price volatility charts to flag suspicious price hikes right before major sale events.</li>
            <li>Generating the initial "Our Take" draft summarizing the pros, cons, and bottom-line value of a product based on thousands of user reviews.</li>
        </ul>
    </x-info.section>

    <x-info.section id="ai-restrictions" number="04" title="What AI is NOT Allowed To Do">
        <p>We have strict guardrails on our artificial intelligence engine to ensure absolute trustworthiness:</p>
        <ul>
            <li><strong>AI cannot publish a deal:</strong> Every deal requires a human editor to click "Publish".</li>
            <li><strong>AI cannot invent specifications:</strong> Hallucinations are strictly prohibited. The AI is restricted to only parsing and formatting verified manufacturer data.</li>
            <li><strong>AI cannot alter historical pricing:</strong> The mathematical verification engine is hardcoded in Python; the AI cannot manipulate the discount percentages.</li>
        </ul>
    </x-info.section>

    <x-info.callout title="Read the Full Policy" icon="info">
        <p class="mb-4">Want to see the exact 8-step pipeline every deal goes through?</p>
        <a href="/editorial-policy" class="font-bold text-red-600 hover:underline">Read our complete Editorial Policy &rarr;</a>
    </x-info.callout>

</x-info.page-container>
@endsection

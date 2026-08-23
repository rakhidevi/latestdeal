@extends('layouts.app')

@section('meta')
    <title>How LatestDeal Works | LatestDeal</title>
    <meta name="description" content="Discover the technology behind LatestDeal's autonomous deal discovery and price verification engine.">
    <link rel="canonical" href="{{ url('/how-it-works') }}">
@endsection

@section('content')
<x-info.page-container>
    <x-info.page-header title="How LatestDeal Works" label="Our Technology">
        The intelligence engine that protects you from fake discounts.
    </x-info.page-header>
    
    <x-info.last-updated date="{{ date('F d, Y') }}" />

    <!-- 5 Step Process Cards -->
    
    <x-info.trust-card title="Search" icon='<svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>'>
        <p>Tell us exactly what you're looking for. Unlike traditional deal sites that just dump a feed of random offers, LatestDeal is built around search intent. Whether it's "Nike running shoes" or "4K TVs under $500", we focus on finding what you actually want.</p>
    </x-info.trust-card>

    <x-info.trust-card title="Discover" icon='<svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>'>
        <p>Our autonomous engine constantly monitors top e-commerce platforms. It identifies thousands of price drops every hour, long before they are advertised to the public, surfacing the absolute best offers available across the web.</p>
    </x-info.trust-card>

    <x-info.trust-card title="Verify" icon='<svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'>
        <p>A "90% off" tag means nothing if the original price was artificially inflated. We mathematically verify every deal against historical pricing data to calculate the <em>true</em> discount, instantly discarding fake promotions and duplicate listings.</p>
    </x-info.trust-card>

    <x-info.trust-card title="Understand" icon='<svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>'>
        <p>Our AI-assisted editorial process breaks down complex product specifications and extracts the most important features. We summarize the pros, cons, and bottom-line value so you don't have to read through hundreds of conflicting user reviews.</p>
    </x-info.trust-card>

    <x-info.trust-card title="Decide" icon='<svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>'>
        <p>Only deals that pass our strict AI and human curation thresholds make it to the platform. We present you with a clean, fact-based deal page so you can make a confident, informed purchasing decision.</p>
    </x-info.trust-card>

</x-info.page-container>
@endsection

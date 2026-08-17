@extends('layouts.app')

@section('meta')
    <title>How LatestDeal Works | LatestDeal.in</title>
    <meta name="description" content="Discover the technology behind LatestDeal's autonomous deal discovery and price verification engine.">
    <link rel="canonical" href="{{ url('/how-it-works') }}">
@endsection

@section('content')
<div class="relative min-h-screen pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-cyan-50 border border-cyan-100 text-cyan-600 text-sm font-bold tracking-widest uppercase mb-4 shadow-sm">
                Our Technology
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-slate-800 tracking-tight mb-4">
                How <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-500 to-blue-500">LatestDeal</span> Works
            </h1>
        </div>

        <div class="space-y-8">
            <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 shadow-xl shadow-slate-200/50 flex gap-6 items-start">
                <div class="p-4 bg-cyan-100 text-cyan-600 rounded-2xl shrink-0 mt-1">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-3">1. Autonomous Discovery</h3>
                    <p class="text-slate-600 text-lg leading-relaxed">Our custom built Python scrapers constantly monitor top e-commerce websites across hundreds of categories. They identify thousands of price drops every hour, long before they are advertised to the public.</p>
                </div>
            </div>

            <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 shadow-xl shadow-slate-200/50 flex gap-6 items-start">
                <div class="p-4 bg-blue-100 text-blue-600 rounded-2xl shrink-0 mt-1">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-3">2. Historical Verification</h3>
                    <p class="text-slate-600 text-lg leading-relaxed">A "90% off" tag means nothing if the original price was artificially inflated. We cross-reference every deal against historical pricing data to calculate the <em>true</em> discount.</p>
                </div>
            </div>

            <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 shadow-xl shadow-slate-200/50 flex gap-6 items-start">
                <div class="p-4 bg-purple-100 text-purple-600 rounded-2xl shrink-0 mt-1">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-3">3. AI Trust Scoring</h3>
                    <p class="text-slate-600 text-lg leading-relaxed">We feed the verified deal data into our AI engine. The AI analyzes seller reputation, product reviews, and the rarity of the discount to generate a definitive "Trust Score" (1-100).</p>
                </div>
            </div>

            <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 shadow-xl shadow-slate-200/50 flex gap-6 items-start">
                <div class="p-4 bg-emerald-100 text-emerald-600 rounded-2xl shrink-0 mt-1">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-3">4. Human Curation & Publishing</h3>
                    <p class="text-slate-600 text-lg leading-relaxed">Only deals that pass the AI Trust Score threshold are reviewed by our editors and instantly published to our website and Telegram channels, ensuring you never miss a verified steal.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('meta')
    <title>{{ $seoMeta['title'] ?? 'Find the Best Global Deals, Offers, & Coupons | LatestDeal' }}</title>
    <meta name="description" content="{{ $seoMeta['description'] ?? 'Discover top discounts, live offers, and verified coupons from global marketplaces like Amazon. Our AI scores deals so you always save money.' }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] ?? url()->current() }}">
    @if(isset($deals) && $deals instanceof \Illuminate\Pagination\AbstractPaginator)
        @if($deals->previousPageUrl())
            <link rel="prev" href="{{ $deals->previousPageUrl() }}">
        @endif
        @if($deals->nextPageUrl())
            <link rel="next" href="{{ $deals->nextPageUrl() }}">
        @endif
    @endif
    
    <meta property="og:title" content="{{ $seoMeta['og_title'] ?? 'Find the Best Global Deals, Offers, & Coupons | LatestDeal' }}">
    <meta property="og:description" content="{{ $seoMeta['og_description'] ?? 'Discover top discounts, live offers, and verified coupons from global marketplaces like Amazon.' }}">
    <meta property="og:url" content="{{ $seoMeta['og_url'] ?? url()->current() }}">
    <meta property="og:type" content="{{ $seoMeta['og_type'] ?? 'website' }}">
    <meta property="og:image" content="{{ asset('/images/logo.png') }}">
    <meta name="twitter:card" content="{{ $seoMeta['twitter_card'] ?? 'summary_large_image' }}">

    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "WebSite",
      "name": "LatestDeal",
      "url": @json(url('/')),
      "potentialAction": {
        "@type": "SearchAction",
        "target": @json(url('/') . '?q={search_term_string}'),
        "query-input": "required name=search_term_string"
      }
    }
    </script>
    
    @if(isset($schema))
    <script type="application/ld+json">
    {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
@endsection

@section('hero')
  <style>
    [x-cloak] { display: none !important; }
    /* Hide scrollbar for Chrome, Safari, Opera, Edge and Firefox */
    .no-scrollbar::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    .no-scrollbar {
        -ms-overflow-style: none !important;  /* IE and Edge */
        scrollbar-width: none !important;  /* Firefox */
    }
  </style>

@if(request()->is('/'))
<div class="bg-white pt-16 pb-12 sm:pt-24 sm:pb-16 lg:pb-20 border-b border-gray-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        
        <h1 class="text-4xl font-black text-gray-900 sm:text-5xl md:text-6xl tracking-tight mb-4">
            Find the deal you're<br/><span class="text-red-600">actually looking for.</span>
        </h1>
        
        <form action="/search" method="GET" class="max-w-2xl mx-auto mt-10 relative">
            <div class="relative flex items-center shadow-sm">
                <svg class="absolute left-4 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="q" placeholder="What are you looking for?" class="w-full pl-14 pr-4 py-4 sm:py-5 bg-white border border-gray-200 rounded-2xl text-lg font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all placeholder-gray-400 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
            </div>
            
            <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm font-bold text-gray-500">
                <span>Popular:</span>
                <a href="/search?q=puma+shoes" class="hover:text-red-600 transition-colors">Puma Shoes</a>
                <span class="text-gray-300">&middot;</span>
                <a href="/search?q=samsung+tv" class="hover:text-red-600 transition-colors">Samsung TV</a>
                <span class="text-gray-300">&middot;</span>
                <a href="/search?q=iphone" class="hover:text-red-600 transition-colors">iPhone</a>
                <span class="text-gray-300">&middot;</span>
                <a href="/search?q=sneakers" class="hover:text-red-600 transition-colors">Sneakers</a>
                <span class="text-gray-300">&middot;</span>
                <a href="/search?q=laptops" class="hover:text-red-600 transition-colors">Laptops</a>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('content')
<section class="space-y-6">

    @if(isset($trendingDeals) && $trendingDeals->isNotEmpty())
        <div class="mt-8 mb-10">
            <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                        <span class="text-2xl">🔥</span> Today's Best Deals
                    </h2>
                </div>
                <a href="/search?sort=trending" class="text-sm font-bold text-red-600 hover:text-red-700 flex items-center gap-1">
                    View all 
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
            <div class="flex overflow-x-auto gap-4 pb-6 snap-x snap-mandatory scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden -mx-4 px-4 sm:mx-0 sm:px-0">
                @foreach($trendingDeals->take(5) as $deal)
                    <div class="snap-start shrink-0 w-[280px] sm:w-[320px]">
                        <x-deal-card :deal="$deal" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Popular Categories -->
    <div class="mb-12 mt-12 border-t border-gray-100 pt-10">
        <h2 class="text-2xl font-black text-gray-900 mb-6">Popular Categories</h2>
        <div class="flex flex-wrap gap-3 sm:gap-4">
            <a href="/search?sort=categories&q=electronics" class="px-6 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl transition-all shadow-sm">Electronics</a>
            <a href="/search?sort=categories&q=fashion" class="px-6 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl transition-all shadow-sm">Fashion</a>
            <a href="/search?sort=categories&q=home" class="px-6 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl transition-all shadow-sm">Home</a>
            <a href="/search?sort=categories&q=beauty" class="px-6 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl transition-all shadow-sm">Beauty</a>
            <a href="/search?sort=categories&q=grocery" class="px-6 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl transition-all shadow-sm">Grocery</a>
            <a href="/search?sort=categories&q=mobiles" class="px-6 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl transition-all shadow-sm">Mobiles</a>
            <a href="/search?sort=categories&q=appliances" class="px-6 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl transition-all shadow-sm">Appliances</a>
            <a href="/search?sort=categories&q=footwear" class="px-6 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl transition-all shadow-sm">Footwear</a>
            <a href="/search?sort=categories&q=gaming" class="px-6 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl transition-all shadow-sm">Gaming</a>
        </div>
    </div>

    <!-- Popular Stores -->
    <div class="mb-12 border-t border-gray-100 pt-10">
        <h2 class="text-2xl font-black text-gray-900 mb-6">Popular Stores</h2>
        <div class="flex flex-wrap gap-3 sm:gap-4">
            <a href="/search?store=amazon" class="px-8 py-4 bg-white hover:bg-gray-50 border border-gray-200 text-gray-900 font-black text-lg rounded-2xl transition-all shadow-sm flex items-center justify-center min-w-[140px]">Amazon</a>
            <a href="/search?store=flipkart" class="px-8 py-4 bg-white hover:bg-gray-50 border border-gray-200 text-blue-600 font-black text-lg rounded-2xl transition-all shadow-sm flex items-center justify-center min-w-[140px]">Flipkart</a>
            <a href="/search?store=myntra" class="px-8 py-4 bg-white hover:bg-gray-50 border border-gray-200 text-pink-600 font-black text-lg rounded-2xl transition-all shadow-sm flex items-center justify-center min-w-[140px]">Myntra</a>
            <a href="/search?store=croma" class="px-8 py-4 bg-white hover:bg-gray-50 border border-gray-200 text-teal-600 font-black text-lg rounded-2xl transition-all shadow-sm flex items-center justify-center min-w-[140px]">Croma</a>
            <a href="/search?store=tatacliq" class="px-8 py-4 bg-white hover:bg-gray-50 border border-gray-200 text-gray-900 font-black text-lg rounded-2xl transition-all shadow-sm flex items-center justify-center min-w-[140px]">Tata CLiQ</a>
        </div>
    </div>

    <!-- Why Trust LatestDeal? -->
    <div class="mb-16 mt-8 bg-white border border-slate-200 rounded-[2rem] p-8 sm:p-12 shadow-sm">
        <h2 class="text-3xl font-black text-slate-900 mb-8">Why trust LatestDeal?</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-slate-900 font-bold text-lg mb-1">Price checked</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">Verified against source live data.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-slate-900 font-bold text-lg mb-1">Discount calculated</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">Mathematically calculated instead of trusting marketing.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-slate-900 font-bold text-lg mb-1">Deal validated</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">Duplicate protection & intelligent filtering.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-slate-900 font-bold text-lg mb-1">Editorial reviewed</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">AI-assisted analysis + QA. <span class="text-red-600 font-bold">AI doesn't decide what gets published.</span></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

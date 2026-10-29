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
    .no-scrollbar::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    .no-scrollbar {
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
    }
  </style>

@php
    $categories = $categories ?? \Illuminate\Support\Facades\Cache::remember('popular_categories_home', 300, function() {
        return \App\Models\Category::whereNotIn('slug', ['general', 'uncategorized', 'all-other-categories', 'c.url', 'item.url', 'b.url', 'd.url', 'm.url'])
            ->where('slug', 'NOT LIKE', '%.url%')
            ->where('slug', 'NOT LIKE', '%"%')
            ->withCount(['deals as active_deals_count' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->filter(fn($c) => preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $c->slug) && (($c->active_deals_count ?? 0) > 0))
            ->sortByDesc('active_deals_count')
            ->values();
    });
    $merchants = $merchants ?? \Illuminate\Support\Facades\Cache::remember('popular_merchants_home', 300, function() {
        return \App\Models\Merchant::whereNotNull('name')
            ->where('name', '!=', '')
            ->where('name', 'NOT LIKE', '%.url%')
            ->withCount(['deals as active_deals_count' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->filter(fn($m) => !empty($m->name) && (($m->active_deals_count ?? 0) > 0))
            ->sortByDesc('active_deals_count')
            ->values();
    });
    $brands = $brands ?? \Illuminate\Support\Facades\Cache::remember('popular_brands_home', 300, function() {
        return \App\Models\Brand::whereNotNull('name')
            ->where('name', '!=', '')
            ->where('name', 'NOT LIKE', '%.url%')
            ->where('name', 'NOT LIKE', '%"%')
            ->whereNotIn('slug', ['amazon', 'flipkart', 'myntra', 'croma', 'reliance-digital'])
            ->withCount(['deals as active_deals_count' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->filter(fn($b) => !empty($b->name) && (($b->active_deals_count ?? 0) > 0))
            ->sortByDesc('active_deals_count')
            ->take(15)
            ->values();
    });
@endphp

@if(request()->is('/') && !request()->filled('search') && !request()->filled('q') && !request()->filled('max_price') && !request()->filled('category') && !request()->filled('brand'))
  <div class="w-full relative overflow-hidden bg-gradient-to-b from-slate-50 via-white to-slate-50/60 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 border-b border-slate-200/80 dark:border-slate-800">
     <!-- Subtle ambient glow -->
     <div class="absolute inset-0 bg-[radial-gradient(#ef44440a_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none opacity-60"></div>
     <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[720px] h-[220px] bg-gradient-to-r from-red-500/10 via-amber-500/10 to-rose-500/10 rounded-full blur-[80px] pointer-events-none"></div>

     <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-7 sm:py-9 relative z-10 space-y-4">
        
        <!-- Compact Headline & Promise -->
        <div class="max-w-3xl mx-auto text-center space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 border border-red-200/70 dark:border-red-800/60 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                <span>India's Verified Deal & Price Intelligence</span>
            </div>
            
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                Find deals that are <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 via-rose-600 to-amber-500">genuinely good.</span>
            </h1>
            
            <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm max-w-xl mx-auto font-medium">
                Real 90-day price history, verified store benchmarks, and zero fake discounts.
            </p>
        </div>

        <!-- Hero Search Bar -->
        <div class="max-w-2xl mx-auto">
            <form action="{{ url('/') }}" method="GET" class="relative group">
                <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-700 shadow-md shadow-slate-200/50 dark:shadow-none hover:border-slate-400 focus-within:border-red-500 focus-within:ring-4 focus-within:ring-red-500/15 transition-all">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}"
                           placeholder="Search smartphones, laptops, electronics, brands..." 
                           class="w-full pl-12 pr-28 py-3.5 bg-transparent text-slate-900 dark:text-white placeholder-slate-400 text-sm font-medium outline-none" />
                    <button type="submit" 
                            class="absolute right-2 px-5 py-2.5 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold text-xs rounded-xl transition shadow-md shadow-red-500/20 flex items-center gap-1.5">
                        <span>Search</span>
                        <svg class="w-3.5 h-3.5 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Clean Tags: AI Prompts & Categories -->
        <div class="max-w-3xl mx-auto flex flex-wrap items-center justify-center gap-2 pt-1 text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-bold text-[11px] uppercase tracking-wider mr-1">Trending:</span>
            
            <a href="{{ url('/assistant?q=Best+smartphone+under+30000') }}" 
               class="px-3 py-1 rounded-full bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 border border-slate-200 dark:border-slate-700 shadow-sm hover:border-red-300 hover:bg-red-50/50 transition-all font-semibold flex items-center gap-1">
                <span>🤖</span> Phone &lt; &#8377;30k
            </a>
            
            <a href="{{ url('/categories/mobiles') }}" 
               class="px-3 py-1 rounded-full bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 border border-slate-200 dark:border-slate-700 shadow-sm hover:border-red-300 hover:bg-red-50/50 transition-all font-semibold flex items-center gap-1">
                <span>📱</span> Mobiles
            </a>

            <a href="{{ url('/categories/electronics') }}" 
               class="px-3 py-1 rounded-full bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 border border-slate-200 dark:border-slate-700 shadow-sm hover:border-red-300 hover:bg-red-50/50 transition-all font-semibold flex items-center gap-1">
                <span>💻</span> Electronics
            </a>

            <a href="{{ url('/categories/home-kitchen') }}" 
               class="px-3 py-1 rounded-full bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 border border-slate-200 dark:border-slate-700 shadow-sm hover:border-red-300 hover:bg-red-50/50 transition-all font-semibold flex items-center gap-1">
                <span>🏡</span> Home & Kitchen
            </a>

            <a href="{{ url('/categories/sports-fitness') }}" 
               class="px-3 py-1 rounded-full bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 border border-slate-200 dark:border-slate-700 shadow-sm hover:border-red-300 hover:bg-red-50/50 transition-all font-semibold flex items-center gap-1">
                <span>🏋️</span> Fitness
            </a>

            <a href="{{ url('/deals/all') }}" 
               class="px-3 py-1 rounded-full bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 hover:bg-red-600 hover:text-white border border-red-200/80 dark:border-red-800/60 transition-all font-bold">
                All Deals &rarr;
            </a>
        </div>

        <!-- 3 Pillars of Trust -->
        <div class="flex items-center justify-center gap-6 pt-1 text-[11px] font-semibold text-slate-500 dark:text-slate-400">
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                90-Day Price Audit
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Zero Inflated MRPs
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Verified Retailer Prices
            </span>
        </div>

     </div>
  </div>
@else
  <!-- 100% Full-Width Edge-to-Edge Brand / Category Command Banner -->
  @if(isset($brand) || isset($category) || isset($merchant) || (isset($pageTitle) && !request()->is('/')) || request()->filled('q') || request()->filled('search') || request()->filled('max_price'))
      @php
          $entityName = isset($brand) ? $brand->name : (isset($category) ? $category->name : (isset($merchant) ? $merchant->name : ($pageTitle ?? 'Deals')));
          $entityType = isset($brand) ? 'Brand' : (isset($category) ? 'Category' : (isset($merchant) ? 'Store' : 'Deals'));
          $avgDiscount = isset($brand) ? ($brand->average_discount ?: 34.9) : (isset($category) ? ($category->average_discount ?: 41.2) : 38.5);
          $popularity = isset($brand) ? ($brand->trending_score ?: 85) : (isset($category) ? ($category->trending_score ?: 92) : 88);
          $totalDeals = $deals->total();
      @endphp
      
      <div class="w-full relative overflow-hidden bg-slate-950 text-white shadow-2xl border-b border-slate-800/80">
          <div class="absolute inset-0 bg-gradient-to-br from-red-950/40 via-slate-950 to-slate-900 pointer-events-none"></div>
          <div class="absolute -top-32 left-1/3 w-[600px] h-[600px] bg-red-600/10 rounded-full blur-[120px] pointer-events-none"></div>

          <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 relative z-10 space-y-6">
              <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                  <div class="space-y-2">
                      <div class="flex flex-wrap items-center gap-2 text-xs">
                          <span class="px-3.5 py-1 rounded-full font-black uppercase tracking-wider bg-red-600 text-white shadow-md">
                              🎯 {{ $entityType }} Intelligence Hub
                          </span>
                          <span class="px-3 py-1 rounded-full font-bold bg-slate-900 border border-slate-800 text-emerald-400 flex items-center gap-1.5">
                              <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                              Catalog Synced
                          </span>
                          <span class="px-3 py-1 rounded-full font-bold bg-slate-900 border border-slate-800 text-amber-300">
                              🛡️ Verified Price History
                          </span>
                      </div>

                      <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                          {{ $entityName }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-amber-400">Deals & Offers</span>
                      </h1>
                      <p class="text-slate-300 text-sm sm:text-base max-w-2xl font-medium">
                          @if($totalDeals > 0)
                              Tracking {{ number_format($totalDeals) }}+ verified discounts on {{ $entityName }}. Periodically synchronized with retailer feeds.
                          @else
                              Discover verified discounts and price drops on {{ $entityName }}. Periodically synchronized with retailer feeds.
                          @endif
                      </p>
                  </div>

                  <div class="grid grid-cols-3 gap-3 shrink-0">
                      <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-3.5 text-center shadow-lg">
                          <div class="text-2xl sm:text-3xl font-black text-red-500 tracking-tight">{{ number_format($avgDiscount, 1) }}%</div>
                          <div class="text-[10px] uppercase font-black tracking-wider text-slate-400 mt-1">Avg Savings</div>
                      </div>
                      <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-3.5 text-center shadow-lg">
                          <div class="text-2xl sm:text-3xl font-black text-amber-400 tracking-tight">{{ $popularity }}</div>
                          <div class="text-[10px] uppercase font-black tracking-wider text-slate-400 mt-1">Popularity</div>
                      </div>
                      <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-3.5 text-center shadow-lg">
                          <div class="text-2xl sm:text-3xl font-black text-emerald-400 tracking-tight">{{ $totalDeals }}</div>
                          <div class="text-[10px] uppercase font-black tracking-wider text-slate-400 mt-1">Active Offers</div>
                      </div>
                  </div>
              </div>

              <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-800/80">
                  <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                      <span class="text-slate-400 mr-1 text-[11px] uppercase tracking-wider font-extrabold">Quick Sort:</span>
                      <a href="?sort=featured" class="px-3.5 py-2 rounded-xl {{ (request('sort') == 'featured' || !request('sort')) ? 'bg-red-600 text-white font-black shadow-md' : 'bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800' }} transition">
                          🔥 Top Rated
                      </a>
                      <a href="?sort=discount" class="px-3.5 py-2 rounded-xl {{ request('sort') == 'discount' ? 'bg-red-600 text-white font-black shadow-md' : 'bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800' }} transition">
                          📉 Biggest Discount
                      </a>
                      <a href="?sort=price_low" class="px-3.5 py-2 rounded-xl {{ request('sort') == 'price_low' ? 'bg-red-600 text-white font-black shadow-md' : 'bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800' }} transition">
                          💵 Lowest Price
                      </a>
                      <a href="?sort=newest" class="px-3.5 py-2 rounded-xl {{ request('sort') == 'newest' ? 'bg-red-600 text-white font-black shadow-md' : 'bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800' }} transition">
                          ⚡ Just Added
                      </a>
                  </div>

                  <div class="flex items-center gap-3">
                      @if(isset($brand) || isset($category))
                          @php
                              $watchableType = isset($brand) ? 'Brand' : 'Category';
                              $watchableId = isset($brand) ? $brand->id : $category->id;
                              $isWatching = Auth::check() ? \App\Models\Watchlist::where('user_id', Auth::id())->where('watchable_type', 'App\\Models\\' . $watchableType)->where('watchable_id', $watchableId)->exists() : false;
                          @endphp
                          <form action="{{ route('watchlist.toggle') }}" method="POST" class="inline">
                              @csrf
                              <input type="hidden" name="type" value="{{ $watchableType }}">
                              <input type="hidden" name="id" value="{{ $watchableId }}">
                              <button type="submit" class="px-4 py-2 rounded-xl {{ $isWatching ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/50' : 'bg-slate-800 hover:bg-slate-700 text-white border border-slate-700' }} font-black text-xs transition-all shadow-lg flex items-center gap-1.5" @if(!Auth::check()) onclick="event.preventDefault(); window.location='{{ route('shopper.login') }}'" @endif>
                                  <span>{{ $isWatching ? '✓ Following' : '+ Watch ' . $entityName }}</span>
                              </button>
                          </form>
                      @endif

                      <button x-data @click="$dispatch('open-alert-modal')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-red-600 hover:from-amber-400 hover:to-red-500 text-white font-black text-xs transition-all shadow-lg flex items-center gap-1.5">
                          <span>🔔 Track {{ $entityName }} Price Drops</span>
                      </button>
                  </div>
              </div>
          </div>
      </div>
  @endif
@endif
@endsection

@section('content')
<section class="space-y-6" x-data="{ mobileFiltersOpen: false }">

  <x-ad-banner slot="home-top" />

  <div class="space-y-4">

    @if(isset($breadcrumbs))
        <nav class="flex text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 bg-white dark:bg-slate-900 px-4 py-2 rounded-xl shadow-sm border border-gray-100 dark:border-slate-800">
                @foreach($breadcrumbs as $index => $crumb)
                    <li class="inline-flex items-center">
                        @if(!$loop->first)
                            <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        @if($crumb['url'])
                            <a href="{{ $crumb['url'] }}" class="text-gray-500 dark:text-slate-400 hover:text-red-600 transition font-medium">{{ $crumb['title'] }}</a>
                        @else
                            <span class="text-gray-900 dark:text-white font-bold">{{ $crumb['title'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    <!-- Section Heading & Secondary Tools -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                {{ $pageTitle ?? "Today's Verified Deals" }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">Found {{ number_format($deals->total()) }} matching verified deals</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="/assistant" class="btn-primary bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white shadow-lg flex items-center px-3.5 py-2 text-xs sm:text-sm font-bold rounded-xl transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                AI Assistant
            </a>
            <button x-data @click="$dispatch('open-alert-modal')" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-bold text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 flex items-center rounded-xl dark:bg-red-900/30 dark:text-red-400 dark:border-red-800 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                Price Alert
            </button>
        </div>
    </div>

    <!-- Priority 5: Concise Filter Bar Directly Above Deals -->
    <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-3 sm:p-4 shadow-sm space-y-3">
        
        <!-- Desktop Filter Form -->
        <form action="{{ url()->current() }}" method="GET" id="filter-form" class="hidden md:grid md:grid-cols-6 gap-2.5">
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif

            <!-- 1. Category -->
            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1">Category</label>
                <select name="category" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl px-2.5 py-2 text-xs font-semibold text-gray-800 dark:text-slate-200 focus:ring-2 focus:ring-red-500 outline-none">
                    <option value="" {{ (!request()->filled('category') && !request()->filled('category_slug')) ? 'selected' : '' }}>All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ ((request()->filled('category') && request('category') == $cat->slug) || (request()->filled('category_slug') && request('category_slug') == $cat->slug)) ? 'selected' : '' }}>
                            {{ $cat->name }}@if(($cat->active_deals_count ?? 0) > 0) ({{ $cat->active_deals_count }})@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Budget -->
            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1">Budget</label>
                <select name="max_price" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl px-2.5 py-2 text-xs font-semibold text-gray-800 dark:text-slate-200 focus:ring-2 focus:ring-red-500 outline-none">
                    <option value="">Any Budget</option>
                    <option value="500" {{ request('max_price') == '500' ? 'selected' : '' }}>Under ₹500</option>
                    <option value="1000" {{ request('max_price') == '1000' ? 'selected' : '' }}>Under ₹1,000</option>
                    <option value="5000" {{ request('max_price') == '5000' ? 'selected' : '' }}>Under ₹5,000</option>
                    <option value="20000" {{ request('max_price') == '20000' ? 'selected' : '' }}>Under ₹20,000</option>
                    <option value="50000" {{ request('max_price') == '50000' ? 'selected' : '' }}>Under ₹50,000</option>
                </select>
            </div>

            <!-- 3. Store -->
            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1">Store</label>
                <select name="merchant" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl px-2.5 py-2 text-xs font-semibold text-gray-800 dark:text-slate-200 focus:ring-2 focus:ring-red-500 outline-none">
                    <option value="" {{ (!request()->filled('merchant') && !request()->filled('merchant_slug')) ? 'selected' : '' }}>All Stores</option>
                    @foreach($merchants as $m)
                        <option value="{{ $m->name }}" {{ ((request()->filled('merchant') && request('merchant') == $m->name) || (request()->filled('merchant_slug') && !empty($m->slug) && request('merchant_slug') == $m->slug)) ? 'selected' : '' }}>
                            {{ $m->name }} ({{ $m->active_deals_count ?? 0 }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Brand -->
            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1">Brand</label>
                <select name="brand" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl px-2.5 py-2 text-xs font-semibold text-gray-800 dark:text-slate-200 focus:ring-2 focus:ring-red-500 outline-none">
                    <option value="" {{ (!request()->filled('brand') && !request()->filled('brand_slug')) ? 'selected' : '' }}>All Brands</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->slug }}" {{ ((request()->filled('brand') && request('brand') == $b->slug) || (request()->filled('brand_slug') && request('brand_slug') == $b->slug)) ? 'selected' : '' }}>
                            {{ $b->name }} ({{ $b->active_deals_count ?? 0 }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 5. Discount Band -->
            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1">Discount</label>
                <select name="discount_range" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl px-2.5 py-2 text-xs font-semibold text-gray-800 dark:text-slate-200 focus:ring-2 focus:ring-red-500 outline-none">
                    <option value="">Any Discount</option>
                    <option value="30-49" {{ request('discount_range') == '30-49' ? 'selected' : '' }}>30% – 49% OFF</option>
                    <option value="50-69" {{ request('discount_range') == '50-69' ? 'selected' : '' }}>50% – 69% OFF</option>
                    <option value="70-89" {{ request('discount_range') == '70-89' ? 'selected' : '' }}>70% – 89% OFF</option>
                    <option value="90+" {{ request('discount_range') == '90+' ? 'selected' : '' }}>90%+ OFF</option>
                </select>
            </div>

            <!-- 6. Sort By -->
            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1">Sort By</label>
                <select name="sort" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl px-2.5 py-2 text-xs font-semibold text-gray-800 dark:text-slate-200 focus:ring-2 focus:ring-red-500 outline-none">
                    <option value="featured" {{ (request('sort') == 'featured' || !request('sort')) ? 'selected' : '' }}>⭐ Best Value</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>💵 Lowest Price</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>⚡ Newest</option>
                    <option value="discount" {{ request('sort') == 'discount' ? 'selected' : '' }}>📉 Biggest Discount</option>
                </select>
            </div>
        </form>

        <!-- Mobile Filter Bar Header: Compact Trigger Button + Quick Sort -->
        <div class="md:hidden flex items-center justify-between gap-2">
            @php
                $activeCount = 0;
                if(request()->filled('q')) $activeCount++;
                if(request()->filled('category')) $activeCount++;
                if(request()->filled('max_price')) $activeCount++;
                if(request()->filled('merchant')) $activeCount++;
                if(request()->filled('brand')) $activeCount++;
                if(request()->filled('discount_range')) $activeCount++;
                if(request()->filled('sort') && request('sort') !== 'featured') $activeCount++;
            @endphp
            <button type="button" 
                    @click="mobileFiltersOpen = true" 
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 px-3 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700 text-gray-900 dark:text-white rounded-xl text-xs font-bold transition">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <span>Filter Deals</span>
                @if($activeCount > 0)
                    <span class="px-2 py-0.5 bg-red-600 text-white rounded-full text-[10px] font-black">{{ $activeCount }}</span>
                @endif
            </button>
            <div class="flex-1">
                <select onchange="document.querySelector('#filter-form select[name=\'sort\']').value = this.value; document.getElementById('filter-form').dispatchEvent(new Event('submit'));" 
                        class="w-full bg-gray-100 dark:bg-slate-800 border border-transparent rounded-xl py-2.5 px-2.5 text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="featured" {{ (request('sort') == 'featured' || !request('sort')) ? 'selected' : '' }}>⭐ Best Value</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>💵 Lowest Price</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>⚡ Newest</option>
                    <option value="discount" {{ request('sort') == 'discount' ? 'selected' : '' }}>📉 Biggest Discount</option>
                </select>
            </div>
        </div>

        <!-- Removable Active Filter Chips -->
        @php
            $activeChips = [];
            if (request()->filled('q')) $activeChips[] = ['key' => 'q', 'label' => 'Search: "' . request('q') . '"'];
            if (request()->filled('category')) $activeChips[] = ['key' => 'category', 'label' => 'Category: ' . ucfirst(str_replace('-', ' ', request('category')))];
            if (request()->filled('max_price')) $activeChips[] = ['key' => 'max_price', 'label' => 'Under ₹' . number_format(request('max_price'))];
            if (request()->filled('merchant')) $activeChips[] = ['key' => 'merchant', 'label' => 'Store: ' . request('merchant')];
            if (request()->filled('brand')) $activeChips[] = ['key' => 'brand', 'label' => 'Brand: ' . ucfirst(request('brand'))];
            if (request()->filled('discount_range')) $activeChips[] = ['key' => 'discount_range', 'label' => request('discount_range') . '% Off'];
            if (request()->filled('sort') && request('sort') !== 'featured') $activeChips[] = ['key' => 'sort', 'label' => 'Sort: ' . ucfirst(str_replace('_', ' ', request('sort')))];
        @endphp
        @if(count($activeChips) > 0)
        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 dark:border-slate-800 text-xs">
            <span class="font-bold text-gray-500 dark:text-slate-400">Active filters:</span>
            @foreach($activeChips as $chip)
                @php
                    $removeUrl = url()->current() . '?' . http_build_query(request()->except($chip['key']));
                @endphp
                <a href="{{ $removeUrl }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900 hover:bg-red-100 transition shadow-sm">
                    <span>{{ $chip['label'] }}</span>
                    <span class="font-black text-xs">✕</span>
                </a>
            @endforeach
            <a href="{{ url()->current() }}" class="text-gray-500 dark:text-slate-400 hover:text-red-600 underline font-semibold ml-1">
                Clear all
            </a>
        </div>
        @endif

    </div>

    <!-- Mobile Filter Slide-over Drawer / Bottom Sheet -->
    <div x-cloak x-show="mobileFiltersOpen" class="fixed inset-0 z-50 md:hidden flex flex-col justify-end">
        <div @click="mobileFiltersOpen = false" class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity"></div>
        <div class="relative bg-white dark:bg-slate-900 rounded-t-3xl border-t border-gray-200 dark:border-slate-800 p-6 max-h-[85vh] overflow-y-auto space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-800 pb-3">
                <h3 class="font-black text-gray-900 dark:text-white text-base flex items-center gap-2">
                    <span>Filter Deals</span>
                    @if(count($activeChips) > 0)
                        <span class="text-xs font-semibold text-red-500">({{ count($activeChips) }} active)</span>
                    @endif
                </h3>
                <button @click="mobileFiltersOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white font-black text-lg p-1">✕</button>
            </div>

            <div class="space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 mb-1">Category</label>
                    <select onchange="document.querySelector('#filter-form select[name=\'category\']').value = this.value" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-800 dark:text-slate-200">
                        <option value="" {{ (!request()->filled('category') && !request()->filled('category_slug')) ? 'selected' : '' }}>All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ ((request()->filled('category') && request('category') == $cat->slug) || (request()->filled('category_slug') && request('category_slug') == $cat->slug)) ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 mb-1">Budget</label>
                    <select onchange="document.querySelector('#filter-form select[name=\'max_price\']').value = this.value" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-800 dark:text-slate-200">
                        <option value="">Any Budget</option>
                        <option value="500" {{ request('max_price') == '500' ? 'selected' : '' }}>Under ₹500</option>
                        <option value="1000" {{ request('max_price') == '1000' ? 'selected' : '' }}>Under ₹1,000</option>
                        <option value="5000" {{ request('max_price') == '5000' ? 'selected' : '' }}>Under ₹5,000</option>
                        <option value="20000" {{ request('max_price') == '20000' ? 'selected' : '' }}>Under ₹20,000</option>
                        <option value="50000" {{ request('max_price') == '50000' ? 'selected' : '' }}>Under ₹50,000</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 mb-1">Store</label>
                    <select onchange="document.querySelector('#filter-form select[name=\'merchant\']').value = this.value" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-800 dark:text-slate-200">
                        <option value="" {{ (!request()->filled('merchant') && !request()->filled('merchant_slug')) ? 'selected' : '' }}>All Stores</option>
                        @foreach($merchants as $m)
                            <option value="{{ $m->name }}" {{ ((request()->filled('merchant') && request('merchant') == $m->name) || (request()->filled('merchant_slug') && !empty($m->slug) && request('merchant_slug') == $m->slug)) ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 mb-1">Brand</label>
                    <select onchange="document.querySelector('#filter-form select[name=\'brand\']').value = this.value" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-800 dark:text-slate-200">
                        <option value="" {{ (!request()->filled('brand') && !request()->filled('brand_slug')) ? 'selected' : '' }}>All Brands</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->slug }}" {{ ((request()->filled('brand') && request('brand') == $b->slug) || (request()->filled('brand_slug') && request('brand_slug') == $b->slug)) ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 mb-1">Discount</label>
                    <select onchange="document.querySelector('#filter-form select[name=\'discount_range\']').value = this.value" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-800 dark:text-slate-200">
                        <option value="">Any Discount</option>
                        <option value="30-49" {{ request('discount_range') == '30-49' ? 'selected' : '' }}>30% – 49% OFF</option>
                        <option value="50-69" {{ request('discount_range') == '50-69' ? 'selected' : '' }}>50% – 69% OFF</option>
                        <option value="70-89" {{ request('discount_range') == '70-89' ? 'selected' : '' }}>70% – 89% OFF</option>
                        <option value="90+" {{ request('discount_range') == '90+' ? 'selected' : '' }}>90%+ OFF</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-3">
                <button type="button" 
                        @click="document.getElementById('filter-form').dispatchEvent(new Event('submit')); mobileFiltersOpen = false;" 
                        class="flex-1 py-3 bg-red-600 hover:bg-red-500 text-white font-black text-sm rounded-xl transition shadow-md text-center">
                    Apply Filters
                </button>
                <a href="{{ url()->current() }}" 
                   class="px-5 py-3 bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 font-bold text-sm rounded-xl text-center">
                    Reset
                </a>
            </div>
        </div>
    </div>

    <!-- Deals Grid (Visible Immediately on First Screen) -->
    <div class="grid gap-3 grid-cols-2 md:grid-cols-3 xl:grid-cols-5" id="deals-grid">
      @include('partials.deals_grid')
    </div>
    
    <div class="mt-12 flex justify-center hidden" id="loading-spinner">
      <svg class="animate-spin h-8 w-8 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
    </div>

    <div class="mt-8 flex justify-center h-10" id="pagination-container">
      @if($deals->hasMorePages())
        <div id="load-more-trigger" data-url="{{ $deals->nextPageUrl() }}"></div>
      @endif
    </div>

    <!-- Priority 1: Popular Stores & Brands (Moved Below First Deal Row) -->
    <div class="mt-14 pt-8 border-t border-gray-100 dark:border-slate-800 space-y-8">
        
        <!-- Top Verified Stores -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span>🏪 Verified Partner Stores</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Track and compare deals across trusted retail platforms</p>
                </div>
                <a href="{{ url('/merchants') }}" class="text-xs font-bold text-red-600 dark:text-red-400 hover:underline">
                    View all stores &rarr;
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                @foreach($merchants->take(6) as $merchantItem)
                <a href="{{ url('/merchants/' . $merchantItem->slug) }}" 
                   class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 hover:border-red-500/50 rounded-2xl p-3.5 flex flex-col items-center justify-center text-center group hover:shadow-md transition">
                    <span class="text-sm font-black text-gray-800 dark:text-white group-hover:text-red-500 transition line-clamp-1">{{ $merchantItem->name }}</span>
                    <span class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 font-semibold">{{ $merchantItem->active_deals_count ?? 0 }} active deals</span>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Popular Brands -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span>🏷️ Popular Brands</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Browse live discounts by manufacturer</p>
                </div>
                <a href="{{ url('/brands') }}" class="text-xs font-bold text-red-600 dark:text-red-400 hover:underline">
                    View all brands &rarr;
                </a>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($brands->take(12) as $brandItem)
                <a href="{{ url('/brands/' . $brandItem->slug) }}" 
                   class="px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:border-red-500/50 rounded-xl text-xs font-bold text-gray-700 dark:text-slate-300 hover:text-red-500 dark:hover:text-red-400 transition shadow-sm flex items-center gap-1.5">
                    <span>{{ $brandItem->name }}</span>
                    @if(isset($brandItem->active_deals_count) && $brandItem->active_deals_count > 0)
                        <span class="text-[10px] text-gray-400 dark:text-slate-500 font-mono">({{ $brandItem->active_deals_count }})</span>
                    @endif
                </a>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Priority 1: Expert Buying Guides (Moved Below First Deal Row) -->
    <div class="mt-8 bg-indigo-50 dark:bg-indigo-900/10 rounded-3xl p-6 sm:p-8 border border-indigo-100 dark:border-indigo-900/30 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="bg-indigo-600 text-white p-3 rounded-2xl shadow-md shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white">Expert Buying Guides</h2>
                <p class="text-gray-600 dark:text-indigo-200 text-sm mt-1">Data-driven analysis on when to buy, price trends, and genuine savings.</p>
            </div>
        </div>
        <a href="{{ route('articles.index') }}" class="shrink-0 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition-all shadow-md">
            Read Our Guides &rarr;
        </a>
    </div>
    
    <!-- SEO Search Hub Editorial Content -->
    @if(isset($hubData) && $hubData)
        <div class="mt-12 mb-8 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl p-6 md:p-8 shadow-sm">
            <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-4">{{ $hubData['title'] }}</h1>
            <p class="text-gray-600 dark:text-slate-300 text-lg leading-relaxed mb-8">
                {{ $hubData['intro'] }}
            </p>
            
            @if(isset($hubData['faqs']) && count($hubData['faqs']) > 0)
            <div class="mt-8 border-t border-gray-100 dark:border-slate-800 pt-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Frequently Asked Questions</h2>
                <div class="space-y-4">
                    @foreach($hubData['faqs'] as $faq)
                    <div class="bg-gray-50 dark:bg-slate-800/50 rounded-xl p-4">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-2">{{ $faq['question'] }}</h3>
                        <p class="text-gray-600 dark:text-slate-300 text-sm">{{ $faq['answer'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <script type="application/ld+json">
            {
              "@@context": "https://schema.org",
              "@@type": "FAQPage",
              "mainEntity": [
                @foreach($hubData['faqs'] as $faq)
                {
                  "@@type": "Question",
                  "name": "{{ addslashes($faq['question']) }}",
                  "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "{{ addslashes($faq['answer']) }}"
                  }
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
              ]
            }
            </script>
            @endif
        </div>
    @endif

    <x-ad-banner slot="home-bottom" />
    
    <!-- Newsletter Banner -->
    <div class="mt-16 bg-gradient-to-r from-gray-900 to-black rounded-3xl p-8 sm:p-12 shadow-2xl relative overflow-hidden border border-gray-800">
        <div class="absolute top-0 right-0 w-64 h-64 bg-red-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
        <div class="relative z-10 grid md:grid-cols-2 gap-8 items-center">
            <div>
                <h3 class="text-3xl font-black text-white tracking-tight mb-3">Get weekly deal alerts</h3>
                <p class="text-gray-400 text-sm sm:text-base">Subscribe for a concise digest of fresh price drops, verified coupons, and curated picks.</p>
            </div>
            <div x-data="{ email: '', loading: false, success: false, error: '' }" class="w-full max-w-md ml-auto">
                <form @submit.prevent="
                    if(!email) return;
                    loading = true; error = ''; success = false;
                    fetch('/api/subscribe', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ email: email })
                    })
                    .then(res => res.json())
                    .then(data => {
                        loading = false;
                        if(data.error || data.errors) {
                            error = data.message || 'Error subscribing.';
                        } else {
                            success = true;
                            email = '';
                        }
                    })
                    .catch(() => { loading = false; error = 'Network error.'; });
                " class="relative">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="email" x-model="email" required placeholder="Enter your email address" class="flex-1 bg-white/10 border border-gray-700 text-white placeholder-gray-500 px-5 py-3.5 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all">
                        <button type="submit" :disabled="loading" class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-3.5 rounded-xl transition-colors disabled:opacity-50 flex items-center justify-center min-w-[120px]">
                            <span x-show="!loading">Subscribe</span>
                            <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </div>
                    <p x-show="success" x-transition class="text-emerald-400 text-sm mt-3 font-medium flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Success! You're on the list.</p>
                    <p x-show="error" x-transition class="text-red-400 text-sm mt-3 font-medium" x-text="error"></p>
                </form>
            </div>
        </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const dealsGrid = document.getElementById('deals-grid');
    const spinner = document.getElementById('loading-spinner');
    const paginationContainer = document.getElementById('pagination-container');
    let isFetching = false;
    let observer = null;
    
    function fetchDeals(url, append = false) {
        if (isFetching) return;
        isFetching = true;
        
        if (!append) {
            dealsGrid.style.opacity = '0.5';
        }
        spinner.classList.remove('hidden');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (append) {
                dealsGrid.insertAdjacentHTML('beforeend', data.html);
            } else {
                dealsGrid.innerHTML = data.html;
                dealsGrid.style.opacity = '1';
                window.history.pushState({}, '', url);
            }

            if (data.has_more && data.next_page) {
                paginationContainer.innerHTML = `<div id="load-more-trigger" data-url="${data.next_page}"></div>`;
                bindAutoLoad();
            } else {
                paginationContainer.innerHTML = '';
            }
        })
        .finally(() => {
            spinner.classList.add('hidden');
            isFetching = false;
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(filterForm);
            const params = new URLSearchParams();
            
            for (const [key, val] of formData.entries()) {
                if (val && val !== 'all' && val !== '') {
                    params.set(key, val);
                }
            }
            
            const basePath = window.location.pathname || '/';
            const queryString = params.toString();
            const url = basePath + (queryString ? '?' + queryString : '');
            fetchDeals(url, false);
        });

        const selects = filterForm.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', () => {
                filterForm.dispatchEvent(new Event('submit'));
            });
        });
    }

    function bindAutoLoad() {
        const trigger = document.getElementById('load-more-trigger');
        if (!trigger) return;
        
        if (observer) {
            observer.disconnect();
        }
        
        observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                const url = trigger.getAttribute('data-url');
                if (url && !isFetching) {
                    fetchDeals(url, true);
                }
            }
        }, { rootMargin: '300px' });
        
        observer.observe(trigger);
    }
    
    bindAutoLoad();
});
</script>
@endsection

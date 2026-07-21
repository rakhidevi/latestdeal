@extends('layouts.app')

@section('meta')
    <title>{{ $seoMeta['title'] ?? 'Find the Best Global Deals, Offers, & Coupons | LatestDeal' }}</title>
    <meta name="description" content="{{ $seoMeta['description'] ?? 'Discover top discounts, live offers, and verified coupons from global marketplaces like Amazon. Our AI scores deals so you always save money.' }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] ?? url()->current() }}">
    
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
  </style>
  <div x-data="{ showSearch: false }" class="w-full relative overflow-hidden bg-gradient-to-r from-red-600 via-red-500 to-red-600 text-white shadow-md group">
    <!-- Animated background accents -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 opacity-50 group-hover:opacity-80 transition-opacity duration-1000 pointer-events-none"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-yellow-400/10 rounded-full blur-3xl translate-y-1/2 opacity-50 group-hover:opacity-80 transition-opacity duration-1000 pointer-events-none"></div>

    <!-- Search Toggle Button (Ribbon) -->
    <button x-show="!showSearch" @click="showSearch = true" x-transition.opacity.duration.300ms class="absolute top-0 left-1/2 -translate-x-1/2 z-30 bg-white/20 hover:bg-white/30 backdrop-blur-md border-x border-b border-white/30 text-white font-bold py-2 px-8 shadow-[0_10px_20px_rgba(0,0,0,0.1)] flex items-center gap-2 transition-all hover:pt-3 focus:outline-none focus:ring-4 focus:ring-white/20 rounded-b-2xl cursor-pointer">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        Search Deals
    </button>

    <!-- Expanding Search Bar -->
    <div x-show="showSearch" x-collapse.duration.400ms x-cloak class="w-full relative z-20 bg-black/10 backdrop-blur-sm border-b border-white/10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 pt-8 pb-6">
            <form action="/" method="GET" class="space-y-4 w-full relative" id="filter-form">
                <!-- Search Bar -->
                <div class="flex w-full shadow-lg rounded-xl overflow-hidden bg-white transition-all hover:shadow-xl relative z-10">
                    <div class="pl-4 flex items-center text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input name="q" value="{{ request('q') }}" autocomplete="off" placeholder="Search products, brands, or categories..." class="border-0 bg-transparent text-gray-900 flex-1 py-3.5 px-3 text-base focus:ring-0 placeholder-gray-400 outline-none" />
                    <button class="bg-gray-900 px-8 py-3.5 text-sm font-bold tracking-wide text-white transition hover:bg-black flex-shrink-0">Search</button>
                </div>
                
                <!-- Filters Row -->
                <div class="flex flex-col sm:flex-row gap-3 w-full">
                    <div class="flex items-center gap-2 w-full sm:w-auto bg-white/10 rounded-xl p-1 backdrop-blur-sm border border-white/20 hover:bg-white/20 transition-colors">
                        <input type="number" min="0" name="min_price" value="{{ request('min_price') }}" placeholder="Min Deal Price ₹" class="rounded-lg border-0 bg-white px-3 py-2 text-sm text-gray-900 focus:ring-2 focus:ring-red-300 w-full sm:w-32 placeholder-gray-500 shadow-sm transition-all" />
                        <span class="text-white/80 font-medium px-1">-</span>
                        <input type="number" min="0" name="max_price" value="{{ request('max_price') }}" placeholder="Max Deal Price ₹" class="rounded-lg border-0 bg-white px-3 py-2 text-sm text-gray-900 focus:ring-2 focus:ring-red-300 w-full sm:w-32 placeholder-gray-500 shadow-sm transition-all" />
                    </div>
                    
                    <div class="w-full sm:w-auto bg-white/10 rounded-xl p-1 backdrop-blur-sm border border-white/20">
                        <select name="min_discount" onchange="this.form.submit()" class="rounded-lg border-0 bg-white px-3 py-2 text-sm text-gray-900 focus:ring-2 focus:ring-red-300 w-full sm:w-44 shadow-sm transition-all cursor-pointer">
                            <option value="">Any Discount %</option>
                            <option value="10" {{ request('min_discount') == '10' ? 'selected' : '' }}>At least 10% Off</option>
                            <option value="25" {{ request('min_discount') == '25' ? 'selected' : '' }}>At least 25% Off</option>
                            <option value="50" {{ request('min_discount') == '50' ? 'selected' : '' }}>At least 50% Off</option>
                            <option value="75" {{ request('min_discount') == '75' ? 'selected' : '' }}>75%+ Off (Deep Cuts)</option>
                        </select>
                    </div>
                    
                    <div class="ml-auto flex items-center gap-2 mt-1 sm:mt-0">
                        @if(request()->hasAny(['q', 'min_price', 'max_price', 'min_discount', 'category']))
                            <a href="/" class="text-xs font-semibold text-white bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl transition-colors flex items-center justify-center gap-1.5 backdrop-blur-sm border border-white/20 h-[42px]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Clear
                            </a>
                        @endif
                        <button type="button" @click="showSearch = false" class="text-xs font-semibold text-red-100 bg-black/20 hover:bg-black/30 px-4 py-2 rounded-xl transition-colors flex items-center justify-center gap-1.5 backdrop-blur-sm border border-white/10 h-[42px]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
                            Close
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="mx-auto max-w-7xl grid gap-4 md:gap-6 p-4 pt-16 md:p-6 md:pt-16 lg:p-8 lg:pt-16 md:grid-cols-[1.2fr_0.8fr] items-center relative z-10 transition-all duration-300">
      <div class="min-w-0 flex flex-col justify-center">
        
        <h1 class="text-3xl lg:text-4xl font-bold leading-tight break-words tracking-tight text-white drop-shadow-sm min-h-[2.5rem] lg:min-h-[3rem]" 
            x-data="{
                words: ['global deals', 'tech discounts', 'fashion offers', 'hidden gems'],
                wordIndex: 0,
                charIndex: 0,
                isDeleting: false,
                text: '',
                type() {
                    let current = this.words[this.wordIndex];
                    if(this.isDeleting) {
                        this.text = current.substring(0, this.charIndex - 1);
                        this.charIndex--;
                    } else {
                        this.text = current.substring(0, this.charIndex + 1);
                        this.charIndex++;
                    }
                    let speed = this.isDeleting ? 40 : 80;
                    if(!this.isDeleting && this.charIndex === current.length) {
                        speed = 2500;
                        this.isDeleting = true;
                    } else if(this.isDeleting && this.charIndex === 0) {
                        this.isDeleting = false;
                        this.wordIndex = (this.wordIndex + 1) % this.words.length;
                        speed = 400;
                    }
                    setTimeout(() => this.type(), speed);
                }
            }" 
            x-init="setTimeout(() => type(), 500)">
          Find real <span x-text="text" class="text-yellow-300"></span><span class="animate-pulse font-light opacity-70">|</span> <br class="hidden lg:block">in seconds
        </h1>
        
        <p class="mt-2 lg:mt-4 text-sm lg:text-base text-red-50 hidden sm:block font-medium max-w-xl">
          Our AI engines continuously extract and verify deals from top Indian marketplaces 24/7.
        </p>
        
        <div class="mt-6 flex flex-wrap items-center gap-2 text-sm text-red-100">
            <span class="font-bold text-white/90 text-xs uppercase tracking-wider mr-2">Trending</span>
            @foreach($categories as $cat)
            <a href="/?category={{ $cat->slug }}" class="rounded-full bg-white/15 hover:bg-white/25 px-4 py-1.5 text-xs font-semibold whitespace-nowrap transition-colors border border-white/20 backdrop-blur-sm text-white">
              {{ $cat->name }}
            </a>
            @endforeach
            <a href="/categories" class="rounded-full bg-black/20 hover:bg-black/40 px-4 py-1.5 text-xs font-bold whitespace-nowrap transition-colors border border-white/30 backdrop-blur-sm text-white flex items-center gap-1">
              <span>View All</span>
              <span>→</span>
            </a>
        </div>
      </div>
      
      <div class="space-y-5 hidden md:flex flex-col justify-center pl-0 lg:pl-8">
        <!-- KPI Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="rounded-2xl bg-white/10 p-4 text-center backdrop-blur-md border border-white/20 shadow-lg">
                <div class="text-2xl lg:text-3xl font-black text-white">24/7</div>
                <div class="text-[10px] uppercase tracking-wider text-white/80 font-bold mt-1">Scanning</div>
            </div>
            <div class="rounded-2xl bg-white/10 p-4 text-center backdrop-blur-md border border-white/20 shadow-lg">
                <div class="text-2xl lg:text-3xl font-black text-white">100%</div>
                <div class="text-[10px] uppercase tracking-wider text-white/80 font-bold mt-1">Verified</div>
            </div>
            <div class="rounded-2xl bg-white/10 p-4 text-center backdrop-blur-md border border-white/20 shadow-lg">
                <div class="text-2xl lg:text-3xl font-black text-white">AI</div>
                <div class="text-[10px] uppercase tracking-wider text-white/80 font-bold mt-1">Scored</div>
            </div>
            <div class="rounded-2xl bg-white/10 p-4 text-center backdrop-blur-md border border-white/20 shadow-lg relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-green-400/20 to-emerald-600/20"></div>
                <div class="relative z-10 text-2xl lg:text-3xl font-black text-green-300">Free</div>
                <div class="relative z-10 text-[10px] uppercase tracking-wider text-green-100 font-bold mt-1">Access</div>
            </div>
        </div>

        <div class="rounded-2xl bg-white/10 p-6 backdrop-blur-md border border-white/20 shadow-xl relative overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10"></div>
          
          <p class="font-bold flex items-center gap-2.5 text-base text-white relative z-10">
            <span class="relative flex h-3.5 w-3.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-green-400 shadow-[0_0_10px_rgba(74,222,128,0.8)]"></span>
            </span>
            Live Engine Status
          </p>
          <ul class="mt-5 space-y-3.5 text-sm relative z-10">
            <li class="flex justify-between items-center border-b border-white/10 pb-2.5">
                <span class="text-white/90 font-medium">Crawling frequency</span> 
                <span class="font-bold text-gray-900 bg-white px-2.5 py-1 rounded-md text-xs shadow-sm">Every 5 mins</span>
            </li>
            <li class="flex justify-between items-center border-b border-white/10 pb-2.5">
                <span class="text-white/90 font-medium">Verification engine</span> 
                <span class="font-bold text-green-900 bg-green-400 px-2.5 py-1 rounded-md text-xs shadow-[0_0_10px_rgba(74,222,128,0.4)]">Online</span>
            </li>
            <li class="flex justify-between items-center">
                <span class="text-white/90 font-medium">Auto-publish</span> 
                <span class="font-bold text-gray-900 bg-white px-2.5 py-1 rounded-md text-xs shadow-sm">Enabled</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('content')
<section class="space-y-6">

  <x-ad-banner slot="home-top" />

  <div class="space-y-4">
    @if(isset($breadcrumbs))
        <nav class="flex text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-100">
                @foreach($breadcrumbs as $index => $crumb)
                    <li class="inline-flex items-center">
                        @if(!$loop->first)
                            <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        @if($crumb['url'])
                            <a href="{{ $crumb['url'] }}" class="text-gray-500 hover:text-red-600 transition font-medium">{{ $crumb['title'] }}</a>
                        @else
                            <span class="text-gray-900 font-bold">{{ $crumb['title'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3">
        <div>
            <h2 class="section-title">{{ $pageTitle ?? 'Featured Deals' }}</h2>
            <p class="text-sm text-gray-500 mt-1">Found {{ $deals->total() }} matching deals</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="/assistant" class="btn-primary bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white shadow-lg flex items-center px-3 py-1.5 text-sm sm:px-4 sm:py-2">
                <i data-lucide="sparkles" class="w-4 h-4 mr-1.5"></i> AI Assistant
            </a>
            <button x-data @click="$dispatch('open-alert-modal')" class="btn-secondary px-3 py-1.5 text-sm sm:px-4 sm:py-2 text-red-600 border-red-200 bg-red-50 hover:bg-red-100 flex items-center dark:bg-red-900/30 dark:text-red-400 dark:border-red-800">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                Price Alert
            </button>
            <a href="/?category=all" class="btn-secondary px-3 py-1.5 text-sm sm:px-4 sm:py-2 hidden sm:inline-block">View all</a>
        </div>
    </div>

    <!-- Entity Intelligence Landing Page UI -->
    @if(isset($category))
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-8 items-start md:items-center">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $category->name }} Deals & Buying Guide</h1>
                    <p class="text-gray-500 dark:text-slate-400 mt-2">Discover the best discounts on {{ $category->name }}. Our AI has analyzed {{ $category->deal_count ?? $deals->total() }} deals to bring you the top offers today.</p>
                </div>
                <div class="grid grid-cols-2 gap-4 flex-shrink-0 w-full md:w-auto">
                    <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-xl border border-red-100 dark:border-red-800/30 text-center">
                        <div class="text-2xl font-black text-red-600 dark:text-red-400">{{ number_format($category->average_discount, 1) }}%</div>
                        <div class="text-xs uppercase tracking-wider text-red-800 dark:text-red-300 font-semibold mt-1">Avg Discount</div>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-xl border border-orange-100 dark:border-orange-800/30 text-center">
                        <div class="text-2xl font-black text-orange-600 dark:text-orange-400">{{ $category->trending_score }}</div>
                        <div class="text-xs uppercase tracking-wider text-orange-800 dark:text-orange-300 font-semibold mt-1">Trending Score</div>
                    </div>
                </div>
            </div>
            @if($category->topMerchant)
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-slate-800">
                <p class="text-sm font-medium text-gray-600 dark:text-slate-300">
                    <span class="inline-block w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                    Top merchant for this category right now: <strong>{{ $category->topMerchant->name }}</strong>
                </p>
            </div>
            @endif
        </div>
    @endif

    @if(isset($brand))
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-8 items-start md:items-center">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $brand->name }} Deals & Offers</h1>
                    <p class="text-gray-500 dark:text-slate-400 mt-2">Find the latest verified discounts on {{ $brand->name }} products. Handpicked and scored by our AI engine.</p>
                </div>
                <div class="grid grid-cols-2 gap-4 flex-shrink-0 w-full md:w-auto">
                    <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-xl border border-red-100 dark:border-red-800/30 text-center">
                        <div class="text-2xl font-black text-red-600 dark:text-red-400">{{ number_format($brand->average_discount, 1) }}%</div>
                        <div class="text-xs uppercase tracking-wider text-red-800 dark:text-red-300 font-semibold mt-1">Avg Discount</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800/30 text-center">
                        <div class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $brand->trending_score }}</div>
                        <div class="text-xs uppercase tracking-wider text-blue-800 dark:text-blue-300 font-semibold mt-1">Brand Popularity</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(isset($trendingDeals) && $trendingDeals->isNotEmpty())
        <div class="mb-10">
            <div class="flex items-center gap-2 mb-4">
                <div class="bg-red-100 text-red-600 p-2 rounded-full dark:bg-red-900/30 dark:text-red-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Trending Deals Right Now</h2>
            </div>
            <div class="grid gap-3 grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
                @foreach($trendingDeals as $deal)
                    <x-deal-card :deal="$deal" />
                @endforeach
            </div>
        </div>
    @endif

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
      
      <x-ad-banner slot="home-bottom" />
      
      <!-- Newsletter Banner -->
      <div class="mt-16 bg-gradient-to-r from-gray-900 to-black rounded-3xl p-8 sm:p-12 shadow-2xl relative overflow-hidden border border-gray-800">
          <div class="absolute top-0 right-0 w-64 h-64 bg-red-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
          <div class="relative z-10 grid md:grid-cols-2 gap-8 items-center">
              <div>
                  <h3 class="text-3xl font-black text-white tracking-tight mb-3">Never Miss a 90% Price Drop</h3>
                  <p class="text-gray-400 text-sm sm:text-base">Join 50,000+ smart shoppers. We'll email you once a week with the absolute best AI-verified deals.</p>
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
                
                // Update URL without reloading
                window.history.pushState({}, '', url);
            }

            // Update Pagination Trigger
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

    // Handle Form Submit (Filters)
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            
            // Preserve current path context (e.g. /deals/70-89-off, /categories/electronics)
            const basePath = window.location.pathname || '/';
            const queryString = params.toString();
            const url = basePath + (queryString ? '?' + queryString : '');
            fetchDeals(url, false);
        });

        // Also trigger on select change
        const selects = filterForm.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', () => {
                filterForm.dispatchEvent(new Event('submit'));
            });
        });
    }

    // Handle Auto Load (Infinite Scroll)
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
    
    // Initialize auto load on page load
    bindAutoLoad();
});
</script>
@endsection

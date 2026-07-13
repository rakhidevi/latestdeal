@extends('layouts.app')

@section('meta')
    <title>Find the Best Global Deals, Offers, & Coupons | LatestDeal</title>
    <meta name="description" content="Discover top discounts, live offers, and verified coupons from global marketplaces like Amazon. Our AI scores deals so you always save money.">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="Find the Best Global Deals, Offers, & Coupons | LatestDeal">
    <meta property="og:description" content="Discover top discounts, live offers, and verified coupons from global marketplaces like Amazon. Our AI scores deals so you always save money.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('/images/logo.png') }}">
    <meta name="twitter:card" content="summary_large_image">

    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "WebSite",
      "name": "LatestDeal",
      "url": "{{ url('/') }}",
      "potentialAction": {
        "@@type": "SearchAction",
        "target": "{{ url('/') }}?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>
@endsection

@section('hero')
  <style>
    [x-cloak] { display: none !important; }
  </style>
  <div x-data="{ showSearch: {{ request()->hasAny(['q', 'min_price', 'max_price', 'min_discount', 'category']) ? 'true' : 'false' }} }" class="w-full relative overflow-hidden bg-gradient-to-r from-red-600 via-red-500 to-rose-600 text-white shadow-md group">
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

    <div class="mx-auto max-w-7xl grid gap-4 md:gap-6 p-4 md:p-6 lg:p-8 md:grid-cols-[1.2fr_0.8fr] items-center relative z-10 transition-all duration-300">
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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3">
        <div>
            <h2 class="section-title">Featured Deals</h2>
            <p class="section-subtitle">Global opportunities selected by scoring engine.</p>
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
            
            // Handle select specifically if needed, but FormData should catch it
            const url = '/?' + params.toString();
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

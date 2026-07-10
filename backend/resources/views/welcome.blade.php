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

@endsection

@section('hero')
  <div class="w-full bg-gradient-to-br from-red-600 via-rose-500 to-red-500 text-white hidden md:block shadow-inner border-b border-red-700/50">
    <div class="mx-auto max-w-7xl grid gap-6 p-4 sm:p-6 md:grid-cols-2 md:p-8">
      <div class="min-w-0 flex flex-col justify-center">
        <h1 class="text-3xl sm:text-4xl font-extrabold leading-tight break-words tracking-tight">
          Find real global deals in seconds
        </h1>
        <p class="mt-2 text-sm sm:text-base text-red-50 hidden sm:block font-medium">
          Our AI engines score and verify discounts across global marketplaces 24/7.
        </p>
        
        <form action="/" method="GET" class="mt-6 space-y-3 w-full" id="filter-form">
          <div class="flex gap-2 w-full shadow-lg rounded-xl">
            <input
              name="q"
              value="{{ request('q') }}"
              placeholder="Search products, brands, or categories..."
              class="input-base border-0 text-gray-900 flex-1 min-w-0 py-3 px-4 text-base rounded-l-xl focus:ring-2 focus:ring-red-900"
            />
            <button class="rounded-r-xl bg-gray-900 px-6 py-3 text-sm font-bold tracking-wide text-white transition hover:bg-black flex-shrink-0">Search</button>
          </div>
          
          <div class="flex flex-col sm:flex-row flex-wrap gap-2 sm:items-center w-full mt-2">
            <div class="grid grid-cols-2 gap-2 w-full sm:w-auto sm:flex sm:items-center relative">
                <span class="absolute -top-5 left-1 text-[10px] font-bold uppercase tracking-wider text-red-100/80">Price Limits</span>
                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min ₹" class="rounded-lg border-0 bg-white/10 backdrop-blur px-3 py-2 text-sm text-white focus:bg-white focus:text-gray-900 focus:ring-2 focus:ring-gray-900 w-full sm:w-28 placeholder-white/60 transition-colors" />
                <span class="text-white/50 hidden sm:inline px-1">-</span>
                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max ₹" class="rounded-lg border-0 bg-white/10 backdrop-blur px-3 py-2 text-sm text-white focus:bg-white focus:text-gray-900 focus:ring-2 focus:ring-gray-900 w-full sm:w-28 placeholder-white/60 transition-colors" />
            </div>
            <div class="w-full sm:w-auto sm:ml-2 relative">
                <span class="absolute -top-5 left-1 text-[10px] font-bold uppercase tracking-wider text-red-100/80">Discount Cutoff</span>
                <select name="min_discount" onchange="this.form.submit()" class="rounded-lg border-0 bg-white/10 backdrop-blur px-3 py-2 text-sm text-white focus:bg-white focus:text-gray-900 focus:ring-2 focus:ring-gray-900 w-full sm:w-40 flex-shrink-0 transition-colors appearance-none cursor-pointer">
                    <option value="" class="text-gray-900">Any Discount %</option>
                    <option value="10" class="text-gray-900" {{ request('min_discount') == '10' ? 'selected' : '' }}>At least 10% Off</option>
                    <option value="25" class="text-gray-900" {{ request('min_discount') == '25' ? 'selected' : '' }}>At least 25% Off</option>
                    <option value="50" class="text-gray-900" {{ request('min_discount') == '50' ? 'selected' : '' }}>At least 50% Off</option>
                    <option value="75" class="text-gray-900" {{ request('min_discount') == '75' ? 'selected' : '' }}>At least 75% Off (Deep Cuts)</option>
                </select>
            </div>
            
            @if(request()->hasAny(['q', 'min_price', 'max_price', 'min_discount', 'category']))
                <a href="/" class="ml-auto text-xs font-semibold text-white bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg transition-colors flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Clear Filters
                </a>
            @endif
          </div>
          
          <p class="text-[11px] text-red-100/70 pt-1 leading-snug">
            * Filters apply globally. Use Price Limits to stick to a budget, and the Discount Cutoff to instantly filter out low-value deals.
          </p>
        </form>
        
        <div class="mt-5 flex flex-wrap gap-2 text-sm text-red-100">
            <span class="py-1 font-semibold text-white/90 text-xs uppercase tracking-wider mr-1">Trending:</span>
            @foreach($categories as $cat)
            <a href="/?category={{ $cat->slug }}" class="rounded-full bg-black/10 hover:bg-black/20 px-3 py-1 text-xs font-medium whitespace-nowrap transition-colors border border-black/5">
              {{ $cat->name }}
            </a>
            @endforeach
        </div>
      </div>
      
      <div class="space-y-4 hidden md:flex flex-col justify-center">
        <!-- KPI Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="rounded-xl bg-black/15 p-4 text-center backdrop-blur shadow-sm border border-white/5">
                <div class="text-2xl sm:text-3xl font-black">24/7</div>
                <div class="text-[10px] sm:text-xs uppercase tracking-wider text-red-100 font-bold mt-1">Scanning</div>
            </div>
            <div class="rounded-xl bg-black/15 p-4 text-center backdrop-blur shadow-sm border border-white/5">
                <div class="text-2xl sm:text-3xl font-black">100%</div>
                <div class="text-[10px] sm:text-xs uppercase tracking-wider text-red-100 font-bold mt-1">Verified</div>
            </div>
            <div class="rounded-xl bg-black/15 p-4 text-center backdrop-blur shadow-sm border border-white/5">
                <div class="text-2xl sm:text-3xl font-black">AI</div>
                <div class="text-[10px] sm:text-xs uppercase tracking-wider text-red-100 font-bold mt-1">Scored</div>
            </div>
            <div class="rounded-xl bg-black/15 p-4 text-center backdrop-blur shadow-sm border border-white/5">
                <div class="text-2xl sm:text-3xl font-black text-green-400">Free</div>
                <div class="text-[10px] sm:text-xs uppercase tracking-wider text-red-100 font-bold mt-1">Access</div>
            </div>
        </div>

        <div class="rounded-2xl bg-black/20 p-5 text-sm backdrop-blur border border-white/10 shadow-lg mt-2">
          <p class="font-bold flex items-center gap-2 text-base">
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
            </span>
            Live Engine Status
          </p>
          <ul class="mt-4 space-y-3 text-red-50 text-sm">
            <li class="flex justify-between border-b border-white/10 pb-2">
                <span class="text-white/80">Crawling frequency</span> 
                <span class="font-black text-white bg-black/20 px-2 py-0.5 rounded text-xs">Every 5 mins</span>
            </li>
            <li class="flex justify-between border-b border-white/10 pb-2">
                <span class="text-white/80">Verification engine</span> 
                <span class="font-black text-green-400 bg-green-900/30 px-2 py-0.5 rounded text-xs">Online</span>
            </li>
            <li class="flex justify-between">
                <span class="text-white/80">Auto-publish</span> 
                <span class="font-black text-white bg-black/20 px-2 py-0.5 rounded text-xs">Enabled</span>
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

      <div class="mt-8 flex justify-center" id="pagination-container">
        @if($deals->hasMorePages())
          <button id="load-more-btn" data-url="{{ $deals->nextPageUrl() }}" class="rounded-lg bg-white border border-gray-300 px-6 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:bg-slate-800 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">Load More</button>
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
    
    function fetchDeals(url, append = false) {
        if (!append) {
            dealsGrid.style.opacity = '0.5';
        }
        spinner.classList.remove('hidden');
        if (append && document.getElementById('load-more-btn')) {
            document.getElementById('load-more-btn').classList.add('hidden');
        }

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

            // Update Pagination Button
            if (data.has_more && data.next_page) {
                paginationContainer.innerHTML = `<button id="load-more-btn" data-url="${data.next_page}" class="rounded-lg bg-white border border-gray-300 px-6 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:bg-slate-800 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">Load More</button>`;
                bindLoadMore();
            } else {
                paginationContainer.innerHTML = '';
            }
        })
        .finally(() => {
            spinner.classList.add('hidden');
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

    // Handle Load More
    function bindLoadMore() {
        const btn = document.getElementById('load-more-btn');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('data-url');
                if (url) {
                    fetchDeals(url, true);
                }
            });
        }
    }
    bindLoadMore();
});
</script>
@endsection

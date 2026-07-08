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

@section('content')
<section class="space-y-6">
  <div class="panel overflow-hidden bg-gradient-to-br from-red-500 via-rose-500 to-red-400 p-0 text-white">
    <div class="grid gap-6 p-4 sm:p-6 md:grid-cols-2 md:p-8">
      <div class="min-w-0"> <!-- Added min-w-0 to prevent grid blowout -->
        <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight break-words">
          Find real global deals in seconds
        </h1>
        <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-red-50 hidden sm:block">
          AI-scored discounts from global marketplaces.
        </p>
        <form action="/" method="GET" class="mt-4 sm:mt-5 space-y-3 w-full" id="filter-form">
          <div class="flex gap-2 w-full">
            <input
              name="q"
              value="{{ request('q') }}"
              placeholder="Search products, brands..."
              class="input-base border-0 text-gray-900 flex-1 min-w-0"
            />
            <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 flex-shrink-0">Search</button>
          </div>
          <div class="flex flex-col sm:flex-row flex-wrap gap-2 sm:items-center w-full">
            <div class="grid grid-cols-2 gap-2 w-full sm:w-auto sm:flex sm:items-center">
                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min ₹" class="rounded-lg border-0 bg-white/90 px-3 py-1.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 w-full sm:w-24 placeholder-gray-500 min-w-0" />
                <span class="text-white/80 hidden sm:inline">-</span>
                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max ₹" class="rounded-lg border-0 bg-white/90 px-3 py-1.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 w-full sm:w-24 placeholder-gray-500 min-w-0" />
            </div>
            <select name="min_discount" onchange="this.form.submit()" class="rounded-lg border-0 bg-white/90 px-3 py-1.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 w-full sm:w-36 flex-shrink-0">
                <option value="">Any Discount</option>
                <option value="10" {{ request('min_discount') == '10' ? 'selected' : '' }}>10%+ Off</option>
                <option value="25" {{ request('min_discount') == '25' ? 'selected' : '' }}>25%+ Off</option>
                <option value="50" {{ request('min_discount') == '50' ? 'selected' : '' }}>50%+ Off</option>
                <option value="75" {{ request('min_discount') == '75' ? 'selected' : '' }}>75%+ Off</option>
            </select>
          </div>
        </form>
        <div class="mt-4 flex flex-wrap gap-2 text-xs text-red-100">
            @foreach(['Laptops under ₹60k', 'Smartphones', 'Kitchen offers', 'Monitors', 'Headphones'] as $chip)
            <a href="/?q={{ urlencode($chip) }}" class="rounded-full bg-white/20 px-3 py-1 hover:bg-white/30 whitespace-nowrap">
              {{ $chip }}
            </a>
            @endforeach
        </div>
      </div>
      <div class="space-y-4 hidden md:block">
        <!-- KPI Strip Equivalent -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <div class="rounded-xl bg-white/10 p-3 text-center backdrop-blur">
                <div class="text-2xl font-black">24/7</div>
                <div class="text-[10px] uppercase tracking-wider text-red-100">Scanning</div>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center backdrop-blur">
                <div class="text-2xl font-black">100%</div>
                <div class="text-[10px] uppercase tracking-wider text-red-100">Verified</div>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center backdrop-blur">
                <div class="text-2xl font-black">AI</div>
                <div class="text-[10px] uppercase tracking-wider text-red-100">Scored</div>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center backdrop-blur">
                <div class="text-2xl font-black">Free</div>
                <div class="text-[10px] uppercase tracking-wider text-red-100">Access</div>
            </div>
        </div>

        <div class="rounded-2xl bg-white/15 p-4 text-sm backdrop-blur">
          <p class="font-semibold">Live Signals</p>
          <ul class="mt-3 space-y-2 text-red-50">
            <li>• Continuous crawling every 15 minutes</li>
            <li>• Verified discount scoring</li>
            <li>• Auto-publish top opportunities</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="surface">
    <h2 class="text-lg font-bold">Top Categories</h2>
    <div class="mt-3 flex flex-wrap gap-2 text-sm">
        @foreach($categories as $cat)
        <a href="/?category={{ $cat->slug }}" class="rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-red-700 transition hover:bg-red-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
            {{ $cat->name }}
        </a>
        @endforeach
    </div>
  </div>

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

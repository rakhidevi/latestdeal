@extends('layouts.app')

@section('content')
<section class="space-y-6">
  <div class="panel overflow-hidden bg-gradient-to-br from-red-500 via-rose-500 to-red-400 p-0 text-white">
    <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-2 md:p-8">
      <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight">
          Find real global deals in seconds
        </h1>
        <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-red-50 hidden sm:block">
          AI-scored discounts from global marketplaces.
        </p>
        <form action="/" method="GET" class="mt-4 sm:mt-5 space-y-3">
          <div class="flex gap-2">
            <input
              name="q"
              value="{{ request('q') }}"
              placeholder="Search products, brands, categories"
              class="input-base border-0 text-gray-900 flex-1"
            />
            <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800">Search</button>
          </div>
          <div class="flex flex-wrap gap-2 items-center">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min ₹" class="rounded-lg border-0 bg-white/90 px-3 py-1.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 flex-1 sm:w-24 placeholder-gray-500" />
                <span class="text-white/80 hidden sm:inline">-</span>
                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max ₹" class="rounded-lg border-0 bg-white/90 px-3 py-1.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 flex-1 sm:w-24 placeholder-gray-500" />
            </div>
            <select name="min_discount" onchange="this.form.submit()" class="rounded-lg border-0 bg-white/90 px-3 py-1.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 w-full sm:w-36">
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
            <a href="/?q={{ urlencode($chip) }}" class="rounded-full bg-white/20 px-3 py-1 hover:bg-white/30">
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
            <a href="/?category=all" class="btn-secondary px-3 py-1.5 text-sm sm:px-4 sm:py-2">View all</a>
        </div>
    </div>

    @if($deals->isEmpty())
      <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center dark:border-slate-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100">No deals available yet</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Scraper is running. Fresh deals will appear shortly.</p>
      </div>
    @else
      <div class="grid gap-3 grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
        @foreach($deals as $deal)
          <x-deal-card :deal="$deal" />
        @endforeach
      </div>
      
      <div class="mt-12 flex justify-center">
        {{ $deals->links() }}
      </div>
      
      <x-ad-banner slot="home-bottom" />
    @endif
  </div>
</section>
@endsection

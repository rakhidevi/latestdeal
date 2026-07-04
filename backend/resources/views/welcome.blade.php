@extends('layouts.app')

@section('meta')
    <meta name="description" content="Discover the best curated deals, discounts, and offers on the internet. Updated hourly by AI.">
    
    @if($deals->isNotEmpty())
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "ItemList",
      "itemListElement": [
        @foreach($deals as $index => $deal)
        {
          "@@type": "ListItem",
          "position": {{ $index + 1 }},
          "item": {
            "@@type": "Product",
            "name": "{{ addslashes($deal->title) }}",
            "image": "{{ asset($deal->image_path) }}",
            "offers": {
              "@@type": "Offer",
              "price": "{{ $deal->discounted_price }}",
              "priceCurrency": "INR",
              "availability": "https://schema.org/InStock",
              "url": "{{ route('deal.redirect', ['deal' => $deal->id]) }}"
            }
          }
        }@if(!$loop->last),@endif
        @endforeach
      ]
    }
    </script>
    @endif
@endsection

@section('content')
    <div class="mb-10 text-center">
        <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl">
            Today's <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-red-400">Hottest</span> Deals
        </h1>
        <p class="mt-4 text-lg leading-8 text-gray-600 max-w-2xl mx-auto">
            Our AI engine scans the web 24/7 to bring you the highest-value discounts before they expire.
        </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation & Filters -->
        <aside class="w-full lg:w-72 flex-shrink-0">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <form action="/" method="GET" class="mb-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3">Search</h3>
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search deals..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-1 focus:ring-red-500 text-sm transition">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </form>

                <div class="mb-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3 flex items-center justify-between">
                        Categories
                        @if(request('category'))
                            <a href="/" class="text-xs text-red-500 hover:text-red-700 font-medium">Clear</a>
                        @endif
                    </h3>
                    <div class="space-y-2">
                        @foreach($categories as $category)
                            <a href="/?category={{ $category->slug }}" class="block px-3 py-2 rounded-lg text-sm font-medium transition {{ request('category') === $category->slug ? 'bg-red-50 text-red-700' : 'text-gray-600 hover:bg-gray-50' }}">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3 flex items-center justify-between">
                        Brands
                        @if(request('brand'))
                            <a href="/" class="text-xs text-red-500 hover:text-red-700 font-medium">Clear</a>
                        @endif
                    </h3>
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                        @foreach($brands as $brand)
                            <a href="/?brand={{ urlencode($brand) }}" class="block px-3 py-2 rounded-lg text-sm font-medium transition {{ request('brand') === $brand ? 'bg-red-50 text-red-700' : 'text-gray-600 hover:bg-gray-50' }}">
                                {{ $brand }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3">Trending Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $t)
                            <a href="/?tag={{ $t->slug }}" class="px-3 py-1 rounded-full text-xs font-semibold transition {{ request('tag') === $t->slug ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                #{{ $t->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Top Ad Slot -->
            <x-ad-slot type="horizontal" class="mb-8" />

            @if($deals->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 text-center py-24">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-bold text-gray-900">No deals found</h3>
                    <p class="mt-2 text-sm text-gray-500">Try adjusting your filters or search query.</p>
                    <a href="/" class="mt-6 inline-block bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">View All Deals</a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($deals as $index => $deal)
                        <x-deal-card :deal="$deal" />
                        
                        <!-- Inline Ad Slot every 6th deal -->
                        @if(($index + 1) % 6 == 0)
                            <div class="sm:col-span-2 xl:col-span-3">
                                <x-ad-slot type="infeed" class="my-4" />
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="mt-12 flex justify-center">
                    {{ $deals->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Newsletter Subscription -->
    <div class="mt-16 bg-white border border-gray-200 rounded-2xl shadow-sm px-6 py-10 sm:px-12 sm:py-16 overflow-hidden relative">
        <div class="relative max-w-2xl mx-auto text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Never miss a massive price drop.</h2>
            <p class="mt-4 text-lg leading-6 text-gray-500">Subscribe to our weekly newsletter to get the absolute best, hand-picked deals delivered straight to your inbox.</p>
            <form action="{{ route('subscribe') }}" method="POST" class="mt-8 sm:flex justify-center">
                @csrf
                <label for="email" class="sr-only">Email address</label>
                <input id="email" name="email" type="email" autocomplete="email" required class="w-full px-5 py-3 border border-gray-300 shadow-sm placeholder-gray-400 focus:ring-1 focus:ring-accent focus:border-accent sm:max-w-xs rounded-md" placeholder="Enter your email">
                <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3 sm:flex-shrink-0">
                    <button type="submit" class="w-full flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-accent hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent">
                        Notify me
                    </button>
                </div>
            </form>
            @if(session('success'))
                <p class="mt-3 text-sm text-green-600 font-medium">{{ session('success') }}</p>
            @endif
        </div>
    </div>
@endsection

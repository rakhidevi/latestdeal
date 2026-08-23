@php
    $searchPlaceholder = 'Search deals, products & brands...';
    if (request()->is('category/*') && isset($category)) {
        $searchPlaceholder = 'Search in ' . $category->name . '...';
    } elseif (request()->routeIs('deal.show')) {
        $searchPlaceholder = 'Search for another deal...';
    }
@endphp

<header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur-md shadow-sm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Desktop Header Row -->
        <div class="flex h-20 items-center justify-between gap-8">
            
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="/" class="flex items-center gap-2 outline-none rounded-lg focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <img src="{{ asset('/images/logo.png') }}" alt="LatestDeal" class="h-9 w-auto object-contain" />
                </a>
            </div>
            
            <!-- Center: Persistent Search Bar (Desktop) -->
            <div class="hidden md:flex flex-1 max-w-2xl">
                @if(!request()->is('/'))
                <form action="/search" method="GET" class="w-full relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-red-500 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $searchPlaceholder }}" 
                           class="w-full bg-gray-50/50 hover:bg-gray-50 focus:bg-white text-gray-900 text-sm font-semibold rounded-full pl-12 pr-4 py-3.5 border border-gray-200 focus:border-red-400 focus:ring-4 focus:ring-red-100 outline-none transition-all shadow-sm placeholder:text-gray-400 placeholder:font-medium">
                </form>
                @endif
            </div>
            
            <!-- Right: Primary Navigation (Desktop) -->
            <nav class="hidden lg:flex items-center gap-1.5 text-[14px] font-bold text-gray-600">
                <a href="/search" class="px-3 py-2 rounded-lg hover:text-gray-900 hover:bg-gray-50 transition-colors">Deals</a>
                <a href="/search?sort=categories" class="px-3 py-2 rounded-lg hover:text-gray-900 hover:bg-gray-50 transition-colors">Categories</a>
                <a href="/search?sort=merchants" class="px-3 py-2 rounded-lg hover:text-gray-900 hover:bg-gray-50 transition-colors">Stores</a>
                <a href="/search?type=coupon" class="px-3 py-2 rounded-lg hover:text-gray-900 hover:bg-gray-50 transition-colors">Coupons</a>
                <a href="/search?sort=trending" class="px-3 py-2 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    Trending
                </a>
                
                <div class="w-px h-6 bg-gray-200 mx-2"></div>
                
                <a href="/login" class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-red-600 transition-all shadow-sm">Sign In</a>
            </nav>
            
            <!-- Mobile Menu Button -->
            <div class="flex items-center lg:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:outline-none transition-colors">
                    <span class="sr-only">Open main menu</span>
                    <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <svg x-show="mobileMenuOpen" class="h-6 w-6" x-cloak fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
        </div>
        
        <!-- Mobile Search Row (Always visible on mobile below logo) -->
        <div class="md:hidden pb-4 pt-1">
            @if(!request()->is('/'))
            <form action="/search" method="GET" class="w-full relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $searchPlaceholder }}" 
                       class="w-full bg-gray-50 focus:bg-white text-gray-900 text-sm font-semibold rounded-xl pl-11 pr-4 py-3.5 border border-gray-200 focus:border-red-400 outline-none transition-all shadow-sm">
            </form>
            @endif
        </div>
        
    </div>

    <!-- Mobile Navigation Menu Dropdown -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         x-cloak 
         class="lg:hidden absolute top-full left-0 w-full bg-white border-b border-gray-100 shadow-xl pb-4 pt-2 px-4 z-50">
         
        <div class="grid grid-cols-3 gap-2">
            <a href="/search" class="flex flex-col items-center justify-center py-4 bg-gray-50 rounded-2xl text-sm font-bold text-gray-700 hover:bg-gray-100 hover:text-gray-900 active:scale-95 transition-all">
                <svg class="w-6 h-6 mb-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                Deals
            </a>
            <a href="/search?sort=merchants" class="flex flex-col items-center justify-center py-4 bg-gray-50 rounded-2xl text-sm font-bold text-gray-700 hover:bg-gray-100 hover:text-gray-900 active:scale-95 transition-all">
                <svg class="w-6 h-6 mb-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Stores
            </a>
            <a href="/search?sort=categories" class="flex flex-col items-center justify-center py-4 bg-gray-50 rounded-2xl text-sm font-bold text-gray-700 hover:bg-gray-100 hover:text-gray-900 active:scale-95 transition-all">
                <svg class="w-6 h-6 mb-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Categories
            </a>
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-100">
            <a href="/login" class="flex w-full items-center justify-center px-4 py-3.5 rounded-xl bg-gray-900 text-white font-bold text-sm hover:bg-red-600 transition-colors shadow-sm">
                Sign In or Register
            </a>
        </div>
    </div>
</header>

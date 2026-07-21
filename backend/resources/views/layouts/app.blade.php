<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name='impact-site-verification' value='dcd870d6-a11b-48ec-8df2-15ba5c96630b'>
    
    @hasSection('meta')
        @yield('meta')
    @else
        <title>LatestDeal - Discover the Best Verified Deals Worldwide</title>
        <meta name="description" content="LatestDeal is your autonomous global deal discovery engine. We scour the web to find the best discounts, offers, and coupons so you never pay full price.">
        <link rel="canonical" href="{{ url()->current() }}">
        <meta property="og:title" content="LatestDeal - Discover the Best Verified Deals Worldwide">
        <meta property="og:description" content="LatestDeal is your autonomous global deal discovery engine. We scour the web to find the best discounts, offers, and coupons.">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta property="og:image" content="{{ asset('/images/logo.png') }}">
        <meta name="twitter:card" content="summary_large_image">
        <style>[x-cloak] { display: none !important; }</style>
    @endif

    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Organization",
      "name": "LatestDeal",
      "url": @json(url('/')),
      "logo": @json(asset('/images/logo.png')),
      "sameAs": [
        "https://t.me/latestdealin"
      ]
    }
    </script>

    @if(config('services.google.adsense_id'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.google.adsense_id') }}" crossorigin="anonymous"></script>
    @endif

    <!-- OneSignal Push Notifications -->
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
      window.OneSignalDeferred = window.OneSignalDeferred || [];
      OneSignalDeferred.push(async function(OneSignal) {
        await OneSignal.init({
          appId: "dummy-onesignal-app-id", // Replace with real App ID
          safari_web_id: "web.onesignal.auto.dummy",
          notifyButton: { enable: true },
        });
        
        OneSignal.User.PushSubscription.addEventListener("change", (subscription) => {
            if (subscription.current.optedIn) {
                const token = subscription.current.token;
                if(token) {
                    fetch('/api/subscribe', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ push_token: token })
                    }).catch(err => console.error("Error saving push token", err));
                }
            }
        });
      });
    </script>

    <!-- User Intelligence Center (UIC) Tracker -->
    <script src="{{ asset('js/uic-tracker.js') }}" defer></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    @php
        $defaultTheme = \App\Models\Setting::where('key', 'default_theme')->value('value') ?? 'red';
        $defaultColorMode = \App\Models\Setting::where('key', 'default_color_mode')->value('value') ?? 'auto';
    @endphp
    <script>
        window.appConfig = { theme: '{{ $defaultTheme }}', colorMode: '{{ $defaultColorMode }}' };
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        red: {
                            50: 'var(--theme-50)', 100: 'var(--theme-100)', 200: 'var(--theme-200)',
                            300: 'var(--theme-300)', 400: 'var(--theme-400)', 500: 'var(--theme-500)',
                            600: 'var(--theme-600)', 700: 'var(--theme-700)', 800: 'var(--theme-800)',
                            900: 'var(--theme-900)', 950: 'var(--theme-950)'
                        },
                        primary: { DEFAULT: 'var(--theme-500)', 500: 'var(--theme-500)', 600: 'var(--theme-600)' }
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            :root, html[data-theme="red"] {
                --theme-50: #fef2f2; --theme-100: #fee2e2; --theme-200: #fecaca;
                --theme-300: #fca5a5; --theme-400: #f87171; --theme-500: #ef4444;
                --theme-600: #dc2626; --theme-700: #b91c1c; --theme-800: #991b1b;
                --theme-900: #7f1d1d; --theme-950: #450a0a;
            }
            html[data-theme="green"] {
                --theme-50: #F4FBF7; --theme-100: #D6F2ED; --theme-200: #A7E0D2;
                --theme-300: #47B49A; --theme-400: #298F77; --theme-500: #1B5E3C;
                --theme-600: #104A2F; --theme-700: #102321; --theme-800: #0E1D1B;
                --theme-900: #120F12; --theme-950: #0E100F;
            }
            html[data-theme="amber"] {
                --theme-50: #fffbeb; --theme-100: #fef3c7; --theme-200: #fde68a;
                --theme-300: #fcd34d; --theme-400: #fbbf24; --theme-500: #f59e0b;
                --theme-600: #d97706; --theme-700: #b45309; --theme-800: #92400e;
                --theme-900: #78350f; --theme-950: #451a03;
            }
            
            :root { color-scheme: light; }
            html.dark { color-scheme: dark; }

            body {
                @apply min-h-screen bg-slate-50 text-gray-900 transition-colors duration-300;
                background-image: radial-gradient(circle at 50% 0%, color-mix(in srgb, var(--theme-500) 3%, transparent) 0%, transparent 50%);
            }
            html.dark body {
                @apply bg-slate-950 text-slate-100;
                background-image: radial-gradient(circle at 50% 0%, color-mix(in srgb, var(--theme-500) 8%, transparent) 0%, transparent 50%);
            }
            .section-title { @apply text-2xl font-black tracking-tight text-gray-900 dark:text-slate-100; }
            .section-subtitle { @apply mt-1 text-sm text-gray-600 dark:text-slate-400; }
            .panel { @apply rounded-2xl border border-red-100 bg-white/90 p-4 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/80; }
            .surface { @apply rounded-2xl border border-red-100 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900; }
            
            /* Accessibility tweaks */
            .btn-primary { @apply rounded-xl bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600 hover:text-white; }
            .btn-secondary { @apply rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-slate-700 dark:bg-slate-900 dark:text-red-400 dark:hover:bg-slate-800; }
            .input-base { @apply w-full rounded-xl border border-red-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-red-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500; }
            
            /* Logo filters */
            html[data-theme="green"] .theme-logo { filter: hue-rotate(135deg) brightness(0.85) contrast(1.2); }
            html[data-theme="amber"] .theme-logo { filter: hue-rotate(45deg) brightness(1.2) saturate(1.5); }
            
            [x-cloak] { display: none !important; }
        }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('themeSwitcher', () => ({
                isDark: false,
                colorTheme: 'red',
                open: false,
                init() {
                    // 1. Setup Color Theme
                    const storedTheme = localStorage.getItem("adh-color");
                    this.colorTheme = storedTheme || window.appConfig.theme;
                    document.documentElement.setAttribute('data-theme', this.colorTheme);

                    // 2. Setup Dark Mode (Auto by default)
                    const storedDark = localStorage.getItem("adh-dark");
                    
                    if (storedDark) {
                        this.isDark = storedDark === "dark";
                    } else if (window.appConfig.colorMode === "dark" || window.appConfig.colorMode === "light") {
                        this.isDark = window.appConfig.colorMode === "dark";
                    } else {
                        // Auto mode: check system pref or time (8 PM to 7 AM)
                        const hour = new Date().getHours();
                        const isNight = hour >= 20 || hour < 7;
                        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                        this.isDark = isNight || prefersDark;
                    }
                    
                    if(this.isDark) document.documentElement.classList.add("dark");
                },
                setDark(value) {
                    this.isDark = value;
                    document.documentElement.classList.toggle("dark", this.isDark);
                    localStorage.setItem("adh-dark", this.isDark ? "dark" : "light");
                },
                setColorTheme(theme) {
                    this.colorTheme = theme;
                    document.documentElement.setAttribute('data-theme', theme);
                    localStorage.setItem("adh-color", theme);
                }
            }))
        })
    </script>


</head>
<body x-data="themeSwitcher" class="antialiased">
    
    <header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-40 border-b border-red-100 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/85">
      <div class="mx-auto flex max-w-7xl px-4 sm:px-6 lg:px-8 py-3 items-center justify-between relative">
        
        <!-- Left Side: Logo -->
        <a href="/" class="flex items-center justify-start flex-shrink-0 z-50 relative">
          <img src="/images/logo.png" alt="LatestDeal" class="theme-logo h-8 md:h-10 w-auto block dark:hidden" />
          <img src="/images/logo-white.png" alt="LatestDeal" class="theme-logo h-8 md:h-10 w-auto hidden dark:block" />
        </a>

        <!-- Center: Desktop Mega Menu (Marketplace Discovery Hub) -->
        <div x-data="{ 
            megaPinned: false, 
            megaHover: false,
            brandQuery: '', 
            searchResults: [], 
            searchQuery: '',
            suggestions: { brands: [], categories: [], deals: [] },
            tickerIndex: 0,
            tickers: [
                '⚡ 143 New Deals Today', 
                '🤖 AI Verified 96% Accuracy', 
                '🔥 58 Flash Sales Live', 
                '🏷️ 1,200 Verified Coupons'
            ],
            init() {
                setInterval(() => {
                    this.tickerIndex = (this.tickerIndex + 1) % this.tickers.length;
                }, 3500);
            },
            async fetchSuggestions() {
                if (this.searchQuery.length >= 2) {
                    try {
                        const res = await fetch('/api/v1/search/suggestions?q=' + encodeURIComponent(this.searchQuery));
                        this.suggestions = await res.json();
                    } catch (e) {
                        this.suggestions = { brands: [], categories: [], merchants: [], deals: [] };
                    }
                } else {
                    this.suggestions = { brands: [], categories: [], merchants: [], deals: [] };
                }
            },
            async fetchServerBrands() {
                if (this.brandQuery.length >= 2) {
                    try {
                        const res = await fetch('/api/v1/brands/search?q=' + encodeURIComponent(this.brandQuery));
                        const json = await res.json();
                        this.searchResults = json.data || [];
                    } catch (e) {
                        this.searchResults = [];
                    }
                } else {
                    this.searchResults = [];
                }
            } 
        }" @mouseenter="megaHover = true" @mouseleave="megaHover = false" class="hidden lg:flex absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2 items-center text-[14px] font-medium text-gray-700 dark:text-slate-200 z-30">
            
            <!-- Explore Deals Trigger (Hover or Click to Pin) -->
            <div @click="megaPinned = !megaPinned" class="py-4 px-4 cursor-pointer flex items-center gap-1.5 hover:text-red-600 dark:hover:text-red-400 transition select-none">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <span>Explore Deals</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180 text-red-600': megaPinned || megaHover }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                <template x-if="megaPinned">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-ping ml-0.5"></span>
                </template>
            </div>
            
            <!-- Mega Menu Dropdown Panel (with Hover Bridge pt-2) -->
            <div x-cloak x-show="megaPinned || megaHover" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 -translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 -translate-y-2" @click.outside="megaPinned = false" class="absolute top-full left-1/2 -translate-x-1/2 w-[1060px] bg-white dark:bg-slate-900 rounded-2xl shadow-[0_25px_60px_-15px_rgba(0,0,0,0.25)] border border-gray-100 dark:border-slate-800 p-7">
                
                <!-- 1. Dominant Search Header + 3. Dynamic Ticker + 5. Popular Searches + 6. Live Search Autocomplete Popover -->
                <div class="mb-6 pb-5 border-b border-gray-100 dark:border-slate-800">
                    <div class="flex items-center justify-between gap-4 mb-3 relative">
                        <!-- Search Form (15-20% Taller & Dominant) -->
                        <form action="/" method="GET" class="relative flex-1">
                            <input type="text" name="search" x-model="searchQuery" @input.debounce.250ms="fetchSuggestions()" placeholder="🔍 Search deals, products, brands, or categories (e.g. 'Samsung', 'Laptop')..." class="w-full text-base bg-gray-50 dark:bg-slate-800/90 text-gray-900 dark:text-white border-2 border-gray-200 dark:border-slate-700 rounded-xl pl-5 pr-12 py-3.5 outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition shadow-inner font-medium">
                            <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-600 transition p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>
                        </form>

                        <!-- Live Search Autocomplete Suggestions Popover -->
                        <div x-cloak x-show="searchQuery.length >= 2 && (suggestions.brands.length > 0 || suggestions.categories.length > 0 || suggestions.merchants.length > 0 || suggestions.deals.length > 0)" class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-2xl z-50 p-4 max-h-96 overflow-y-auto">
                            <!-- Brands Suggestions -->
                            <template x-if="suggestions.brands && suggestions.brands.length > 0">
                                <div class="mb-3">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-1.5">Brands</div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <template x-for="b in suggestions.brands" :key="b.name">
                                            <a :href="b.url" class="flex items-center justify-between p-2 rounded-lg hover:bg-red-50 dark:hover:bg-slate-800 transition">
                                                <span class="font-bold text-gray-800 dark:text-slate-200 text-xs" x-text="b.name"></span>
                                                <span class="text-[10px] text-red-600 dark:text-red-400 font-semibold" x-text="b.count"></span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Categories Suggestions -->
                            <template x-if="suggestions.categories && suggestions.categories.length > 0">
                                <div class="mb-3">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-1.5">Categories</div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <template x-for="c in suggestions.categories" :key="c.name">
                                            <a :href="c.url" class="flex items-center justify-between p-2 rounded-lg hover:bg-red-50 dark:hover:bg-slate-800 transition">
                                                <span class="font-bold text-gray-800 dark:text-slate-200 text-xs flex items-center gap-1.5">
                                                    <span x-text="c.icon"></span>
                                                    <span x-text="c.name"></span>
                                                </span>
                                                <span class="text-[10px] text-gray-500" x-text="c.count"></span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Merchants Suggestions -->
                            <template x-if="suggestions.merchants && suggestions.merchants.length > 0">
                                <div class="mb-3">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-1.5">Merchants & Stores</div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <template x-for="m in suggestions.merchants" :key="m.name">
                                            <a :href="m.url" class="flex items-center justify-between p-2 rounded-lg hover:bg-red-50 dark:hover:bg-slate-800 transition">
                                                <span class="font-bold text-gray-800 dark:text-slate-200 text-xs flex items-center gap-1.5">
                                                    <span x-text="m.icon"></span>
                                                    <span x-text="m.name"></span>
                                                </span>
                                                <span class="text-[10px] text-gray-500" x-text="m.count"></span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Deals Suggestions -->
                            <template x-if="suggestions.deals && suggestions.deals.length > 0">
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-1.5">Deals</div>
                                    <div class="space-y-1">
                                        <template x-for="d in suggestions.deals" :key="d.name">
                                            <a :href="d.url" class="flex items-center justify-between p-2 rounded-lg hover:bg-red-50 dark:hover:bg-slate-800 transition text-xs">
                                                <span class="font-medium text-gray-800 dark:text-slate-200 truncate pr-2" x-text="d.name"></span>
                                                <span class="font-extrabold text-red-600 dark:text-red-400 text-xs whitespace-nowrap" x-text="d.count"></span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Dynamic Live Status Ticker -->
                        <div class="hidden sm:flex items-center gap-2 bg-gradient-to-r from-red-50 to-orange-50 dark:from-slate-800 dark:to-slate-800/80 border border-red-100 dark:border-slate-700 px-4 py-3 rounded-xl text-xs font-semibold text-gray-700 dark:text-slate-200 shadow-sm min-w-[210px] justify-center">
                            <span x-text="tickers[tickerIndex]" class="transition-all duration-500 transform"></span>
                        </div>
                    </div>

                    <!-- Popular Searches Chips -->
                    <div class="flex items-center gap-2 text-xs">
                        <span class="font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider text-[10px]">Popular:</span>
                        <a href="/?q=Laptop" class="bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 px-2.5 py-1 rounded-md font-medium hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400 transition">Laptop</a>
                        <a href="/?q=iPhone" class="bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 px-2.5 py-1 rounded-md font-medium hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400 transition">iPhone</a>
                        <a href="/?q=Headphones" class="bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 px-2.5 py-1 rounded-md font-medium hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400 transition">Headphones</a>
                        <a href="/?q=Mattress" class="bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 px-2.5 py-1 rounded-md font-medium hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400 transition">Mattress</a>
                        <a href="/?q=Juicer" class="bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 px-2.5 py-1 rounded-md font-medium hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400 transition">Juicer</a>
                    </div>
                </div>

                <div class="grid grid-cols-5 gap-7">
                    <!-- Shop by Category (with Icons & View All) -->
                    <div class="col-span-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-3 pb-1 border-b border-gray-100 dark:border-slate-800">Categories</h3>
                            <ul class="space-y-2">
                                @if(isset($nav['categories']) && $nav['categories']->isNotEmpty())
                                    @foreach($nav['categories']->take(6) as $cat)
                                    @php
                                        $isActiveCat = request()->routeIs('deals.category') && request()->route('slug') === $cat->slug;
                                    @endphp
                                    <li>
                                        <a href="{{ route('deals.category', $cat->slug) }}" class="flex items-center justify-between text-[13px] transition py-1 px-1.5 rounded-lg {{ $isActiveCat ? 'bg-red-50 dark:bg-red-950/60 border-l-4 border-red-600 pl-2 font-bold text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-50 dark:hover:bg-slate-800/60 font-medium' }}">
                                            <span class="truncate pr-1.5 flex items-center gap-1.5">
                                                <span class="text-base">{{ $cat->icon }}</span>
                                                <span class="truncate">{{ $cat->name }}</span>
                                            </span>
                                            <span class="text-[11px] {{ $isActiveCat ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 font-bold' : 'text-gray-400 bg-gray-100 dark:bg-slate-800' }} px-1.5 py-0.5 rounded-full">{{ $cat->deal_count ?? $cat->deals_count }}</span>
                                        </a>
                                    </li>
                                    @endforeach
                                @else
                                    <li class="text-xs text-gray-400 italic">Indexing categories...</li>
                                @endif
                            </ul>
                        </div>
                        <a href="{{ route('directory.categories') }}" class="mt-4 pt-2 border-t border-gray-100 dark:border-slate-800 text-xs font-semibold text-red-600 dark:text-red-400 hover:underline flex items-center justify-between">
                            <span>View All Categories</span>
                            <span>→</span>
                        </a>
                    </div>

                    <!-- Shop by Brand (Live Search + Smart Visibility Filter) -->
                    <div class="col-span-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-3 pb-1 border-b border-gray-100 dark:border-slate-800">Top Brands</h3>
                            
                            @if(isset($nav['brands']) && $nav['brands']->count() >= 8)
                            <div class="mb-2">
                                <input type="text" x-model="brandQuery" @input.debounce.300ms="fetchServerBrands()" placeholder="Filter brand..." class="w-full text-xs bg-gray-50 dark:bg-slate-800 text-gray-800 dark:text-slate-200 border border-gray-200 dark:border-slate-700 rounded-lg px-2 py-1 outline-none focus:ring-1 focus:ring-red-400">
                            </div>
                            @endif

                            <ul class="space-y-1.5">
                                <template x-if="brandQuery.length >= 2 && searchResults.length > 0">
                                    <template x-for="item in searchResults.slice(0, 6)" :key="item.id">
                                        <li>
                                            <a :href="item.url" class="flex items-center justify-between text-[13px] font-medium text-gray-600 dark:text-slate-300 hover:text-red-600 transition py-1 px-1.5">
                                                <span class="truncate pr-1.5" x-text="item.name"></span>
                                                <span class="text-[11px] text-gray-400 bg-gray-100 dark:bg-slate-800 px-1.5 py-0.5 rounded-full" x-text="item.deal_count"></span>
                                            </a>
                                        </li>
                                    </template>
                                </template>
                                <template x-if="brandQuery.length < 2 || searchResults.length === 0">
                                    <div>
                                        @if(isset($nav['brands']) && $nav['brands']->isNotEmpty())
                                            @foreach($nav['brands']->take(6) as $brand)
                                            @php
                                                $isActiveBrand = request()->routeIs('deals.brand') && request()->route('slug') === $brand->slug;
                                            @endphp
                                            <div x-show="!brandQuery || '{{ strtolower($brand->name) }}'.includes(brandQuery.toLowerCase())">
                                                <a href="{{ route('deals.brand', $brand->slug) }}" class="flex items-center justify-between text-[13px] transition py-1 px-1.5 rounded-lg {{ $isActiveBrand ? 'bg-red-50 dark:bg-red-950/60 border-l-4 border-red-600 pl-2 font-bold text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-50 dark:hover:bg-slate-800/60 font-medium' }}">
                                                    <span class="truncate pr-1.5">{{ $brand->name }}</span>
                                                    <span class="text-[11px] {{ $isActiveBrand ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 font-bold' : 'text-gray-400 bg-gray-100 dark:bg-slate-800' }} px-1.5 py-0.5 rounded-full">{{ $brand->deal_count ?? $brand->deals_count }}</span>
                                                </a>
                                            </div>
                                            @endforeach
                                        @else
                                            <div class="text-xs text-gray-400 italic">Indexing brands...</div>
                                        @endif
                                    </div>
                                </template>
                            </ul>
                        </div>
                        <a href="{{ route('directory.brands') }}" class="mt-4 pt-2 border-t border-gray-100 dark:border-slate-800 text-xs font-semibold text-red-600 dark:text-red-400 hover:underline flex items-center justify-between">
                            <span>View All Brands</span>
                            <span>→</span>
                        </a>
                    </div>

                    <!-- Shop by Merchant (with Logos/Badges) -->
                    <div class="col-span-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-3 pb-1 border-b border-gray-100 dark:border-slate-800">Merchants</h3>
                            <ul class="space-y-2">
                                @if(isset($nav['merchants']) && $nav['merchants']->isNotEmpty())
                                    @foreach($nav['merchants']->take(6) as $merchant)
                                    @php
                                        $mSlug = Str::slug($merchant->name);
                                        $isActiveMerchant = request()->routeIs('deals.merchant') && (request()->route('slug') === $mSlug || strtolower(request()->route('slug')) === strtolower($merchant->name));
                                    @endphp
                                    <li>
                                        <a href="{{ route('deals.merchant', $mSlug) }}" class="flex items-center justify-between text-[13px] transition py-1 px-1.5 rounded-lg {{ $isActiveMerchant ? 'bg-red-50 dark:bg-red-950/60 border-l-4 border-red-600 pl-2 font-bold text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-50 dark:hover:bg-slate-800/60 font-medium' }}">
                                            <span class="truncate pr-1.5 flex items-center gap-1.5">
                                                <span class="text-base">{{ $merchant->icon }}</span>
                                                <span class="truncate">{{ $merchant->name }}</span>
                                            </span>
                                            <span class="text-[11px] {{ $isActiveMerchant ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 font-bold' : 'text-gray-400 bg-gray-100 dark:bg-slate-800' }} px-1.5 py-0.5 rounded-full">{{ $merchant->deal_count ?? $merchant->deals_count }}</span>
                                        </a>
                                    </li>
                                    @endforeach
                                @else
                                    <li class="text-xs text-gray-400 italic">Indexing merchants...</li>
                                @endif
                            </ul>
                        </div>
                        <a href="{{ route('directory.merchants') }}" class="mt-4 pt-2 border-t border-gray-100 dark:border-slate-800 text-xs font-semibold text-red-600 dark:text-red-400 hover:underline flex items-center justify-between">
                            <span>View All Merchants</span>
                            <span>→</span>
                        </a>
                    </div>

                    <!-- AI Picks & Trending Today -->
                    <div class="col-span-1">
                        <h3 class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-3 pb-1 border-b border-gray-100 dark:border-slate-800">AI Picks & Deals</h3>
                        <ul class="space-y-2">
                            <li><a href="/?tag=trending" class="text-gray-600 dark:text-slate-300 hover:text-orange-600 text-[13px] font-medium transition flex items-center gap-2 py-1 px-1.5 hover:bg-gray-50 dark:hover:bg-slate-800/60 rounded-lg">🔥 Trending Today</a></li>
                            <li><a href="/?tag=ai-picks" class="text-gray-600 dark:text-slate-300 hover:text-orange-600 text-[13px] font-medium transition flex items-center gap-2 py-1 px-1.5 hover:bg-gray-50 dark:hover:bg-slate-800/60 rounded-lg">🤖 AI Recommendations</a></li>
                            <li><a href="/?sort=discount" class="text-gray-600 dark:text-slate-300 hover:text-orange-600 text-[13px] font-medium transition flex items-center gap-2 py-1 px-1.5 hover:bg-gray-50 dark:hover:bg-slate-800/60 rounded-lg">⭐ Best Value Deals</a></li>
                            <li><a href="/?q=Flash" class="text-gray-600 dark:text-slate-300 hover:text-red-600 text-[13px] font-medium transition flex items-center gap-2 py-1 px-1.5 hover:bg-gray-50 dark:hover:bg-slate-800/60 rounded-lg">⚡ Flash Sales</a></li>
                            <li><a href="{{ route('directory.categories') }}" class="text-gray-600 dark:text-slate-300 hover:text-red-600 text-[13px] font-medium transition flex items-center gap-2 py-1 px-1.5 hover:bg-gray-50 dark:hover:bg-slate-800/60 rounded-lg">🎫 Active Categories</a></li>
                            <li><a href="/categories/courses-education" class="text-gray-600 dark:text-slate-300 hover:text-blue-600 text-[13px] font-medium transition flex items-center gap-2 py-1 px-1.5 hover:bg-gray-50 dark:hover:bg-slate-800/60 rounded-lg">🎓 Free Courses</a></li>
                        </ul>
                    </div>

                    <!-- 2. Distinct Hot Discounts Accent Column -->
                    <div class="col-span-1 rounded-2xl bg-gradient-to-b from-gray-50 to-red-50/40 dark:from-slate-800/90 dark:to-red-950/30 p-4 border border-gray-200/80 dark:border-slate-700/80 shadow-sm">
                        <h3 class="text-[11px] font-bold uppercase tracking-wider text-gray-900 dark:text-white mb-3 flex items-center gap-1.5">
                            <span>🔥</span> Hot Discounts
                        </h3>
                        @php
                            $currRange = request()->route('range');
                            $maxPrice = request('max_price');
                        @endphp
                        <ul class="space-y-2">
                            <li><a href="{{ route('deals.discount', '90-off') }}" class="text-[13px] transition flex items-center gap-2 py-1 px-1.5 rounded-lg {{ $currRange === '90-off' ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 font-extrabold' : 'text-gray-700 dark:text-slate-200 hover:text-red-600 font-bold' }}">🔥 90%+ Off</a></li>
                            <li><a href="{{ route('deals.discount', '70-89-off') }}" class="text-[13px] transition flex items-center gap-2 py-1 px-1.5 rounded-lg {{ $currRange === '70-89-off' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-extrabold' : 'text-gray-700 dark:text-slate-200 hover:text-emerald-600 font-semibold' }}">🟢 70% – 89% Off</a></li>
                            <li><a href="{{ route('deals.discount', '50-69-off') }}" class="text-[13px] transition flex items-center gap-2 py-1 px-1.5 rounded-lg {{ $currRange === '50-69-off' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-extrabold' : 'text-gray-700 dark:text-slate-200 hover:text-amber-600 font-semibold' }}">🟡 50% – 69% Off</a></li>
                            <li><a href="{{ route('deals.discount', '25-49-off') }}" class="text-[13px] transition flex items-center gap-2 py-1 px-1.5 rounded-lg {{ $currRange === '25-49-off' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 font-extrabold' : 'text-gray-700 dark:text-slate-200 hover:text-blue-600 font-semibold' }}">🔵 25% – 49% Off</a></li>
                            <li class="pt-2 border-t border-gray-200 dark:border-slate-700/80"><a href="/?max_price=500" class="text-[13px] transition flex items-center gap-2 py-1 px-1.5 rounded-lg {{ $maxPrice == '500' ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 font-extrabold' : 'text-gray-700 dark:text-slate-200 hover:text-red-600 font-semibold' }}">💵 Under ₹500</a></li>
                            <li><a href="/?max_price=1000" class="text-[13px] transition flex items-center gap-2 py-1 px-1.5 rounded-lg {{ $maxPrice == '1000' ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 font-extrabold' : 'text-gray-700 dark:text-slate-200 hover:text-red-600 font-semibold' }}">🏷️ Under ₹1,000</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: CTA / Auth -->
        <div class="hidden lg:flex items-center justify-end gap-6 relative z-50">
          <a href="/assistant" class="text-[14px] font-semibold text-orange-500 hover:text-orange-600 dark:text-orange-400 flex items-center gap-1.5 transition whitespace-nowrap">
             <svg class="w-[13.5px] h-[13.5px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 17v4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 19h4"/></svg>
             AI Assistant
          </a>
          
          @auth
            <a href="{{ route('shopper.dashboard') }}" class="btn-primary flex items-center gap-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Dashboard
            </a>
          @else
            <a href="{{ route('shopper.login') }}" class="btn-primary flex items-center gap-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all whitespace-nowrap">
                <svg class="w-[13.5px] h-[13.5px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4" stroke-width="2"/></svg>
                Login / Signup
            </a>
          @endauth
        </div>

        <button @click="mobileMenuOpen = !mobileMenuOpen; if(mobileMenuOpen) { $nextTick(() => $refs.mobileSearchInput.focus()); }" class="lg:hidden p-2 -mr-2 text-gray-600 dark:text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
      </div>

      <!-- Mobile Task-Oriented Drawer (Optimized for Speed & One-Handed Use) -->
      <div x-cloak x-show="mobileMenuOpen" x-data="{
          mobileSearchQuery: '',
          mobileSuggestions: { brands: [], categories: [], merchants: [], deals: [] },
          async fetchMobileSuggestions() {
              if (this.mobileSearchQuery.length >= 2) {
                  try {
                      const res = await fetch('/api/v1/search/suggestions?q=' + encodeURIComponent(this.mobileSearchQuery));
                      this.mobileSuggestions = await res.json();
                  } catch (e) {
                      this.mobileSuggestions = { brands: [], categories: [], merchants: [], deals: [] };
                  }
              } else {
                  this.mobileSuggestions = { brands: [], categories: [], merchants: [], deals: [] };
              }
          }
      }" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden border-t border-gray-100 dark:border-slate-800 px-4 py-5 bg-white dark:bg-slate-900 shadow-2xl absolute w-full z-50 max-h-[85vh] overflow-y-auto">
        
        <!-- 1. Top Search Bar inside Mobile Drawer + Live Autocomplete Popover -->
        <div class="relative mb-4">
            <form action="/" method="GET" class="relative">
                <input type="text" name="search" x-ref="mobileSearchInput" x-model="mobileSearchQuery" @input.debounce.250ms="fetchMobileSuggestions()" placeholder="🔍 Search deals, products, brands..." class="w-full text-sm bg-gray-50 dark:bg-slate-800 text-gray-900 dark:text-white border-2 border-gray-200 dark:border-slate-700 rounded-xl pl-4 pr-10 py-3 outline-none focus:ring-2 focus:ring-red-500 font-medium shadow-inner">
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </form>

            <!-- Mobile Live Autocomplete Popover -->
            <div x-cloak x-show="mobileSearchQuery.length >= 2 && (mobileSuggestions.brands.length > 0 || mobileSuggestions.categories.length > 0 || mobileSuggestions.merchants.length > 0 || mobileSuggestions.deals.length > 0)" class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-2xl z-50 p-3 max-h-80 overflow-y-auto">
                <template x-if="mobileSuggestions.brands && mobileSuggestions.brands.length > 0">
                    <div class="mb-2">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Brands</div>
                        <div class="space-y-1">
                            <template x-for="b in mobileSuggestions.brands" :key="b.name">
                                <a :href="b.url" class="flex items-center justify-between p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-slate-800 text-xs">
                                    <span class="font-bold text-gray-800 dark:text-slate-200" x-text="b.name"></span>
                                    <span class="text-[10px] text-red-600 font-semibold" x-text="b.count"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="mobileSuggestions.categories && mobileSuggestions.categories.length > 0">
                    <div class="mb-2">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Categories</div>
                        <div class="space-y-1">
                            <template x-for="c in mobileSuggestions.categories" :key="c.name">
                                <a :href="c.url" class="flex items-center justify-between p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-slate-800 text-xs">
                                    <span class="font-bold text-gray-800 dark:text-slate-200 flex items-center gap-1">
                                        <span x-text="c.icon"></span>
                                        <span x-text="c.name"></span>
                                    </span>
                                    <span class="text-[10px] text-gray-400" x-text="c.count"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="mobileSuggestions.merchants && mobileSuggestions.merchants.length > 0">
                    <div class="mb-2">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Stores</div>
                        <div class="space-y-1">
                            <template x-for="m in mobileSuggestions.merchants" :key="m.name">
                                <a :href="m.url" class="flex items-center justify-between p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-slate-800 text-xs">
                                    <span class="font-bold text-gray-800 dark:text-slate-200 flex items-center gap-1">
                                        <span x-text="m.icon"></span>
                                        <span x-text="m.name"></span>
                                    </span>
                                    <span class="text-[10px] text-gray-400" x-text="m.count"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="mobileSuggestions.deals && mobileSuggestions.deals.length > 0">
                    <div>
                        <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Deals</div>
                        <div class="space-y-1">
                            <template x-for="d in mobileSuggestions.deals" :key="d.name">
                                <a :href="d.url" class="flex items-center justify-between p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-slate-800 text-xs">
                                    <span class="font-medium text-gray-800 dark:text-slate-200 truncate pr-2" x-text="d.name"></span>
                                    <span class="font-bold text-red-600 text-[10px] whitespace-nowrap" x-text="d.count"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- 2. 🔥 Quick Access Actions (2x2 Grid) -->
        <div class="mb-4">
            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">🔥 Quick Actions</div>
            <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                <a href="/?sort=discount" class="flex items-center gap-2 p-2.5 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-100 dark:border-red-900/30">
                    <span class="text-base">🔥</span> Today's Deals
                </a>
                <a href="/?tag=flash-sales" class="flex items-center gap-2 p-2.5 rounded-xl bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 border border-orange-100 dark:border-orange-900/30">
                    <span class="text-base">⚡</span> Flash Sales
                </a>
                <a href="/?tag=ai-picks" class="flex items-center gap-2 p-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-900/30">
                    <span class="text-base">🤖</span> AI Picks
                </a>
                <a href="/?tag=coupons" class="flex items-center gap-2 p-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-900/30">
                    <span class="text-base">🎫</span> Coupons
                </a>
            </div>
        </div>

        <!-- 3. 🤖 Ask Shopping AI Card (Primary Differentiator) -->
        <a href="/assistant" class="block mb-4 p-4 rounded-xl bg-gradient-to-r from-red-600 to-orange-600 text-white shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">🤖</span>
                    <div>
                        <div class="font-bold text-sm">Ask Shopping AI</div>
                        <div class="text-[11px] text-red-100">Compare products & find best laptop deals</div>
                    </div>
                </div>
                <span class="text-xs font-extrabold bg-white/20 px-2.5 py-1 rounded-lg">Ask →</span>
            </div>
        </a>

        <!-- 4. Accordion Browse (Single Accordion Expanded at a Time) -->
        <div x-data="{ activeAccordion: 'categories' }" class="space-y-3 mb-4">
            
            <!-- 📂 Categories Accordion -->
            <div class="border border-gray-100 dark:border-slate-800 rounded-xl overflow-hidden">
                <button @click="activeAccordion = (activeAccordion === 'categories' ? '' : 'categories')" class="w-full flex items-center justify-between p-3 bg-gray-50/50 dark:bg-slate-800/40 font-bold text-xs uppercase tracking-wider text-gray-700 dark:text-slate-200">
                    <span class="flex items-center gap-2">
                        <span>📂</span> Categories
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeAccordion === 'categories' ? 'rotate-180 text-red-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-cloak x-show="activeAccordion === 'categories'" x-transition class="p-3 space-y-2 border-t border-gray-100 dark:border-slate-800">
                    @if(isset($nav['categories']) && $nav['categories']->isNotEmpty())
                        @foreach($nav['categories']->take(6) as $cat)
                        <a href="{{ route('deals.category', $cat->slug) }}" class="flex items-center justify-between text-xs font-medium text-gray-700 dark:text-slate-300 py-1 hover:text-red-600">
                            <span class="flex items-center gap-2">
                                <span>{{ $cat->icon }}</span>
                                <span>{{ $cat->name }}</span>
                            </span>
                            <span class="text-[10px] text-gray-400 bg-gray-100 dark:bg-slate-800 px-1.5 py-0.5 rounded-full">{{ $cat->deal_count ?? $cat->deals_count }}</span>
                        </a>
                        @endforeach
                    @endif
                    <a href="{{ route('directory.categories') }}" class="block pt-2 text-xs font-bold text-red-600 dark:text-red-400 hover:underline">View All Categories →</a>
                </div>
            </div>

            <!-- 🏷️ Top Brands Accordion -->
            <div class="border border-gray-100 dark:border-slate-800 rounded-xl overflow-hidden">
                <button @click="activeAccordion = (activeAccordion === 'brands' ? '' : 'brands')" class="w-full flex items-center justify-between p-3 bg-gray-50/50 dark:bg-slate-800/40 font-bold text-xs uppercase tracking-wider text-gray-700 dark:text-slate-200">
                    <span class="flex items-center gap-2">
                        <span>🏷️</span> Top Brands
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeAccordion === 'brands' ? 'rotate-180 text-red-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-cloak x-show="activeAccordion === 'brands'" x-transition class="p-3 space-y-2 border-t border-gray-100 dark:border-slate-800">
                    @if(isset($nav['brands']) && $nav['brands']->isNotEmpty())
                        @foreach($nav['brands']->take(5) as $brand)
                        <a href="{{ route('deals.brand', $brand->slug) }}" class="flex items-center justify-between text-xs font-medium text-gray-700 dark:text-slate-300 py-1 hover:text-red-600">
                            <span>{{ $brand->name }}</span>
                            <span class="text-[10px] text-gray-400 bg-gray-100 dark:bg-slate-800 px-1.5 py-0.5 rounded-full">{{ $brand->deal_count ?? $brand->deals_count }}</span>
                        </a>
                        @endforeach
                    @endif
                    <a href="{{ route('directory.brands') }}" class="block pt-2 text-xs font-bold text-red-600 dark:text-red-400 hover:underline">View All Brands →</a>
                </div>
            </div>

            <!-- 🏪 Stores Accordion -->
            <div class="border border-gray-100 dark:border-slate-800 rounded-xl overflow-hidden">
                <button @click="activeAccordion = (activeAccordion === 'stores' ? '' : 'stores')" class="w-full flex items-center justify-between p-3 bg-gray-50/50 dark:bg-slate-800/40 font-bold text-xs uppercase tracking-wider text-gray-700 dark:text-slate-200">
                    <span class="flex items-center gap-2">
                        <span>🏪</span> Verified Stores
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeAccordion === 'stores' ? 'rotate-180 text-red-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-cloak x-show="activeAccordion === 'stores'" x-transition class="p-3 space-y-2 border-t border-gray-100 dark:border-slate-800">
                    @if(isset($nav['merchants']) && $nav['merchants']->isNotEmpty())
                        @foreach($nav['merchants']->take(5) as $merchant)
                        <a href="{{ route('deals.merchant', Str::slug($merchant->name)) }}" class="flex items-center justify-between text-xs font-medium text-gray-700 dark:text-slate-300 py-1 hover:text-red-600">
                            <span class="flex items-center gap-2">
                                <span>{{ $merchant->icon }}</span>
                                <span>{{ $merchant->name }}</span>
                            </span>
                            <span class="text-[10px] text-gray-400 bg-gray-100 dark:bg-slate-800 px-1.5 py-0.5 rounded-full">{{ $merchant->deal_count ?? $merchant->deals_count }}</span>
                        </a>
                        @endforeach
                    @endif
                    <a href="{{ route('directory.merchants') }}" class="block pt-2 text-xs font-bold text-red-600 dark:text-red-400 hover:underline">View All Merchants →</a>
                </div>
            </div>

            <!-- 💰 Discounts Accordion -->
            <div class="border border-gray-100 dark:border-slate-800 rounded-xl overflow-hidden">
                <button @click="activeAccordion = (activeAccordion === 'discounts' ? '' : 'discounts')" class="w-full flex items-center justify-between p-3 bg-gray-50/50 dark:bg-slate-800/40 font-bold text-xs uppercase tracking-wider text-gray-700 dark:text-slate-200">
                    <span class="flex items-center gap-2">
                        <span>💰</span> Browse Discounts
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeAccordion === 'discounts' ? 'rotate-180 text-red-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-cloak x-show="activeAccordion === 'discounts'" x-transition class="p-3 space-y-2 border-t border-gray-100 dark:border-slate-800 text-xs font-semibold">
                    <a href="{{ route('deals.discount', '90-off') }}" class="block py-1 text-red-600">🔥 90%+ Off</a>
                    <a href="{{ route('deals.discount', '70-89-off') }}" class="block py-1 text-emerald-600">🟢 70% – 89% Off</a>
                    <a href="{{ route('deals.discount', '50-69-off') }}" class="block py-1 text-amber-600">🟡 50% – 69% Off</a>
                    <a href="/?max_price=500" class="block py-1 text-gray-700 dark:text-slate-300">💵 Under ₹500</a>
                </div>
            </div>

            <!-- ⭐ Verified Deals (Replaced "Quality") -->
            <div class="border border-gray-100 dark:border-slate-800 rounded-xl overflow-hidden">
                <button @click="activeAccordion = (activeAccordion === 'verified' ? '' : 'verified')" class="w-full flex items-center justify-between p-3 bg-gray-50/50 dark:bg-slate-800/40 font-bold text-xs uppercase tracking-wider text-gray-700 dark:text-slate-200">
                    <span class="flex items-center gap-2">
                        <span>⭐</span> Verified Deals
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeAccordion === 'verified' ? 'rotate-180 text-red-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-cloak x-show="activeAccordion === 'verified'" x-transition class="p-3 space-y-2 border-t border-gray-100 dark:border-slate-800 text-xs font-medium">
                    <a href="/?min_trust_score=90" class="flex items-center gap-2 py-1 text-gray-700 dark:text-slate-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span> 90+ Score (Gold Trust)
                    </a>
                    <a href="/?min_trust_score=80" class="flex items-center gap-2 py-1 text-gray-700 dark:text-slate-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> 80+ Score (Silver Trust)
                    </a>
                </div>
            </div>
        </div>

        <!-- Auth CTA -->
        <div class="pt-3 border-t border-gray-100 dark:border-slate-800">
            @auth
              <a href="{{ route('shopper.dashboard') }}" class="btn-primary w-full text-center flex items-center justify-center gap-2 shadow-lg text-sm">
                  Dashboard
              </a>
            @else
              <a href="{{ route('shopper.login') }}" class="btn-primary w-full text-center flex items-center justify-center gap-2 shadow-lg text-sm">
                  Login / Signup
              </a>
            @endauth
        </div>
      </div>
    </header>

    <!-- Sticky Bottom Navigation Bar (Mobile Only) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-gray-200 dark:border-slate-800 py-2 px-3 flex justify-around items-center text-[10px] font-semibold text-gray-600 dark:text-slate-400 shadow-[0_-5px_15px_rgba(0,0,0,0.05)]">
        <a href="/" class="flex flex-col items-center gap-1 hover:text-red-600 transition {{ request()->is('/') ? 'text-red-600 font-bold' : '' }}">
            <span class="text-lg">🏠</span>
            <span>Home</span>
        </a>
        <button @click="mobileMenuOpen = true" class="flex flex-col items-center gap-1 hover:text-red-600 transition">
            <span class="text-lg">🔍</span>
            <span>Search</span>
        </button>
        <a href="/assistant" class="flex flex-col items-center gap-1 text-orange-500 font-bold transition">
            <span class="text-lg">🤖</span>
            <span>AI</span>
        </a>
        <a href="{{ route('directory.brands') }}" class="flex flex-col items-center gap-1 hover:text-red-600 transition {{ request()->routeIs('directory.brands') ? 'text-red-600 font-bold' : '' }}">
            <span class="text-lg">🏷️</span>
            <span>Brands</span>
        </a>
        @auth
        <a href="{{ route('shopper.dashboard') }}" class="flex flex-col items-center gap-1 hover:text-red-600 transition">
            <span class="text-lg">👤</span>
            <span>Account</span>
        </a>
        @else
        <a href="{{ route('shopper.login') }}" class="flex flex-col items-center gap-1 hover:text-red-600 transition">
            <span class="text-lg">👤</span>
            <span>Login</span>
        </a>
        @endauth
    </nav>

    @yield('hero')

    <div class="mx-auto flex max-w-7xl px-4 py-6 md:px-6 gap-6">
        <main class="flex-1 min-w-0 min-h-[calc(100vh-130px)] w-full">
            @yield('content')
        </main>
    </div>

    <footer class="border-t border-red-100 bg-white pt-12 pb-8 text-sm text-gray-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <!-- Branding & About -->
                <div class="md:col-span-2">
                    <a href="/" class="flex items-center mb-4 group">
                        <img src="/images/logo.png" alt="LatestDeal" class="theme-logo h-8 w-auto block dark:hidden group-hover:scale-105 transition-transform" />
                        <img src="/images/logo-white.png" alt="LatestDeal" class="theme-logo h-8 w-auto hidden dark:block group-hover:scale-105 transition-transform" />
                    </a>
                    <p class="text-slate-500 dark:text-slate-400 max-w-sm mb-6 leading-relaxed">
                        Autonomous global deal discovery engine. We scour the web to find the best discounts, offers, and coupons so you never pay full price.
                    </p>
                    <div class="flex space-x-5">
                        <a href="https://t.me/latestdealin" target="_blank" class="text-slate-400 hover:text-red-500 transition-colors">
                            <span class="sr-only">Telegram</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.415-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.254-.241-1.868-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.892-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4 uppercase tracking-wider text-xs">Platform</h3>
                    <ul class="space-y-3">
                        <li><a href="/?sort=discount" class="hover:text-red-500 transition-colors">Today's Deals</a></li>
                        <li><a href="/?tag=trending" class="hover:text-red-500 transition-colors">Trending</a></li>
                        <li><a href="/?category=electronics" class="hover:text-red-500 transition-colors">Categories</a></li>
                        <li><a href="/?merchant=amazon" class="hover:text-red-500 transition-colors">Stores</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4 uppercase tracking-wider text-xs">Legal</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('privacy') }}" class="hover:text-red-500 transition-colors">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-red-500 transition-colors">Terms of Service</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-red-500 transition-colors">Cookie Policy</a></li>
                        <li><a href="mailto:support@latestdeal.in" class="hover:text-red-500 transition-colors">Contact Us</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 border-t border-slate-200 dark:border-slate-800/60 flex flex-col md:flex-row justify-between items-center gap-4">
                <p>&copy; {{ date('Y') }} LatestDeal. All rights reserved.</p>
                <p class="flex items-center gap-1.5 text-xs text-slate-400">
                    Made with <svg class="h-3 w-3 text-red-500 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg> by LatestDeal Team
                </p>
            </div>
        </div>
    </footer>

    @if(View::exists('components.alert-modal'))
        <x-alert-modal />
    @endif
    @if(View::exists('components.cookie-consent'))
        <x-cookie-consent />
    @endif
    
    <!-- Slide-up Theme Switcher -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 z-[60] flex flex-col items-center">
        <!-- Slide-up Panel -->
        <div x-cloak x-show="open" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="translate-y-full opacity-0" 
             x-transition:enter-end="translate-y-0 opacity-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="translate-y-0 opacity-100" 
             x-transition:leave-end="translate-y-full opacity-0" 
             class="w-[22rem] bg-white/95 dark:bg-slate-900/95 p-5 rounded-t-3xl shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.1)] dark:shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.6)] backdrop-blur-xl border border-b-0 border-slate-200 dark:border-slate-700 pb-8"
             @click.away="open = false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">Appearance Settings</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <!-- Dark Mode Options -->
            <div class="grid grid-cols-2 gap-2 mb-6 bg-slate-100 dark:bg-slate-800 p-1.5 rounded-xl">
                <button @click="setDark(false)" class="py-2.5 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2 focus:outline-none" :class="!isDark ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Light
                </button>
                <button @click="setDark(true)" class="py-2.5 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2 focus:outline-none" :class="isDark ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    Dark
                </button>
            </div>

            <!-- Accent Colors -->
            <div class="mb-5">
                <p class="text-[10px] font-semibold text-slate-500 mb-3 uppercase tracking-wider">Accent Theme</p>
                <div class="flex items-center gap-4">
                    <button @click="setColorTheme('red')" style="background-color: #ef4444;" class="w-10 h-10 rounded-full border-[3px] transition-transform hover:scale-110 focus:outline-none" :class="colorTheme === 'red' ? 'border-slate-800 dark:border-white scale-110' : 'border-transparent'"></button>
                    <button @click="setColorTheme('green')" class="w-10 h-10 rounded-full bg-[#1B5E3C] border-[3px] transition-transform hover:scale-110 focus:outline-none" :class="colorTheme === 'green' ? 'border-slate-800 dark:border-white scale-110' : 'border-transparent'"></button>
                </div>
            </div>

            <!-- Eye Comfort View -->
            <div class="flex items-center justify-between pt-5 border-t border-slate-200 dark:border-slate-700/60">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl">
                        <!-- Eye safety icon -->
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-white">Eye Comfort View</p>
                        <p class="text-xs text-slate-500">Reduces blue light</p>
                    </div>
                </div>
                <!-- Power Toggle Button -->
                <button @click="colorTheme === 'amber' ? setColorTheme('red') : setColorTheme('amber')" class="relative flex items-center justify-center h-10 w-10 rounded-full transition-colors shadow-inner focus:outline-none" :class="colorTheme === 'amber' ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500'">
                    <!-- Power Icon -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.36 6.64a9 9 0 1 1-12.73 0M12 2v10" /></svg>
                </button>
            </div>
        </div>
        
        <!-- Toggle Arrow Button (Hidden when panel is open) -->
        <button @click="open = true" x-show="!open" x-transition.opacity.delay.200ms class="bg-white/90 dark:bg-slate-800/90 shadow-[0_-2px_10px_rgba(0,0,0,0.05)] border border-b-0 border-slate-200 dark:border-slate-700 rounded-t-xl px-5 py-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors backdrop-blur focus:outline-none mb-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
        </button>
    </div>
    
    <!-- Scroll to Top Button -->
    <button 
        x-data="{ show: false }"
        x-init="window.addEventListener('scroll', () => { show = window.scrollY > 500 })"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-10"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-10"
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-6 right-6 z-50 p-3.5 bg-red-600 hover:bg-red-700 text-white rounded-full shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all focus:outline-none focus:ring-4 focus:ring-red-300"
        aria-label="Scroll to top"
        style="display: none;"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
    </button>
    
    <!-- Eye Comfort Overlay -->
    <div x-show="colorTheme === 'amber'" class="fixed inset-0 z-[99999] pointer-events-none bg-[#451a03]/5 backdrop-brightness-95 backdrop-contrast-90" x-transition.opacity></div>
    
    @stack('scripts')
</body>
</html>

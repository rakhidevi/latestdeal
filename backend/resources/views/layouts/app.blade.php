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
    @endif

    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Organization",
      "name": "LatestDeal",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('/images/logo.png') }}",
      "sameAs": [
        "https://t.me/latestdealin"
      ]
    }
    </script>

    @if(config('services.google.adsense_id'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.google.adsense_id') }}" crossorigin="anonymous"></script>
    @endif

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

    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3274200073613804"
     crossorigin="anonymous"></script>
</head>
<body x-data="themeSwitcher" class="antialiased">
    
    <header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-40 border-b border-red-100 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/85">
      <div class="mx-auto flex max-w-7xl px-4 sm:px-6 lg:px-8 py-3 items-center justify-between relative">
        
        <!-- Left Side: Logo -->
        <a href="/" class="flex items-center justify-start flex-shrink-0 z-50 relative">
          <img src="/images/logo.png" alt="LatestDeal" class="theme-logo h-8 md:h-10 w-auto block dark:hidden" />
          <img src="/images/logo-white.png" alt="LatestDeal" class="theme-logo h-8 md:h-10 w-auto hidden dark:block" />
        </a>

        <!-- Center: Desktop Mega Menu -->
        <div class="hidden lg:flex absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2 items-center gap-8 xl:gap-10 text-[14px] font-medium text-gray-600 dark:text-slate-300 z-30">
            <a href="/?sort=discount" class="transition hover:text-red-600 dark:hover:text-red-400 flex items-center gap-1.5 group whitespace-nowrap">
                <svg class="w-[13.5px] h-[13.5px] opacity-70 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" /></svg>
                Top Deals
            </a>
            
            <!-- Stores Dropdown -->
            <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative group py-2">
                <button class="transition hover:text-red-600 dark:hover:text-red-400 flex items-center gap-1.5 focus:outline-none group whitespace-nowrap">
                    <svg class="w-[13.5px] h-[13.5px] opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 6h18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 10a4 4 0 0 1-8 0"/></svg>
                    Stores 
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-transition.opacity.scale.origin.top class="absolute left-0 mt-4 w-48 rounded-xl bg-white/95 dark:bg-slate-900/95 backdrop-blur shadow-xl border border-gray-100 dark:border-slate-800 py-2 z-50">
                    <a href="/?merchant=amazon" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">🛒</span> Amazon
                    </a>
                    <a href="/?merchant=udemy" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">🎓</span> Udemy
                    </a>
                    <span class="flex items-center gap-3 px-4 py-2 text-gray-400 dark:text-slate-500 cursor-not-allowed">
                        <span class="text-lg opacity-50">🛍️</span> Flipkart (Soon)
                    </span>
                </div>
            </div>

            <!-- Brands Dropdown -->
            <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative group py-2">
                <button class="transition hover:text-red-600 dark:hover:text-red-400 flex items-center gap-1.5 focus:outline-none group whitespace-nowrap">
                    <svg class="w-[13.5px] h-[13.5px] opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 7h.01"/></svg>
                    Brands 
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-transition.opacity.scale.origin.top class="absolute left-0 mt-4 w-48 rounded-xl bg-white/95 dark:bg-slate-900/95 backdrop-blur shadow-xl border border-gray-100 dark:border-slate-800 py-2 z-50">
                    <a href="/?brand=apple" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">🍎</span> Apple
                    </a>
                    <a href="/?brand=samsung" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">📱</span> Samsung
                    </a>
                    <a href="/?brand=sony" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">🎮</span> Sony
                    </a>
                    <a href="/?brand=nike" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">👟</span> Nike
                    </a>
                    <a href="/?brand=puma" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">🐆</span> Puma
                    </a>
                </div>
            </div>

            <!-- Categories Dropdown -->
            <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative group py-2">
                <button class="transition hover:text-red-600 dark:hover:text-red-400 flex items-center gap-1.5 focus:outline-none group whitespace-nowrap">
                    <svg class="w-[13.5px] h-[13.5px] opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="7" height="7" x="3" y="3" rx="1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><rect width="7" height="7" x="14" y="3" rx="1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><rect width="7" height="7" x="14" y="14" rx="1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><rect width="7" height="7" x="3" y="14" rx="1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Categories 
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-transition.opacity.scale.origin.top class="absolute left-0 mt-4 w-56 rounded-xl bg-white/95 dark:bg-slate-900/95 backdrop-blur shadow-xl border border-gray-100 dark:border-slate-800 py-2 z-50">
                    <a href="/?category=electronics" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">💻</span> Electronics
                    </a>
                    <a href="/?category=fashion" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">👗</span> Fashion
                    </a>
                    <a href="/?category=home-kitchen" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">🍳</span> Home & Kitchen
                    </a>
                    <a href="/?category=programming" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">👨‍💻</span> Programming
                    </a>
                    <a href="/?category=ai" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                        <span class="text-lg">🤖</span> AI
                    </a>
                </div>
            </div>

            <!-- Quality Score Dropdown -->
            <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative group py-2">
                <button class="transition hover:text-red-600 dark:hover:text-red-400 flex items-center gap-1.5 focus:outline-none group whitespace-nowrap">
                    <svg class="w-[13.5px] h-[13.5px] opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Quality 
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-transition.opacity.scale.origin.top class="absolute left-0 mt-4 w-56 rounded-xl bg-white/95 dark:bg-slate-900/95 backdrop-blur shadow-xl border border-gray-100 dark:border-slate-800 py-2 z-50">
                    <a href="/?min_trust_score=90" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-yellow-500 transition">
                        <span class="w-3 h-3 rounded-full bg-yellow-400 shadow-[0_0_8px_rgba(250,204,21,0.8)]"></span> 90+ Score (Gold)
                    </a>
                    <a href="/?min_trust_score=80" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-slate-400 transition">
                        <span class="w-3 h-3 rounded-full bg-slate-300 shadow-[0_0_8px_rgba(203,213,225,0.8)]"></span> 80+ Score (Silver)
                    </a>
                    <a href="/?min_trust_score=75" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-orange-600 transition">
                        <span class="w-3 h-3 rounded-full bg-orange-500 shadow-[0_0_8px_rgba(249,115,22,0.8)]"></span> 75+ Score (Bronze)
                    </a>
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

        <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 -mr-2 text-gray-600 dark:text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
      </div>

      <!-- Mobile Menu Dropdown -->
      <div x-show="mobileMenuOpen" x-transition class="lg:hidden border-t border-gray-100 dark:border-slate-800 px-4 py-4 space-y-3 bg-white dark:bg-slate-900 shadow-lg absolute w-full z-50">
        <a href="/?sort=discount" class="flex items-center gap-2 font-medium text-gray-800 dark:text-gray-200 hover:text-red-600 group whitespace-nowrap">
            <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" /></svg>
            Top Deals
        </a>
        
        <!-- Stores Accordion -->
        <div x-data="{ open: false }" class="border-b border-gray-50 dark:border-slate-800/50 pb-2">
            <button @click="open = !open" class="flex items-center justify-between w-full font-medium text-gray-800 dark:text-gray-200 hover:text-red-600 focus:outline-none group whitespace-nowrap">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 6h18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 10a4 4 0 0 1-8 0"/></svg>
                    By Store
                </span>
                <svg class="w-5 h-5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-transition class="pl-6 pt-2 space-y-3 pb-2">
                <a href="/?merchant=amazon" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">🛒</span> Amazon
                </a>
                <a href="/?merchant=udemy" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">🎓</span> Udemy
                </a>
                <span class="flex items-center gap-3 text-gray-400 dark:text-slate-600">
                    <span class="text-lg opacity-50">🛍️</span> Flipkart (Soon)
                </span>
            </div>
        </div>

        <!-- Brands Accordion -->
        <div x-data="{ open: false }" class="border-b border-gray-50 dark:border-slate-800/50 pb-2">
            <button @click="open = !open" class="flex items-center justify-between w-full font-medium text-gray-800 dark:text-gray-200 hover:text-red-600 focus:outline-none group whitespace-nowrap">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 7h.01"/></svg>
                    By Brand
                </span>
                <svg class="w-5 h-5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-transition class="pl-6 pt-2 space-y-3 pb-2">
                <a href="/?brand=apple" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">🍎</span> Apple
                </a>
                <a href="/?brand=samsung" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">📱</span> Samsung
                </a>
                <a href="/?brand=sony" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">🎮</span> Sony
                </a>
                <a href="/?brand=nike" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">👟</span> Nike
                </a>
                <a href="/?brand=puma" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">🐆</span> Puma
                </a>
            </div>
        </div>

        <!-- Categories Accordion -->
        <div x-data="{ open: false }" class="border-b border-gray-50 dark:border-slate-800/50 pb-2">
            <button @click="open = !open" class="flex items-center justify-between w-full font-medium text-gray-800 dark:text-gray-200 hover:text-red-600 focus:outline-none group whitespace-nowrap">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="7" height="7" x="3" y="3" rx="1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><rect width="7" height="7" x="14" y="3" rx="1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><rect width="7" height="7" x="14" y="14" rx="1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><rect width="7" height="7" x="3" y="14" rx="1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Categories
                </span>
                <svg class="w-5 h-5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-transition class="pl-6 pt-2 space-y-3 pb-2">
                <a href="/?category=electronics" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">💻</span> Electronics
                </a>
                <a href="/?category=fashion" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">👗</span> Fashion
                </a>
                <a href="/?category=home-kitchen" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">🍳</span> Home & Kitchen
                </a>
                <a href="/?category=programming" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">👨‍💻</span> Programming
                </a>
                <a href="/?category=ai" class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                    <span class="text-lg">🤖</span> AI
                </a>
            </div>
        </div>

        <!-- Quality Score Accordion -->
        <div x-data="{ open: false }" class="border-b border-gray-50 dark:border-slate-800/50 pb-2">
            <button @click="open = !open" class="flex items-center justify-between w-full font-medium text-gray-800 dark:text-gray-200 hover:text-red-600 focus:outline-none group whitespace-nowrap">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Quality
                </span>
                <svg class="w-5 h-5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-transition class="pl-6 pt-2 space-y-3 pb-2">
                <a href="/?min_trust_score=90" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                    <span class="w-3 h-3 rounded-full bg-yellow-400 shadow-[0_0_8px_rgba(250,204,21,0.8)]"></span> 90+ Score (Gold)
                </a>
                <a href="/?min_trust_score=80" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                    <span class="w-3 h-3 rounded-full bg-slate-300 shadow-[0_0_8px_rgba(203,213,225,0.8)]"></span> 80+ Score (Silver)
                </a>
                <a href="/?min_trust_score=75" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                    <span class="w-3 h-3 rounded-full bg-orange-500 shadow-[0_0_8px_rgba(249,115,22,0.8)]"></span> 75+ Score (Bronze)
                </a>
            </div>
        </div>

        <div class="border-t border-gray-100 dark:border-slate-800 pt-4 pb-2 space-y-3">
            <a href="/assistant" class="flex items-center gap-2 font-medium text-orange-500 dark:text-orange-400 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 3v4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 5h4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 17v4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 19h4"/></svg>
                AI Assistant
            </a>
            @auth
              <a href="{{ route('shopper.dashboard') }}" class="flex items-center gap-2 font-medium text-red-600 dark:text-red-400 whitespace-nowrap">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                  My Dashboard
              </a>
            @else
              <a href="{{ route('shopper.login') }}" class="btn-primary w-full text-center flex items-center justify-center gap-2 shadow-lg whitespace-nowrap">
                  <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4" stroke-width="2"/></svg>
                  Login / Signup
              </a>
            @endauth
        </div>
      </div>
    </header>

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
        <div x-show="open" 
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

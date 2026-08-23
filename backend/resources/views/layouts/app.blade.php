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

    <!-- Lottie Web Animation Engine -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>

    <!-- Instant Preloader & Core Theme Styles -->
    <style>
        body:not(.loaded) {
            overflow: hidden !important;
        }
        #page-preloader {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background-color: #ffffff !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            z-index: 9999999 !important;
            opacity: 1 !important;
            visibility: visible !important;
            transition: opacity 0.35s ease, visibility 0.35s ease !important;
        }
        html.dark #page-preloader {
            background-color: #020617 !important;
        }
        #page-preloader.preloader-hidden,
        #page-preloader.preloader-hidden * {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            display: none !important;
        }
        .preloader-content {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }
        .preloader-logo-wrap {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: preloaderPulse 1.8s ease-in-out infinite;
        }
        .preloader-brand-logo {
            height: 54px !important;
            max-height: 54px !important;
            width: auto !important;
            max-width: 280px !important;
            object-fit: contain !important;
        }
        html.dark .preloader-logo-dark { display: block !important; }
        html.dark .preloader-logo-light { display: none !important; }
        html:not(.dark) .preloader-logo-dark { display: none !important; }
        html:not(.dark) .preloader-logo-light { display: block !important; }

        @keyframes preloaderPulse {
            0%, 100% { transform: scale(0.96); opacity: 0.88; }
            50% { transform: scale(1.05); opacity: 1; filter: drop-shadow(0 0 18px rgba(239, 68, 68, 0.35)); }
        }

        .preloader-progress-track {
            margin-top: 14px;
            width: 140px;
            height: 4px;
            background: rgba(239, 68, 68, 0.15);
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }
        .preloader-progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 45%;
            background: linear-gradient(90deg, #ef4444, #f87171, #ef4444);
            border-radius: 999px;
            animation: preloaderSlide 1.4s cubic-bezier(0.65, 0, 0.35, 1) infinite;
        }
        .preloader-text {
            margin-top: 10px;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        html.dark .preloader-text {
            color: #94a3b8;
        }

        @keyframes preloaderSlide {
            0% { left: -45%; }
            100% { left: 100%; }
        }
    </style>
    <script>
        // Instant theme application to prevent FOUC & dark mode white flashes
        (function() {
            try {
                const storedDark = localStorage.getItem("adh-dark");
                const storedTheme = localStorage.getItem("adh-color") || 'red';
                document.documentElement.setAttribute('data-theme', storedTheme);
                let isDark = false;
                if (storedDark) {
                    isDark = storedDark === "dark";
                } else {
                    const hour = new Date().getHours();
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    isDark = (hour >= 20 || hour < 7) || prefersDark;
                }
                if (isDark) document.documentElement.classList.add("dark");
                else document.documentElement.classList.remove("dark");
            } catch(e) {}
        })();
    </script>

    <script type="application/ld+json">
    [
      {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "LatestDeal",
        "url": @json(url('/')),
        "logo": @json(asset('/images/logo.png')),
        "sameAs": [
          "https://t.me/latestdealin"
        ]
      },
      {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "url": @json(url('/')),
        "potentialAction": {
          "@@type": "SearchAction",
          "target": {
            "@@type": "EntryPoint",
            "urlTemplate": "@json(url('/'))?search={search_term_string}"
          },
          "query-input": "required name=search_term_string"
        }
      }
    ]
    </script>

    @if(config('services.google.adsense_id'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.google.adsense_id') }}" crossorigin="anonymous"></script>
    @endif

    <!-- Enterprise Native W3C Web Push Engine -->
    <script>
        window.VAPID_PUBLIC_KEY = "{{ config('services.vapid.public_key') }}";

        if ('serviceWorker' in navigator && 'PushManager' in window) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('LatestDeal ServiceWorker registered:', registration.scope);
                }).catch(function(err) {
                    console.error('ServiceWorker registration failed:', err);
                });
            });
        }

        async function subscribeToNativeWebPush() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                alert('Web Push Notifications are not supported by your browser.');
                return null;
            }
            try {
                const registration = await navigator.serviceWorker.ready;
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    alert('Notification permission was denied.');
                    return null;
                }
                const convertedVapidKey = urlBase64ToUint8Array(window.VAPID_PUBLIC_KEY);
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                });

                // Send PushSubscription to self-hosted API endpoint
                await fetch('/api/subscribe', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ push_subscription: subscription })
                });

                return subscription;
            } catch (e) {
                console.error('Failed to subscribe to Web Push:', e);
                return null;
            }
        }

        function urlBase64ToUint8Array(base64String) {
            if (!base64String) return new Uint8Array();
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    </script>

    <!-- User Intelligence Center (UIC) Tracker -->
    <script src="{{ asset('js/uic-tracker.js') }}" defer></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    @php
        $defaultTheme = 'red';
        $defaultColorMode = 'auto';

        try {
            if (class_exists('App\\Models\\Setting') && method_exists('\Illuminate\\Support\\Facades\\Schema', 'hasTable')) {
                if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    $defaultTheme = \App\Models\Setting::where('key', 'default_theme')->value('value') ?? 'red';
                    $defaultColorMode = \App\Models\Setting::where('key', 'default_color_mode')->value('value') ?? 'auto';
                }
            }
        } catch (\Throwable $e) {
            $defaultTheme = 'red';
            $defaultColorMode = 'auto';
        }
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
            .section-title { @apply text-2xl font-black tracking-tight text-gray-900 ; }
            .section-subtitle { @apply mt-1 text-sm text-gray-600 ; }
            .panel { @apply rounded-2xl border border-red-100 bg-white/90 p-4 shadow-sm backdrop-blur  ; }
            .surface { @apply rounded-2xl border border-red-100 bg-white p-5 shadow-sm  ; }
            
            /* Accessibility tweaks */
            .btn-primary { @apply rounded-xl bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600 hover:text-white; }
            .btn-secondary { @apply rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50    :bg-slate-800; }
            .input-base { @apply w-full rounded-xl border border-red-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-red-400    :text-slate-500; }
            
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
    
    <!-- Fullscreen Animated Page Loader -->
    <div id="page-preloader" aria-label="Loading page">
        <div class="preloader-content">
            <!-- Official Brand Logo -->
            <div class="preloader-logo-wrap">
                <img src="{{ asset('/images/logo.png') }}" alt="LatestDeal" class="preloader-brand-logo preloader-logo-light" style="height: 54px !important; max-height: 54px !important; width: auto !important; max-width: 280px !important; object-fit: contain !important; display: block;" />
                <img src="{{ asset('/images/logo-white.png') }}" alt="LatestDeal" class="preloader-brand-logo preloader-logo-dark" style="height: 54px !important; max-height: 54px !important; width: auto !important; max-width: 280px !important; object-fit: contain !important; display: none;" />
            </div>
            
            <div class="preloader-progress-track">
                <div class="preloader-progress-bar"></div>
            </div>
            
            <div class="preloader-text">Discovering Best Deals...</div>
        </div>
    </div>
    
    @include('partials.header')

    <!-- Sticky Bottom Navigation Bar (Mobile Only) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95  backdrop-blur-md border-t border-gray-200  py-2 px-3 flex justify-around items-center text-[10px] font-semibold text-gray-600  shadow-[0_-5px_15px_rgba(0,0,0,0.05)]">
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
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-1 hover:text-indigo-600 transition text-indigo-600">
                <span class="text-lg">⚙️</span>
                <span>Admin</span>
            </a>
            @else
            <a href="{{ route('shopper.dashboard') }}" class="flex flex-col items-center gap-1 hover:text-red-600 transition">
                <span class="text-lg">👤</span>
                <span>Account</span>
            </a>
            @endif
        @else
        <a href="{{ route('shopper.login') }}" class="flex flex-col items-center gap-1 hover:text-red-600 transition">
            <span class="text-lg">👤</span>
            <span>Login</span>
        </a>
        @endauth
    </nav>

    @yield('hero')

    <div class="mx-auto flex max-w-7xl px-4 py-4 md:px-6 gap-6">
        <main class="flex-1 min-w-0 min-h-[calc(100vh-130px)] w-full">
            @yield('content')
        </main>
    </div>

    <footer class="border-t border-red-100 bg-white pt-12 pb-8 text-sm text-gray-500    mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <!-- Branding & About -->
                <div class="md:col-span-2">
                    <a href="/" class="flex items-center mb-4 group">
                        <img src="{{ asset('/images/logo.png') }}" alt="LatestDeal" class="h-8 group-hover:opacity-80 transition">
                    </a>
                    <p class="text-gray-500 mb-6 max-w-sm">
                        An intelligent deal discovery engine that finds, verifies and explains the best discounts, offers and coupons.
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
                    <h3 class="font-semibold text-slate-900  mb-4 uppercase tracking-wider text-xs">Platform</h3>
                    <ul class="space-y-3">
                        <li><a href="/?sort=discount" class="hover:text-red-500 transition-colors">Today's Deals</a></li>
                        <li><a href="/?category=electronics" class="hover:text-red-500 transition-colors">Categories</a></li>
                        <li><a href="/?merchant=amazon" class="hover:text-red-500 transition-colors">Stores</a></li>
                        <li><a href="{{ route('articles.index') }}" class="hover:text-red-500 transition-colors">Guides & Blog</a></li>
                        <li><a href="{{ route('editorial.team') }}" class="hover:text-red-500 transition-colors">Editorial Team</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-red-500 transition-colors">About Us</a></li>
                        <li><a href="{{ route('how.it.works') }}" class="hover:text-red-500 transition-colors">How It Works</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="font-semibold text-slate-900  mb-4 uppercase tracking-wider text-xs">Legal & Trust</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('privacy') }}" class="hover:text-red-500 transition-colors">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-red-500 transition-colors">Terms of Service</a></li>
                        <li><a href="{{ route('cookie') }}" class="hover:text-red-500 transition-colors">Cookie Policy</a></li>
                        <li><a href="{{ route('editorial.policy') }}" class="hover:text-red-500 transition-colors">Editorial Policy</a></li>
                        <li><a href="{{ route('affiliate.disclosure') }}" class="hover:text-red-500 transition-colors">Affiliate Disclosure</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-red-500 transition-colors">Contact Us</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 border-t border-slate-200  flex flex-col md:flex-row justify-between items-center gap-4">
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
             class="w-[22rem] bg-white/95  p-5 rounded-t-3xl shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.1)] -[0_-10px_40px_-10px_rgba(0,0,0,0.6)] backdrop-blur-xl border border-b-0 border-slate-200  pb-8"
             @click.away="open = false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-800  uppercase tracking-wider">Appearance Settings</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600 :text-white focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <!-- Dark Mode Options -->
            <div class="grid grid-cols-2 gap-2 mb-6 bg-slate-100  p-1.5 rounded-xl">
                <button @click="setDark(false)" class="py-2.5 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2 focus:outline-none" :class="!isDark ? 'bg-white  text-slate-900  shadow-sm' : 'text-slate-500 hover:text-slate-700 '">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Light
                </button>
                <button @click="setDark(true)" class="py-2.5 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2 focus:outline-none" :class="isDark ? 'bg-white  text-slate-900  shadow-sm' : 'text-slate-500 hover:text-slate-700 '">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    Dark
                </button>
            </div>

            <!-- Accent Colors -->
            <div class="mb-5">
                <p class="text-[10px] font-semibold text-slate-500 mb-3 uppercase tracking-wider">Accent Theme</p>
                <div class="flex items-center gap-4">
                    <button @click="setColorTheme('red')" style="background-color: #ef4444;" class="w-10 h-10 rounded-full border-[3px] transition-transform hover:scale-110 focus:outline-none" :class="colorTheme === 'red' ? 'border-slate-800  scale-110' : 'border-transparent'"></button>
                    <button @click="setColorTheme('green')" class="w-10 h-10 rounded-full bg-[#1B5E3C] border-[3px] transition-transform hover:scale-110 focus:outline-none" :class="colorTheme === 'green' ? 'border-slate-800  scale-110' : 'border-transparent'"></button>
                </div>
            </div>

            <!-- Eye Comfort View -->
            <div class="flex items-center justify-between pt-5 border-t border-slate-200 ">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-amber-100  text-amber-600  rounded-xl">
                        <!-- Eye safety icon -->
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800 ">Eye Comfort View</p>
                        <p class="text-xs text-slate-500">Reduces blue light</p>
                    </div>
                </div>
                <!-- Power Toggle Button -->
                <button @click="colorTheme === 'amber' ? setColorTheme('red') : setColorTheme('amber')" class="relative flex items-center justify-center h-10 w-10 rounded-full transition-colors shadow-inner focus:outline-none" :class="colorTheme === 'amber' ? 'bg-amber-500 text-white' : 'bg-slate-100  text-slate-400 '">
                    <!-- Power Icon -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.36 6.64a9 9 0 1 1-12.73 0M12 2v10" /></svg>
                </button>
            </div>
        </div>
        
        <!-- Toggle Arrow Button (Hidden when panel is open) -->
        <button @click="open = true" x-show="!open" x-transition.opacity.delay.200ms class="bg-white/90  shadow-[0_-2px_10px_rgba(0,0,0,0.05)] border border-b-0 border-slate-200  rounded-t-xl px-5 py-1.5 text-slate-400 hover:text-slate-600 :text-white transition-colors backdrop-blur focus:outline-none mb-0">
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

    <script>
        (function() {
            // 1. Initialize Lottie Storyteller Animation
            try {
                if (typeof lottie !== 'undefined') {
                    const lottieContainer = document.getElementById('lottie-logo-container');
                    if (lottieContainer) {
                        lottie.loadAnimation({
                            container: lottieContainer,
                            renderer: 'svg',
                            loop: true,
                            autoplay: true,
                            path: '{{ asset("animations/latestdeal-logo.json") }}'
                        });
                    }
                }
            } catch(err) {}

            // 2. Storytelling text stage ticker
            const storyTexts = [
                '🔥 Discovering Verified Deals...',
                '🏷️ Unlocking 90% OFF Discounts...',
                '🛍️ Filling Shopping Bag...',
                '🎁 Opening Jackpot Savings...'
            ];
            let textIdx = 0;
            const textEl = document.getElementById('preloader-story-text');
            const storyTimer = setInterval(function() {
                if (textEl) {
                    textIdx = (textIdx + 1) % storyTexts.length;
                    textEl.innerText = storyTexts[textIdx];
                }
            }, 850);

            // 3. Complete loading dismissal
            function completeLoading() {
                clearInterval(storyTimer);
                const preloader = document.getElementById('page-preloader');
                if (preloader) {
                    preloader.classList.add('preloader-hidden');
                    document.body.classList.add('loaded');
                    setTimeout(function() {
                        if (preloader && preloader.parentNode) {
                            preloader.parentNode.removeChild(preloader);
                        }
                    }, 400);
                }
            }

            if (document.readyState === 'complete') {
                completeLoading();
            } else {
                window.addEventListener('load', completeLoading);
                // Absolute safety fallback: 3s max
                setTimeout(completeLoading, 3000);
            }
        })();
    </script>
</body>
</html>

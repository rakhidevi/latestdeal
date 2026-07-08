<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="admaven-placement" content="BqTkGpdr9">
    
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

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            :root { color-scheme: light; }
            html.dark { color-scheme: dark; }

            body {
                @apply min-h-screen bg-slate-50 text-gray-900 transition-colors duration-300;
                background-image:
                    radial-gradient(circle at 0% 0%, rgba(239, 68, 68, 0.18), transparent 35%),
                    radial-gradient(circle at 100% 0%, rgba(244, 63, 94, 0.14), transparent 30%),
                    linear-gradient(to bottom, #fffaf5, #f8fafc 35%);
            }
            html.dark body {
                @apply bg-slate-950 text-slate-100;
                background-image:
                    radial-gradient(circle at 0% 0%, rgba(239, 68, 68, 0.12), transparent 28%),
                    radial-gradient(circle at 100% 0%, rgba(251, 113, 133, 0.08), transparent 24%),
                    linear-gradient(to bottom, #0f172a, #020617 50%);
            }
            .section-title { @apply text-2xl font-black tracking-tight text-gray-900 dark:text-slate-100; }
            .section-subtitle { @apply mt-1 text-sm text-gray-600 dark:text-slate-400; }
            .panel { @apply rounded-2xl border border-red-100/80 bg-white/90 p-4 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/80; }
            .surface { @apply rounded-2xl border border-red-100 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900; }
            .btn-primary { @apply rounded-xl bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600; }
            .btn-secondary { @apply rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800; }
            .input-base { @apply w-full rounded-xl border border-red-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-red-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500; }
        }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('themeSwitcher', () => ({
                isDark: false,
                init() {
                    const stored = localStorage.getItem("adh-theme");
                    this.isDark = stored === "dark";
                    if(this.isDark) document.documentElement.classList.add("dark");
                },
                toggleDark() {
                    this.isDark = !this.isDark;
                    document.documentElement.classList.toggle("dark", this.isDark);
                    localStorage.setItem("adh-theme", this.isDark ? "dark" : "light");
                }
            }))
        })
    </script>

    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3274200073613804"
     crossorigin="anonymous"></script>
</head>
<body x-data="themeSwitcher" class="antialiased">
    
    <header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-40 border-b border-red-100/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/85">
      <div class="mx-auto grid max-w-7xl gap-3 px-4 sm:px-6 lg:px-8 py-3 grid-cols-[auto_1fr_auto] lg:grid-cols-[220px_1fr_auto] items-center">
        <a href="/" class="flex items-center justify-start">
          <img src="/images/logo.png" alt="LatestDeal" class="h-8 md:h-10 w-auto block dark:hidden" />
          <img src="/images/logo-white.png" alt="LatestDeal" class="h-8 md:h-10 w-auto hidden dark:block" />
        </a>

        <div class="flex-1 w-full max-w-xl mx-auto lg:mx-0">
          <form action="/" method="GET" class="flex w-full">
            <input name="q" value="{{ request('q') }}" placeholder="Search deals..." class="input-base w-full text-sm py-1.5 md:py-2" />
          </form>
        </div>

        <div class="hidden lg:flex items-center justify-end gap-2">
          <button @click="toggleDark()" class="btn-secondary px-3" type="button" x-text="isDark ? 'Light' : 'Dark'"></button>
          
          @auth
            <a href="{{ route('shopper.dashboard') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-red-500 mx-2">Dashboard</a>
          @else
            <a href="{{ route('shopper.login') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-red-500 mx-2">Login</a>
          @endauth

          <a href="/?category=all" class="btn-primary">Browse Deals</a>
        </div>

        <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 -mr-2 text-gray-600 dark:text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
      </div>

      <!-- Mobile Menu Dropdown -->
      <div x-show="mobileMenuOpen" x-transition class="lg:hidden border-t border-gray-100 dark:border-slate-800 px-4 py-4 space-y-3 bg-white dark:bg-slate-900 shadow-lg absolute w-full">
        <a href="/" class="block font-medium text-gray-800 dark:text-gray-200">Home</a>
        <a href="/?sort=discount" class="block font-medium text-gray-800 dark:text-gray-200">Top Discounts</a>
        <a href="/?merchant=amazon" class="block font-medium text-gray-800 dark:text-gray-200">Amazon Deals</a>
        
        @auth
          <a href="{{ route('shopper.dashboard') }}" class="block font-medium text-red-600 dark:text-red-400">My Dashboard</a>
        @else
          <a href="{{ route('shopper.login') }}" class="block font-medium text-gray-800 dark:text-gray-200">Login / Sign up</a>
        @endauth
        
        <div class="border-t border-gray-100 dark:border-slate-800 pt-3 flex flex-wrap gap-2">
            <button @click="toggleDark()" class="btn-secondary px-3 w-full sm:w-auto" type="button" x-text="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"></button>
            <a href="/?category=all" class="btn-primary text-center w-full sm:w-auto">Browse Deals</a>
        </div>
      </div>

      <div class="mx-auto hidden lg:flex max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8 pb-3 text-sm font-medium text-gray-600 dark:text-slate-300">
        <a href="/" class="transition hover:text-red-600 dark:hover:text-red-400">Home</a>
        <a href="/" class="transition hover:text-red-600 dark:hover:text-red-400">Today Deals</a>
        <a href="/?sort=discount" class="transition hover:text-red-600 dark:hover:text-red-400">Top Discounts</a>
        <a href="/?merchant=amazon" class="transition hover:text-red-600 dark:hover:text-red-400">Amazon Deals</a>
        <a href="/?category=electronics" class="transition hover:text-red-600 dark:hover:text-red-400">Electronics</a>
        <a href="/?category=fashion" class="transition hover:text-red-600 dark:hover:text-red-400">Fashion</a>
      </div>
    </header>

    <div class="mx-auto flex max-w-[96rem] px-4 py-6 md:px-6 gap-6">
        <!-- Left Sidebar Ad -->
        <aside class="hidden xl:block w-[160px] flex-shrink-0">
            <div class="sticky top-24">
                <x-ad-banner format="vertical" slot="sidebar-left" />
            </div>
        </aside>

        <main class="flex-1 min-w-0 min-h-[calc(100vh-130px)] max-w-7xl mx-auto w-full">
            @yield('content')
        </main>

        <!-- Right Sidebar Ad -->
        <aside class="hidden xl:block w-[160px] flex-shrink-0">
            <div class="sticky top-24">
                <x-ad-banner format="vertical" slot="sidebar-right" />
            </div>
        </aside>
    </div>

    <footer class="border-t border-red-100 bg-white pt-12 pb-8 text-sm text-gray-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <!-- Branding & About -->
                <div class="md:col-span-2">
                    <a href="/" class="flex items-center mb-4 group">
                        <img src="/images/logo.png" alt="LatestDeal" class="h-8 w-auto block dark:hidden group-hover:scale-105 transition-transform" />
                        <img src="/images/logo-white.png" alt="LatestDeal" class="h-8 w-auto hidden dark:block group-hover:scale-105 transition-transform" />
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
</body>
</html>

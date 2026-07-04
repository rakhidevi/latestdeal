<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LatestDeal - Curated Discounts & Offers</title>
    
    <!-- SEO Meta Tags dynamically injected -->
    @yield('meta')

    <!-- Typography: Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN for local dev, Vite in Prod) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#2563EB',
                        accent: '#DC2626',
                        background: '#F3F4F6'
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js for Modals and interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- OneSignal Push Notifications -->
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
      window.OneSignalDeferred = window.OneSignalDeferred || [];
      OneSignalDeferred.push(function(OneSignal) {
        OneSignal.init({
          appId: "YOUR-ONESIGNAL-APP-ID",
        });
      });
    </script>
</head>
<body class="bg-background text-gray-900 font-sans antialiased">
    
    <!-- Navigation Menu (Glassmorphism) -->
    <nav x-data="{ mobileMenuOpen: false }" class="bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-red-400 tracking-tight flex items-center gap-2">
                        <svg class="w-7 h-7 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.559-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"></path></svg>
                        LatestDeal
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-sm font-semibold text-gray-700 hover:text-red-600 transition">Today's Deals</a>
                    <a href="/?category=electronics" class="text-sm font-semibold text-gray-700 hover:text-red-600 transition">Electronics</a>
                    <a href="/?category=fashion" class="text-sm font-semibold text-gray-700 hover:text-red-600 transition">Fashion</a>
                    <a href="/?brand=apple" class="text-sm font-semibold text-gray-700 hover:text-red-600 transition">Apple</a>
                    
                    <!-- Trigger Alpine modal for Price Alerts -->
                    <button x-data @click="$dispatch('open-alert-modal')" class="bg-gray-100 hover:bg-red-50 text-gray-700 hover:text-red-600 px-4 py-2 rounded-full text-sm font-bold transition flex items-center gap-2 border border-transparent hover:border-red-200">
                        🔔 Price Alert
                    </button>
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-6 w-6" :class="{'hidden': mobileMenuOpen, 'block': !mobileMenuOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="h-6 w-6" :class="{'block': mobileMenuOpen, 'hidden': !mobileMenuOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu (Alpine.js) -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden border-t border-gray-100 bg-white">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="/" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-red-600 hover:bg-gray-50">Today's Deals</a>
                <a href="/?category=electronics" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-red-600 hover:bg-gray-50">Electronics</a>
                <a href="/?category=fashion" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-red-600 hover:bg-gray-50">Fashion</a>
                <button @click="$dispatch('open-alert-modal'); mobileMenuOpen = false" class="w-full text-left block px-3 py-2 rounded-md text-base font-medium text-red-600 bg-red-50">
                    🔔 Set Price Alert
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content Slot -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12 py-8 text-center text-gray-500 text-sm">
        <p>&copy; {{ date('Y') }} LatestDeal. All rights reserved.</p>
        <p class="mt-2 text-xs">As an Amazon Associate I earn from qualifying purchases.</p>
    </footer>

    <!-- Global Alert Modal Component -->
    <x-alert-modal />

    <!-- GDPR Cookie Consent -->
    <x-cookie-consent />

</body>
</html>

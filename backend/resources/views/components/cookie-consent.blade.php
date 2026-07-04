<div x-data="{ showCookieBanner: !localStorage.getItem('cookieConsent') }"
     x-show="showCookieBanner"
     class="fixed bottom-0 inset-x-0 pb-2 sm:pb-5 z-50">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
        <div class="p-2 rounded-lg bg-gray-900 shadow-lg sm:p-3">
            <div class="flex items-center justify-between flex-wrap">
                <div class="w-0 flex-1 flex items-center">
                    <span class="flex p-2 rounded-lg bg-gray-800">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <p class="ml-3 font-medium text-white truncate">
                        <span class="md:hidden"> We use cookies! </span>
                        <span class="hidden md:inline"> We use cookies to analyze traffic, track affiliate clicks, and provide a better experience. By continuing, you agree to our privacy policy. </span>
                    </p>
                </div>
                <div class="order-3 mt-2 flex-shrink-0 w-full sm:order-2 sm:mt-0 sm:w-auto">
                    <button @click="localStorage.setItem('cookieConsent', 'true'); showCookieBanner = false;" class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-900 bg-white hover:bg-gray-50">
                        Accept & Continue
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

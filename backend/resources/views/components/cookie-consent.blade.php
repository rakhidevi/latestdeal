<div x-data="{ 
        showCookieBanner: !localStorage.getItem('cookieConsent_preference'),
        showPolicyModal: false,
        activeTab: 'privacy',
        accept(level) {
            localStorage.setItem('cookieConsent_preference', level);
            this.showCookieBanner = false;
        }
    }"
     class="relative z-[100]">

    <!-- Cookie Banner -->
    <div x-show="showCookieBanner"
         class="fixed bottom-0 inset-x-0 pb-2 sm:pb-5 z-[100]"
         style="display: none;">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-700 shadow-2xl sm:p-5 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex-1 flex items-start gap-4">
                    <span class="flex p-2 rounded-lg bg-slate-800 shrink-0">
                        <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="font-bold text-white mb-1">Your Privacy Choices</h3>
                        <p class="text-sm text-slate-300">
                            We use cookies to improve your experience, personalize content, and analyze our traffic. 
                            By choosing "Accept All", you agree to our 
                            <button @click.prevent="showPolicyModal = true; activeTab = 'terms'" class="font-semibold text-red-400 hover:text-red-300 underline">Terms</button> and 
                            <button @click.prevent="showPolicyModal = true; activeTab = 'privacy'" class="font-semibold text-red-400 hover:text-red-300 underline">Privacy Policy</button>.
                        </p>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center justify-center md:justify-end gap-2 w-full md:w-auto shrink-0">
                    <button @click="accept('all')" class="rounded-lg bg-red-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-600 transition w-full sm:w-auto">
                        Accept All
                    </button>
                    <button @click="accept('mandatory')" class="rounded-lg bg-slate-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-600 transition w-full sm:w-auto border border-slate-600">
                        Mandatory Only
                    </button>
                    <button @click="accept('reject')" class="rounded-lg bg-transparent px-4 py-2.5 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition w-full sm:w-auto border border-transparent hover:border-slate-700">
                        Reject All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Policy Modal -->
    <div x-show="showPolicyModal" 
         style="display: none;"
         class="fixed inset-0 z-[110] overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop -->
        <div x-show="showPolicyModal"
             x-transition.opacity
             class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity"
             @click="showPolicyModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <!-- Modal Panel -->
            <div x-show="showPolicyModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                
                <!-- Header -->
                <div class="border-b border-slate-200 dark:border-slate-800 px-6 py-4 flex items-center justify-between">
                    <div class="flex gap-4">
                        <button @click="activeTab = 'privacy'" 
                                :class="{'text-red-500 border-b-2 border-red-500': activeTab === 'privacy', 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200': activeTab !== 'privacy'}"
                                class="text-lg font-bold pb-1 transition">
                            Privacy Policy
                        </button>
                        <button @click="activeTab = 'terms'" 
                                :class="{'text-red-500 border-b-2 border-red-500': activeTab === 'terms', 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200': activeTab !== 'terms'}"
                                class="text-lg font-bold pb-1 transition">
                            Terms of Service
                        </button>
                    </div>
                    <button @click="showPolicyModal = false" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Scrollable Content -->
                <div class="px-6 py-4 max-h-[60vh] overflow-y-auto prose dark:prose-invert prose-sm sm:prose-base">
                    
                    <!-- Privacy Content -->
                    <div x-show="activeTab === 'privacy'">
                        <p class="text-slate-500 dark:text-slate-400 mb-4">Last updated: {{ date('F d, Y') }}</p>
                        <h4 class="font-bold mt-4 mb-2">1. Information We Collect</h4>
                        <p class="mb-4">We collect information you provide directly to us, such as when you create or modify your account, request on-demand services, contact customer support, or otherwise communicate with us. This information may include: name, email, phone number, postal address, profile picture, and other information you choose to provide.</p>
                        <h4 class="font-bold mt-4 mb-2">2. How We Use Information</h4>
                        <p class="mb-4">We use the information we collect about you to provide, maintain, and improve our services, including, for example, to facilitate payments, send receipts, provide products and services you request, develop new features, provide customer support, develop safety features, authenticate users, and send product updates and administrative messages.</p>
                        <h4 class="font-bold mt-4 mb-2">3. Sharing of Information</h4>
                        <p class="mb-4">We may share the information we collect about you as described in this Statement or as described at the time of collection or sharing, including as follows: with third parties to provide you a service you requested through a partnership or promotional offering made by a third party or us.</p>
                        <h4 class="font-bold mt-4 mb-2">4. Cookies and Advertising</h4>
                        <p class="mb-4">We use cookies and similar technologies for purposes such as: authenticating users, remembering user preferences and settings, determining the popularity of content, delivering and measuring the effectiveness of advertising campaigns, analyzing site traffic and trends, and generally understanding the online behaviors and interests of people who interact with our services.</p>
                        <h4 class="font-bold mt-4 mb-2">5. Contact Us</h4>
                        <p class="mb-4">If you have any questions about this Privacy Statement, please contact us.</p>
                    </div>

                    <!-- Terms Content -->
                    <div x-show="activeTab === 'terms'" style="display: none;">
                        <p class="text-slate-500 dark:text-slate-400 mb-4">Last updated: {{ date('F d, Y') }}</p>
                        <h4 class="font-bold mt-4 mb-2">1. Acceptance of Terms</h4>
                        <p class="mb-4">By accessing and using LatestDeal, you accept and agree to be bound by the terms and provision of this agreement. In addition, when using LatestDeal's particular services, you shall be subject to any posted guidelines or rules applicable to such services.</p>
                        <h4 class="font-bold mt-4 mb-2">2. Description of Service</h4>
                        <p class="mb-4">LatestDeal provides users with access to a rich collection of resources, including various communications tools, forums, shopping services, and personalized content. You also understand and agree that the service may include advertisements and that these advertisements are necessary for LatestDeal to provide the service.</p>
                        <h4 class="font-bold mt-4 mb-2">3. Affiliate Links Disclosure</h4>
                        <p class="mb-4">Some of the links on LatestDeal are affiliate links. This means that if you click on the link and purchase the item, LatestDeal will receive an affiliate commission at no extra cost to you. We only recommend products or services we believe will add value to our readers.</p>
                        <h4 class="font-bold mt-4 mb-2">4. User Conduct</h4>
                        <p class="mb-4">You understand that all information, data, text, software, music, sound, photographs, graphics, video, messages or other materials, whether publicly posted or privately transmitted, are the sole responsibility of the person from which such content originated. You agree to not use the service to post content that is unlawful, harmful, or threatening.</p>
                        <h4 class="font-bold mt-4 mb-2">5. Modifications to Service</h4>
                        <p class="mb-4">LatestDeal reserves the right at any time and from time to time to modify or discontinue, temporarily or permanently, the service (or any part thereof) with or without notice.</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 flex justify-end">
                    <button @click="showPolicyModal = false" class="btn-primary">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

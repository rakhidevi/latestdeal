@extends('layouts.app')

@section('title', 'AI Shopping Assistant')

@section('content')
<div x-data="assistantApp()" class="space-y-6">
    <header class="panel flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900">AI Shopping Assistant</h1>
            <p class="mt-1 text-sm text-gray-600">Conversational deal discovery with instant AI filtering, comparison and recommendations.</p>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="rounded-full bg-slate-100 px-3 py-1.5 font-medium text-slate-700">
                Live • Active Deals: <span x-text="rawDeals.length"></span>
            </span>
            <button type="button" @click="window.location.reload()" class="btn-secondary px-3 py-1.5">Refresh</button>
        </div>
    </header>

    <div class="grid gap-6 lg:grid-cols-[1.15fr_1fr]">
        <!-- Chat Interface -->
        <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-gray-100 dark:border-slate-800 flex h-[650px] flex-col rounded-3xl p-6 shadow-2xl relative overflow-hidden">
            <!-- Decorative background glow -->
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-red-400/20 blur-3xl rounded-full pointer-events-none"></div>

            <div class="flex-1 space-y-6 overflow-y-auto pr-2 relative z-10 custom-scrollbar" id="chat-window">
                <template x-for="m in messages" :key="m.id">
                    <div class="flex flex-col animate-fade-in-up">
                        <!-- Role label (Optional, can be removed for cleaner look, but let's keep bubbles distinct) -->
                        <div :class="m.role === 'user' ? 'items-end' : 'items-start'" class="flex flex-col w-full">
                            <div class="flex items-center gap-2 mb-1" :class="m.role === 'user' ? 'justify-end' : 'justify-start'">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400" x-text="m.role === 'user' ? 'You' : 'LatestDeal AI'"></span>
                            </div>
                            <div :class="m.role === 'user' ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-l-2xl rounded-tr-2xl' : 'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-gray-200 rounded-r-2xl rounded-tl-2xl border border-gray-200 dark:border-slate-700'" 
                                 class="max-w-[85%] px-5 py-4 text-sm shadow-sm transition-all transform">
                                <div x-html="m.role === 'assistant' ? marked.parse(m.text) : m.text" class="leading-relaxed whitespace-pre-wrap prose prose-sm dark:prose-invert max-w-none prose-p:leading-relaxed prose-a:text-red-500 prose-a:no-underline hover:prose-a:underline"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <form @submit.prevent="onAsk" class="mt-4 relative z-10">
                <div class="relative flex items-center bg-white dark:bg-slate-950 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-red-500/50 focus-within:border-red-500 transition-all p-1.5">
                    <input
                        x-model="query"
                        :disabled="isSearching"
                        placeholder="E.g. Best smartphone under ₹30000"
                        class="w-full bg-transparent border-none focus:ring-0 px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 disabled:opacity-50 outline-none"
                    />
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white rounded-xl p-3 flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-red-500/20 mr-1" :disabled="isSearching">
                        <svg x-show="!isSearching" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        <svg x-show="isSearching" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </form>
            
            <div class="mt-4 flex flex-wrap gap-2 text-xs relative z-10">
                <button type="button" @click="setQuery('Best smartphone under ₹30000')" class="rounded-full border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 px-3.5 py-2 text-gray-600 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all shadow-sm">Best smartphone under ₹30000</button>
                <button type="button" @click="setQuery('Cheapest AirPods today')" class="rounded-full border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 px-3.5 py-2 text-gray-600 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all shadow-sm">Cheapest AirPods today</button>
                <button type="button" @click="setQuery('Laptop deals under ₹70000')" class="rounded-full border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 px-3.5 py-2 text-gray-600 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all shadow-sm">Laptop deals under ₹70000</button>
            </div>
        </div>

        <!-- Prediction & Comparison Panel -->
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-8 shadow-2xl text-white border border-slate-700 relative overflow-hidden">
                <!-- Abstract lines -->
                <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, #fff 0, #fff 1px, transparent 1px, transparent 10px);"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold text-slate-300 uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            AI Price Prediction
                        </h3>
                    </div>
                
                <div class="flex items-baseline space-x-2">
                    <span class="text-4xl font-black">₹<span x-text="bestDeal ? Math.round(bestDeal.price).toLocaleString('en-IN') : '0'"></span></span>
                    <span class="text-emerald-400 font-medium bg-emerald-400/10 px-2 py-0.5 rounded text-sm">-<span x-text="bestDeal ? bestDeal.discount_pct : '0'"></span>% OFF</span>
                </div>
                
                <p class="mt-4 text-sm text-slate-300 bg-white/5 p-3 rounded-xl border border-white/10" 
                   :class="{'animate-pulse text-slate-400': predictionLoading}"
                   x-text="predictionText"></p>
                </div>
            </div>

            <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md rounded-3xl p-8 shadow-2xl border border-gray-100 dark:border-slate-800">
                <h3 class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    AI Comparison Matrix
                </h3>
                
                <template x-if="comparisonDeals.length === 0">
                    <p class="text-sm text-gray-500 italic p-6 text-center bg-gray-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-gray-200 dark:border-slate-700">No products match your query. Ask the AI to find something!</p>
                </template>
                
                <div class="space-y-3">
                    <template x-for="(row, idx) in comparisonDeals" :key="row.id">
                        <div class="rounded-2xl border border-gray-100 dark:border-slate-700/60 p-4 hover:bg-gray-50 dark:hover:bg-slate-800/80 transition-all duration-300 cursor-pointer shadow-sm hover:shadow-md" @click="selectedDeal = row" :class="selectedDeal && selectedDeal.id === row.id ? 'ring-2 ring-red-500 border-transparent bg-red-50/20 dark:bg-red-500/10' : 'bg-white dark:bg-slate-900'">
                            <p class="line-clamp-1 font-bold text-gray-900 dark:text-white text-sm"><span x-text="'#' + (idx + 1) + ' '" class="text-red-500 mr-1"></span> <span x-text="row.title"></span></p>
                            <div class="mt-2 flex items-center justify-between text-xs">
                                <span class="font-bold text-lg text-gray-900 dark:text-white">₹<span x-text="Math.round(row.price).toLocaleString('en-IN')"></span></span>
                                <span class="text-gray-500 dark:text-slate-400 font-medium px-2 py-1 bg-gray-100 dark:bg-slate-800 rounded-md" x-text="row.merchant"></span>
                            </div>
                        </div>
                    </template>
                </div>
                
                <template x-if="bestDeal">
                    <div class="mt-6 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border border-emerald-100 dark:border-emerald-800/30 p-5 shadow-inner">
                        <p class="font-bold text-emerald-700 dark:text-emerald-400 text-sm mb-3 flex items-center tracking-wide uppercase">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            AI Best Pick
                        </p>
                        <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 leading-relaxed" x-text="bestDeal.title"></p>
                        <ul class="mt-3 space-y-2 text-xs text-gray-600 dark:text-gray-300">
                            <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">•</span> Highest value score blend.</li>
                            <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">•</span> Strong price-to-feature ratio at <span class="font-bold text-gray-900 dark:text-white">₹<span x-text="Math.round(bestDeal.price).toLocaleString('en-IN')"></span></span>.</li>
                            <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">•</span> Reliable marketplace signal from <span class="font-bold text-gray-900 dark:text-white" x-text="bestDeal.merchant"></span>.</li>
                        </ul>
                        <div class="mt-4">
                            <button @click="startLiveComparison(bestDeal)" class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 text-sm transition-colors flex items-center justify-center gap-2 shadow-sm" :disabled="isComparing">
                                <svg x-show="!isComparing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <svg x-show="isComparing" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="isComparing ? compareStageText : 'Compare Prices Live'"></span>
                            </button>
                        </div>
                        
                        <!-- Live Comparison Results -->
                        <div x-show="comparisonResults" class="mt-4 border-t border-emerald-200/50 dark:border-emerald-800/50 pt-4" x-transition>
                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-3">Live Competitor Prices</h4>
                            <div class="space-y-3">
                                <template x-for="comp in comparisonResults" :key="comp.store">
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-white/50 dark:bg-slate-900/50 border border-emerald-100 dark:border-emerald-800/30">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="comp.store"></span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] text-gray-500" x-text="comp.delivery || 'Standard Delivery'"></span>
                                                <span class="text-[10px] text-yellow-500 font-bold" x-show="comp.rating" x-text="'⭐ ' + comp.rating"></span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">₹<span x-text="comp.price.toLocaleString('en-IN')"></span></span>
                                            <a :href="comp.url" target="_blank" class="text-[10px] text-blue-500 hover:underline">View</a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <template x-if="aiValueScore">
                                <div class="mt-3 bg-emerald-100 dark:bg-emerald-900/40 p-2 rounded-lg text-center">
                                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300">AI Value Score: <span x-text="aiValueScore + '/100'"></span></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- AI Results Grid -->
    <div class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-slate-900">Top Matches</h2>
            <p class="text-sm text-slate-500"><span x-text="filteredDeals.length"></span> results found</p>
        </div>
        
        <template x-if="filteredDeals.length === 0">
            <div class="bg-white/50 dark:bg-slate-900/50 backdrop-blur border border-gray-100 dark:border-slate-800 p-16 text-center rounded-3xl shadow-sm">
                <svg class="w-16 h-16 text-gray-300 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-gray-900 dark:text-white font-bold text-lg">No matching deals found.</p>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-2">Ask the AI to broaden the search criteria or try a different product category.</p>
            </div>
        </template>
        
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <template x-for="deal in filteredDeals" :key="deal.id">
                <!-- Inline Deal Card -->
                <div class="group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 hover:shadow-xl hover:border-red-200 transition-all duration-300 hover:-translate-y-1 cursor-pointer" @click="selectedDeal = deal">
                    <!-- Discount Badge -->
                    <div class="absolute left-3 top-3 z-10 flex flex-col gap-1">
                        <span class="rounded-full bg-red-600 px-2.5 py-1 text-xs font-black tracking-wide text-white shadow-sm" x-text="'-' + deal.discount_pct + '%'"></span>
                    </div>

                    <div class="relative aspect-square overflow-hidden bg-slate-50 p-4 rounded-xl">
                        <img :src="deal.image_path.startsWith('http') || deal.image_path.startsWith('/') ? deal.image_path : '/storage/' + deal.image_path"
                             :alt="deal.title"
                             class="h-full w-full object-contain transition-transform duration-500 group-hover:scale-105 mix-blend-multiply"
                             onerror="this.src='/images/logo.png'" />
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <div class="mb-2 flex items-center justify-between text-xs font-medium">
                            <span class="text-red-600 bg-red-50 px-2 py-0.5 rounded text-[10px] uppercase tracking-wider font-bold" x-text="deal.merchant"></span>
                            <span class="text-slate-400" x-text="deal.category"></span>
                        </div>

                        <h3 class="mb-2 line-clamp-2 text-sm font-semibold leading-snug text-slate-800 group-hover:text-red-600 transition-colors" x-text="deal.title"></h3>

                        <div class="mt-auto pt-2">
                            <div class="flex items-baseline gap-2">
                                <span class="text-xl font-black tracking-tight text-gray-900">₹<span x-text="Math.round(deal.price).toLocaleString('en-IN')"></span></span>
                                <template x-if="deal.original_price > deal.price">
                                    <span class="text-sm font-medium text-slate-400 line-through">₹<span x-text="Math.round(deal.original_price).toLocaleString('en-IN')"></span></span>
                                </template>
                            </div>
                            <a :href="'/deal/' + deal.id" class="mt-3 block w-full rounded-xl bg-slate-900 py-2.5 text-center text-sm font-bold text-white transition-all hover:bg-red-600 hover:shadow-lg hover:shadow-red-500/30">
                                View Deal
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('assistantApp', () => ({
        rawDeals: @json($deals),
        messages: [
            { id: 1, role: 'assistant', text: 'Hi! Ask me for deal recommendations. Example: "Best smartphone under ₹30000" or "Cheapest AirPods".' }
        ],
        query: '',
        selectedDeal: null,
        filters: {
            budget: null,
            keyword: null,
            intent: 'best' // 'best' or 'cheapest'
        },
        predictionText: 'Select a deal to see AI prediction.',
        predictionLoading: false,
        
        isComparing: false,
        compareStageText: 'Compare Prices Live',
        comparisonResults: null,
        aiValueScore: null,
        comparisonInterval: null,

        init() {
            this.$watch('bestDeal', value => {
                this.comparisonResults = null;
                this.aiValueScore = null;
                if (value) {
                    this.fetchPrediction(value.id);
                } else {
                    this.predictionText = 'Select a deal to see AI prediction.';
                }
            });
            // trigger on load if bestDeal exists
            if (this.bestDeal) {
                this.fetchPrediction(this.bestDeal.id);
            }
        },

        fetchPrediction(dealId) {
            this.predictionLoading = true;
            this.predictionText = 'AI is analyzing price history...';
            
            fetch('/api/predict-price', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ deal_id: dealId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    this.predictionText = data.data.prediction;
                } else {
                    this.predictionText = 'Prediction unavailable.';
                }
            })
            .catch(err => {
                this.predictionText = 'Error connecting to AI.';
            })
            .finally(() => {
                this.predictionLoading = false;
            });
        },

        get filteredDeals() {
            if (this.aiSearchResults !== null) {
                return this.aiSearchResults;
            }
            return [...this.rawDeals].slice(0, 12);
        },
        
        get comparisonDeals() {
            return this.filteredDeals.slice(0, 4);
        },
        
        get bestDeal() {
            return this.selectedDeal || this.comparisonDeals[0] || null;
        },

        setQuery(q) {
            this.query = q;
            this.onAsk();
        },

        async startLiveComparison(deal) {
            if (!deal) return;
            this.isComparing = true;
            this.comparisonResults = null;
            this.aiValueScore = null;
            
            try {
                // 1. Dispatch request
                const res = await fetch('/api/compare-prices', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ deal_id: deal.id, title: deal.title })
                });
                const data = await res.json();
                
                if (data.status === 'cache_hit') {
                    this.comparisonResults = data.data.results;
                    this.aiValueScore = data.data.ai_score;
                    this.isComparing = false;
                    return;
                }
                
                const stages = [
                    "Queuing comparison task...",
                    "Searching Amazon...",
                    "Checking Flipkart...",
                    "Querying Croma...",
                    "AI is scoring the deals..."
                ];
                let currentStage = 0;
                this.compareStageText = stages[0];
                
                let stageInterval = setInterval(() => {
                    currentStage++;
                    if(currentStage >= stages.length) currentStage = stages.length - 1;
                    this.compareStageText = stages[currentStage];
                }, 2000);
                
                // 2. Poll for results if pending
                const jobId = data.job_id;
                let attempts = 0;
                
                this.comparisonInterval = setInterval(async () => {
                    attempts++;
                    if (attempts > 30) { // 60s timeout
                        clearInterval(this.comparisonInterval);
                        clearInterval(stageInterval);
                        this.isComparing = false;
                        alert("Comparison timed out.");
                        return;
                    }
                    
                    const statusRes = await fetch(`/api/compare-prices/${jobId}`);
                    const statusData = await statusRes.json();
                    
                    if (statusData.status === 'completed') {
                        clearInterval(this.comparisonInterval);
                        clearInterval(stageInterval);
                        this.comparisonResults = statusData.data.results;
                        this.aiValueScore = statusData.data.ai_score;
                        this.isComparing = false;
                    } else if (statusData.status === 'failed') {
                        clearInterval(this.comparisonInterval);
                        clearInterval(stageInterval);
                        this.isComparing = false;
                        alert("Failed to retrieve live prices.");
                    }
                }, 2000);
                
            } catch (err) {
                console.error("Comparison error", err);
                this.isComparing = false;
            }
        },

        aiSearchResults: null,
        isSearching: false,

        onAsk() {
            const text = this.query.trim();
            if (!text) return;

            this.messages.push({ id: Date.now(), role: 'user', text });
            
            const aiMessageId = Date.now() + 1;
            
            const keywords = text.substring(0, 35);
            const stages = [
                `🔍 Understanding your request: "${keywords}..."`,
                `🛒 Searching active deals...`,
                `💰 Analyzing pricing history...`,
                `🤖 Ranking best recommendations...`
            ];
            let currentStage = 0;
            
            const getLoadingHtml = (msg) => `<div class="flex items-center space-x-2 text-slate-500 dark:text-slate-400 text-sm italic font-medium"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>${msg}</span></div>`;

            this.messages.push({ id: aiMessageId, role: 'assistant', text: getLoadingHtml(stages[0]) });
            
            this.query = '';
            this.isSearching = true;
            this.selectedDeal = null;
            
            this.$nextTick(() => {
                const el = document.getElementById('chat-window');
                if(el) el.scrollTop = el.scrollHeight;
            });
            
            let loadingInterval = setInterval(() => {
                currentStage++;
                if (currentStage >= stages.length) currentStage = stages.length - 1;
                const msgIndex = this.messages.findIndex(m => m.id === aiMessageId);
                if (msgIndex !== -1 && this.isSearching) {
                    this.messages[msgIndex].text = getLoadingHtml(stages[currentStage]);
                }
            }, 1200);
            
            // Step 1: Hit Smart Search API to get deals based on intent
            fetch('/api/smart-search?q=' + encodeURIComponent(text))
            .then(res => res.json())
            .then(searchData => {
                if (searchData.deals && searchData.deals.length > 0) {
                    this.aiSearchResults = searchData.deals;
                } else {
                    this.aiSearchResults = [];
                }
                this.isSearching = false;

                // Step 2: Hit Chat API to generate a conversational response
                return fetch('/api/assistant/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        message: text,
                        deal_ids: this.aiSearchResults.map(d => d.id)
                    })
                });
            })
            .then(res => res ? res.json() : null)
            .then(data => {
                if (!data) return;
                const msgIndex = this.messages.findIndex(m => m.id === aiMessageId);
                if (msgIndex !== -1) {
                    this.messages[msgIndex].text = data.reply || "I found some deals that match your criteria.";
                }
                this.$nextTick(() => {
                    const el = document.getElementById('chat-window');
                    if(el) el.scrollTop = el.scrollHeight;
                });
            })
            .catch(err => {
                this.isSearching = false;
                const msgIndex = this.messages.findIndex(m => m.id === aiMessageId);
                if (msgIndex !== -1) {
                    this.messages[msgIndex].text = "Error connecting to AI backend.";
                }
            })
            .finally(() => {
                clearInterval(loadingInterval);
            });
        }
    }));
});
</script>
@endsection

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
        <div class="glass-panel flex h-[600px] flex-col rounded-3xl p-6 shadow-lg">
            <div class="flex-1 space-y-4 overflow-y-auto pr-2" id="chat-window">
                <template x-for="m in messages" :key="m.id">
                    <div :class="m.role === 'user' ? 'ml-auto bg-red-600 text-white' : 'bg-slate-100 text-slate-800'" 
                         class="max-w-[85%] rounded-2xl px-4 py-3 text-sm shadow-sm transition-all transform animate-fade-in-up">
                        <div x-html="m.role === 'assistant' ? marked.parse(m.text) : m.text" class="leading-relaxed whitespace-pre-wrap [&>strong]:font-bold [&>ul]:list-disc [&>ul]:pl-5 [&>p]:mb-2 [&>a]:text-red-600 [&>a]:underline"></div>
                    </div>
                </template>
            </div>
            
            <form @submit.prevent="onAsk" class="mt-4 flex gap-2 border-t border-slate-200 pt-4">
                <input
                    x-model="query"
                    placeholder="E.g. Best smartphone under ₹30000"
                    class="input-base w-full rounded-xl bg-slate-50 border-slate-200 focus:bg-white"
                />
                <button type="submit" class="btn-primary rounded-xl px-6">Ask</button>
            </form>
            
            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                <button type="button" @click="setQuery('Best smartphone under ₹30000')" class="rounded-full border border-red-200 bg-red-50/50 px-3 py-1.5 text-red-700 hover:bg-red-50 transition-colors">Best smartphone under ₹30000</button>
                <button type="button" @click="setQuery('Cheapest AirPods today')" class="rounded-full border border-red-200 bg-red-50/50 px-3 py-1.5 text-red-700 hover:bg-red-50 transition-colors">Cheapest AirPods today</button>
                <button type="button" @click="setQuery('Laptop deals under ₹70000')" class="rounded-full border border-red-200 bg-red-50/50 px-3 py-1.5 text-red-700 hover:bg-red-50 transition-colors">Laptop deals under ₹70000</button>
            </div>
        </div>

        <!-- Prediction & Comparison Panel -->
        <div class="space-y-6">
            <div class="glass-panel rounded-3xl p-6 shadow-lg bg-gradient-to-br from-slate-900 to-slate-800 text-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">AI Price Prediction</h3>
                    <i data-lucide="trending-down" class="text-emerald-400 w-5 h-5"></i>
                </div>
                
                <div class="flex items-baseline space-x-2">
                    <span class="text-4xl font-black">₹<span x-text="bestDeal ? Math.round(bestDeal.price).toLocaleString('en-IN') : '0'"></span></span>
                    <span class="text-emerald-400 font-medium bg-emerald-400/10 px-2 py-0.5 rounded text-sm">-<span x-text="bestDeal ? bestDeal.discount_pct : '0'"></span>% OFF</span>
                </div>
                
                <p class="mt-4 text-sm text-slate-300 bg-white/5 p-3 rounded-xl border border-white/10" 
                   x-text="bestDeal ? (bestDeal.discount_pct > 40 ? 'Momentum is strong. High confidence this is near the best available price.' : 'Likely to drop soon. Wait for a better entry price.') : 'Select a deal to see AI prediction.'"></p>
            </div>

            <div class="glass-panel rounded-3xl p-6 shadow-lg">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">AI Comparison</h3>
                
                <template x-if="comparisonDeals.length === 0">
                    <p class="text-sm text-slate-500 italic p-4 text-center bg-slate-50 rounded-xl">No products match your query.</p>
                </template>
                
                <div class="space-y-3">
                    <template x-for="(row, idx) in comparisonDeals" :key="row.id">
                        <div class="rounded-xl border border-slate-200 p-3 hover:bg-slate-50 transition-colors cursor-pointer" @click="selectedDeal = row">
                            <p class="line-clamp-1 font-bold text-slate-800"><span x-text="'#' + (idx + 1) + ' '"></span> <span x-text="row.title"></span></p>
                            <div class="mt-1 flex items-center justify-between text-xs">
                                <span class="font-medium text-slate-600">₹<span x-text="Math.round(row.price).toLocaleString('en-IN')"></span></span>
                                <span class="text-slate-400" x-text="row.merchant"></span>
                            </div>
                        </div>
                    </template>
                </div>
                
                <template x-if="bestDeal">
                    <div class="mt-4 rounded-xl bg-emerald-50 border border-emerald-100 p-4">
                        <p class="font-bold text-emerald-800 text-sm mb-2 flex items-center"><i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Best Pick</p>
                        <p class="text-sm font-medium text-emerald-900 line-clamp-1" x-text="bestDeal.title"></p>
                        <ul class="mt-2 space-y-1 text-xs text-emerald-700">
                            <li>• Highest value score blend.</li>
                            <li>• Strong price-to-feature ratio at ₹<span x-text="Math.round(bestDeal.price).toLocaleString('en-IN')"></span>.</li>
                            <li>• Reliable marketplace signal from <span x-text="bestDeal.merchant"></span>.</li>
                        </ul>
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
            <div class="glass-panel p-12 text-center rounded-3xl">
                <i data-lucide="search-x" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                <p class="text-slate-600 font-medium">No matching deals found.</p>
                <p class="text-sm text-slate-400 mt-1">Try a higher budget or broader keyword.</p>
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

                    <div class="relative aspect-square overflow-hidden bg-slate-50 p-4">
                        <img :src="deal.image_path.startsWith('http') ? deal.image_path : '/storage/' + deal.image_path"
                             :alt="deal.title"
                             class="h-full w-full object-contain transition-transform duration-500 group-hover:scale-105 mix-blend-multiply"
                             onerror="this.src='/placeholder.jpg'" />
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
        filters: {
            budget: null,
            keyword: null,
            intent: 'best' // 'best' or 'cheapest'
        },
        selectedDeal: null,

        get filteredDeals() {
            let rows = [...this.rawDeals];
            
            if (this.filters.keyword) {
                const kw = this.filters.keyword.toLowerCase();
                const synonyms = {
                    'smartphone': ['phone', 'mobile', 'iphone', 'samsung', 'smartphone'],
                    'laptop': ['macbook', 'notebook', 'laptop'],
                    'tv': ['television', 'smart tv', 'tv'],
                    'earbuds': ['airpods', 'buds', 'earbuds', 'tws']
                };
                const searchTerms = synonyms[kw] || [kw];
                
                rows = rows.filter(d => {
                    const title = d.title.toLowerCase();
                    const cat = d.category ? d.category.toLowerCase() : '';
                    return searchTerms.some(term => title.includes(term) || cat.includes(term));
                });
            }
            if (this.filters.budget) {
                rows = rows.filter(d => d.price <= this.filters.budget);
            }
            
            if (this.filters.intent === 'cheapest') {
                rows.sort((a, b) => a.price - b.price);
            } else {
                // Approximate 'best' by highest discount pct
                rows.sort((a, b) => b.discount_pct - a.discount_pct);
            }
            
            return rows.slice(0, 12);
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

        onAsk() {
            const text = this.query.trim();
            if (!text) return;

            // Simple NLP parser
            let nextFilters = { ...this.filters };
            
            // Extract budget (under/below X)
            const budgetMatch = text.match(/(?:under|below|<=?)\s*[₹rs\s]*([\d,]{3,})/i);
            if (budgetMatch) {
                nextFilters.budget = Number(budgetMatch[1].replace(/,/g, ''));
            }
            
            // Extract Intent
            const lower = text.toLowerCase();
            if (lower.includes('cheapest') || lower.includes('lowest')) {
                nextFilters.intent = 'cheapest';
            } else if (lower.includes('best') || lower.includes('top')) {
                nextFilters.intent = 'best';
            }
            
            // Extract Keyword
            const keywords = ["smartphone", "airpods", "laptop", "headphone", "tv", "monitor", "earbuds", "kitchen", "mobile", "watch", "shoe"];
            const foundKw = keywords.find(kw => lower.includes(kw));
            if (foundKw) {
                nextFilters.keyword = foundKw;
            } else {
                // Try to find a noun if it's not in the list
                const words = text.toLowerCase().replace(/[^\w\s]/gi, '').split(' ');
                const stopWords = ['best', 'cheapest', 'under', 'below', 'for', 'with', 'in', 'today', 'deals', 'the', 'a', 'an'];
                const searchWords = words.filter(w => !stopWords.includes(w) && isNaN(w));
                if (searchWords.length > 0) {
                    nextFilters.keyword = searchWords[0];
                }
            }

            // Build Assistant Response using real AI
            this.filters = nextFilters;
            this.messages.push({ id: Date.now(), role: 'user', text });
            
            const aiMessageId = Date.now() + 1;
            this.messages.push({ id: aiMessageId, role: 'assistant', text: '...' });
            
            this.query = '';
            
            this.$nextTick(() => {
                const el = document.getElementById('chat-window');
                if(el) el.scrollTop = el.scrollHeight;
            });
            
            fetch('/api/assistant/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    message: text,
                    deal_ids: this.filteredDeals.map(d => d.id)
                })
            })
            .then(res => res.json())
            .then(data => {
                const msgIndex = this.messages.findIndex(m => m.id === aiMessageId);
                if (msgIndex !== -1) {
                    this.messages[msgIndex].text = data.reply || "No response received.";
                }
                this.$nextTick(() => {
                    const el = document.getElementById('chat-window');
                    if(el) el.scrollTop = el.scrollHeight;
                });
            })
            .catch(err => {
                const msgIndex = this.messages.findIndex(m => m.id === aiMessageId);
                if (msgIndex !== -1) {
                    this.messages[msgIndex].text = "Error connecting to AI backend.";
                }
            });
        }
    }));
});
</script>
@endsection

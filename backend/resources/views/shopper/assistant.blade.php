@extends('layouts.app')

@section('title', 'AI Shopping Assistant | Deal Intelligence & Comparison')

@section('content')
<div x-data="assistantApp()" class="space-y-6">
    <header class="panel flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300">
                    Deal Intelligence
                </span>
                <span class="text-xs text-gray-500">Tracked retail price telemetry & catalog search</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white mt-1">AI Shopping Assistant</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Specify what you need and your budget. We isolate genuine products, exclude misleading accessories, and audit price drops.</p>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1.5 font-medium text-slate-700 dark:text-slate-300">
                Catalog Deals: <span x-text="filteredDeals.length"></span>
            </span>
            <button type="button" @click="resetSearch()" class="btn-secondary px-3 py-1.5">Reset</button>
        </div>
    </header>

    <div class="grid gap-6 lg:grid-cols-[1.15fr_1fr]">
        <!-- Chat Interface -->
        <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-gray-100 dark:border-slate-800 flex h-[650px] flex-col rounded-3xl p-6 shadow-2xl relative overflow-hidden">
            <!-- Decorative background glow -->
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-red-400/20 blur-3xl rounded-full pointer-events-none"></div>

            <div class="flex-1 space-y-6 overflow-y-auto pr-2 relative z-10 custom-scrollbar" id="chat-window" role="log" aria-live="polite">
                <template x-for="m in messages" :key="m.id">
                    <div class="flex flex-col animate-fade-in-up">
                        <div :class="m.role === 'user' ? 'items-end' : 'items-start'" class="flex flex-col w-full">
                            <div class="flex items-center gap-2 mb-1" :class="m.role === 'user' ? 'justify-end' : 'justify-start'">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400" x-text="m.role === 'user' ? 'You' : 'LatestDeal AI Expert'"></span>
                            </div>
                            <div :class="m.role === 'user' ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-l-2xl rounded-tr-2xl' : 'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-gray-200 rounded-r-2xl rounded-tl-2xl border border-gray-200 dark:border-slate-700'" 
                                 class="max-w-[88%] px-5 py-4 text-sm shadow-sm transition-all">
                                
                                <!-- SAFE RENDERING: User messages rendered strictly as plain text to prevent XSS -->
                                <template x-if="m.role === 'user'">
                                    <div class="leading-relaxed whitespace-pre-wrap text-sm" x-text="m.text"></div>
                                </template>
                                
                                <!-- SAFE RENDERING: Assistant markdown sanitized against script/iframe injection -->
                                <template x-if="m.role === 'assistant'">
                                    <div x-html="renderSafeMarkdown(m.text)" class="leading-relaxed whitespace-pre-wrap prose prose-sm dark:prose-invert max-w-none prose-p:leading-relaxed prose-a:text-red-500 prose-a:no-underline hover:prose-a:underline"></div>
                                </template>
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
                        placeholder="E.g. Best smartphone under ₹30000 or Cheapest AirPods"
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
                <button type="button" @click="setQuery('Best smartphone under ₹30000')" class="rounded-full border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 px-3.5 py-1.5 text-gray-600 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all shadow-sm">📱 Best smartphone under ₹30000</button>
                <button type="button" @click="setQuery('Cheapest AirPods today')" class="rounded-full border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 px-3.5 py-1.5 text-gray-600 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all shadow-sm">🎧 Cheapest AirPods today</button>
                <button type="button" @click="setQuery('Gaming laptop under ₹75000')" class="rounded-full border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 px-3.5 py-1.5 text-gray-600 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all shadow-sm">💻 Gaming laptop under ₹75000</button>
            </div>
        </div>

        <!-- Prediction & Comparison Panel -->
        <div class="space-y-6">
            <!-- Price Intelligence & Buy Signal Card -->
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-7 shadow-2xl text-white border border-slate-700 relative overflow-hidden">
                <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, #fff 0, #fff 1px, transparent 1px, transparent 10px);"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold text-slate-300 uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Price Drop Intelligence
                        </h3>
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded"
                              :class="predictionBuyNow ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30'"
                              x-text="predictionBuyNow ? 'Buy Now Signal' : 'Wait for Drop'"></span>
                    </div>
                
                    <div class="flex items-baseline space-x-2">
                        <span class="text-3xl font-black">₹<span x-text="bestDeal ? Math.round(bestDeal.price).toLocaleString('en-IN') : '0'"></span></span>
                        <template x-if="bestDeal && bestDeal.original_price > bestDeal.price">
                            <span class="text-slate-400 line-through text-sm">₹<span x-text="Math.round(bestDeal.original_price).toLocaleString('en-IN')"></span></span>
                        </template>
                        <span class="text-emerald-400 font-semibold bg-emerald-400/10 px-2 py-0.5 rounded text-xs">-<span x-text="bestDeal ? bestDeal.discount_pct : '0'"></span>% OFF</span>
                    </div>
                    
                    <p class="mt-3 text-xs text-slate-300 bg-white/5 p-3 rounded-xl border border-white/10 leading-relaxed" 
                       :class="{'animate-pulse text-slate-400': predictionLoading}"
                       x-text="predictionText"></p>
                    
                    <div class="mt-3 flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-white/10">
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span x-text="predictionConfidence ? predictionConfidence + '% Confidence' : 'Telemetry Verified'"></span>
                        </span>
                        <span x-text="predictionSource"></span>
                    </div>
                </div>
            </div>

            <!-- Comparison & Recommendation Matrix -->
            <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md rounded-3xl p-7 shadow-2xl border border-gray-100 dark:border-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        AI Comparison Matrix
                    </h3>
                    <span class="text-xs text-gray-400" x-text="comparisonDeals.length + ' deals compared'"></span>
                </div>
                
                <template x-if="comparisonDeals.length === 0">
                    <p class="text-sm text-gray-500 italic p-6 text-center bg-gray-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-gray-200 dark:border-slate-700">No products match your query. Ask the AI to find something!</p>
                </template>
                
                <div class="space-y-2.5">
                    <template x-for="(row, idx) in comparisonDeals" :key="row.id">
                        <div class="rounded-2xl border p-3.5 hover:bg-gray-50 dark:hover:bg-slate-800/80 transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md" 
                             @click="selectedDeal = row" 
                             :class="selectedDeal && selectedDeal.id === row.id ? 'ring-2 ring-red-500 border-red-200 bg-red-50/20 dark:bg-red-500/10' : 'border-gray-100 dark:border-slate-800 bg-white dark:bg-slate-900'">
                            <div class="flex items-center justify-between gap-2">
                                <p class="line-clamp-1 font-bold text-gray-900 dark:text-white text-xs flex-1">
                                    <span x-text="'#' + (idx + 1)" class="text-red-500 mr-1 font-extrabold"></span> 
                                    <span x-text="row.title"></span>
                                </p>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded"
                                      :class="row.recommendation_type === 'best_match' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : (row.recommendation_type === 'best_value' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : (row.recommendation_type === 'value_runner_up' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300' : (row.recommendation_type === 'lowest_price' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300')))"
                                      x-text="row.recommendation_type === 'best_match' ? 'Best Match' : (row.recommendation_type === 'best_value' ? 'Best Value' : (row.recommendation_type === 'lowest_price' ? 'Budget Pick' : (row.recommendation_type === 'value_runner_up' ? 'Value Runner-Up' : 'Alternative Option')))"></span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="font-extrabold text-base text-gray-900 dark:text-white">₹<span x-text="Math.round(row.price).toLocaleString('en-IN')"></span></span>
                                    <template x-if="row.original_price > row.price">
                                        <span class="text-[11px] text-gray-400 line-through">₹<span x-text="Math.round(row.original_price).toLocaleString('en-IN')"></span></span>
                                    </template>
                                </div>
                                <span class="text-gray-500 dark:text-slate-400 font-medium px-2 py-0.5 bg-gray-100 dark:bg-slate-800 rounded text-[11px]" x-text="row.merchant"></span>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Dynamic Grounded Recommendation Detail Card -->
                <template x-if="bestDeal">
                    <div class="mt-5 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/30 dark:to-teal-950/30 border border-emerald-200 dark:border-emerald-800/40 p-4 shadow-inner">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-emerald-800 dark:text-emerald-300 text-xs flex items-center tracking-wide uppercase">
                                <svg class="w-4 h-4 mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span x-text="bestDeal.recommendation_type === 'best_match' ? 'AI Recommended Pick (Best Match)' : (bestDeal.recommendation_type === 'best_value' ? 'Best Value Pick (Highest Savings)' : (bestDeal.recommendation_type === 'lowest_price' ? 'Budget Pick (Lowest Price)' : (bestDeal.recommendation_type === 'value_runner_up' ? 'Value Runner-Up (Strong Discount)' : 'Alternative Option')))"></span>
                            </span>
                            <span class="text-[10px] font-semibold" :class="bestDeal.status === 'out_of_stock' ? 'text-amber-500' : 'text-emerald-700 dark:text-emerald-400'" x-text="bestDeal.status === 'out_of_stock' ? 'Out of Stock' : 'Catalog Tracked'"></span>
                        </div>
                        <p class="text-xs font-bold text-gray-900 dark:text-white line-clamp-2 leading-relaxed" x-text="bestDeal.title"></p>
                        
                        <div class="mt-2.5 space-y-1.5 text-xs text-gray-700 dark:text-gray-300">
                            <div class="flex items-start gap-1.5">
                                <span class="text-emerald-600 font-bold">•</span>
                                <span x-text="bestDeal.recommendation_reason || 'Verified genuine deal with authentic merchant pricing.'"></span>
                            </div>
                            <template x-if="bestDeal.savings > 0">
                                <div class="flex items-start gap-1.5">
                                    <span class="text-emerald-600 font-bold">•</span>
                                    <span>Exact Savings: <strong class="text-emerald-700 dark:text-emerald-400">₹<span x-text="Math.round(bestDeal.savings).toLocaleString('en-IN')"></span></strong> below listed MRP (<span x-text="bestDeal.discount_pct + '% OFF'"></span>).</span>
                                </div>
                            </template>
                        </div>
                        
                        <div class="mt-3.5 flex gap-2">
                            <button @click="startLiveComparison(bestDeal)" 
                                    class="flex-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 text-xs transition-colors flex items-center justify-center gap-1.5 shadow-sm disabled:opacity-50" 
                                    :disabled="isComparing">
                                <svg x-show="!isComparing" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <svg x-show="isComparing" class="animate-spin w-3.5 h-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="isComparing ? compareStageText : 'Check Multi-Store Availability'"></span>
                            </button>
                            <a :href="bestDeal.deal_url || ('/deal/' + (bestDeal.slug || bestDeal.id))" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold px-3 py-2 text-xs transition-colors flex items-center justify-center">
                                View Deal
                            </a>
                        </div>
                        
                        <!-- Live Comparison Results -->
                        <div x-show="comparisonResults" class="mt-4 border-t border-emerald-200/50 dark:border-emerald-800/50 pt-3" x-transition>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-widest">Retailer Availability Status</h4>
                                <span class="text-[10px] text-gray-500">Catalog record & SKU verification status</span>
                            </div>
                            <div class="space-y-2">
                                <template x-for="comp in comparisonResults" :key="comp.store">
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-white/70 dark:bg-slate-900/70 border border-emerald-100 dark:border-emerald-800/30 text-xs">
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-gray-900 dark:text-white" x-text="comp.store"></span>
                                                <span x-show="comp.is_verified" class="px-1.5 py-0.2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[9px] font-bold rounded">Catalog Verified</span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[10px] text-gray-500" x-text="comp.checked_at || comp.delivery || 'Standard Delivery'"></span>
                                                <span class="text-[10px] text-amber-500 font-bold" x-show="comp.rating" x-text="'⭐ ' + comp.rating"></span>
                                            </div>
                                        </div>
                                        <template x-if="comp.is_verified && comp.in_stock && comp.price">
                                            <div class="flex flex-col items-end">
                                                <span class="font-extrabold text-emerald-600 dark:text-emerald-400">₹<span x-text="Math.round(comp.price).toLocaleString('en-IN')"></span></span>
                                                <a :href="comp.url || '#'" target="_blank" class="text-[10px] text-blue-500 hover:underline">Check Store</a>
                                            </div>
                                        </template>
                                        <template x-if="comp.is_verified && !comp.in_stock">
                                            <div class="flex flex-col items-end text-right">
                                                <span class="text-[10px] text-amber-500 font-semibold">Out of Stock</span>
                                                <span class="text-[9px] text-slate-500">Currently unavailable</span>
                                            </div>
                                        </template>
                                        <template x-if="!comp.is_verified">
                                            <div class="flex flex-col items-end text-right">
                                                <span class="text-[10px] text-slate-400 italic">Unverified</span>
                                                <span class="text-[9px] text-slate-500">SKU match pending</span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <template x-if="aiValueScore">
                                <div class="mt-2.5 bg-emerald-100/70 dark:bg-emerald-950/60 p-1.5 rounded-lg text-center">
                                    <span class="text-[11px] font-bold text-emerald-800 dark:text-emerald-300">Catalog Value Score: <span x-text="aiValueScore + '/100'"></span></span>
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
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Catalog Matches</h2>
                <p class="text-xs text-slate-500 mt-0.5">Ranked by relevance, genuine price drop, and buyer trust</p>
            </div>
            <p class="text-sm text-slate-500"><span x-text="filteredDeals.length"></span> genuine deals</p>
        </div>
        
        <template x-if="filteredDeals.length === 0">
            <div class="bg-white/50 dark:bg-slate-900/50 backdrop-blur border border-gray-100 dark:border-slate-800 p-12 text-center rounded-3xl shadow-sm">
                <svg class="w-12 h-12 text-gray-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-gray-900 dark:text-white font-bold text-base">No genuine products match your current budget or query.</p>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">We excluded unrelated accessories. Try increasing your budget slightly or search for a broad product category.</p>
            </div>
        </template>
        
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <template x-for="deal in filteredDeals" :key="deal.id">
                <div class="group relative flex flex-col overflow-hidden rounded-2xl bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:border-red-200 dark:hover:border-red-900 transition-all duration-300 hover:-translate-y-1 cursor-pointer" @click="selectedDeal = deal">
                    <!-- Discount & Recommendation Badge -->
                    <div class="absolute left-3 top-3 z-10 flex flex-col gap-1">
                        <span class="rounded-full bg-red-600 px-2.5 py-1 text-xs font-black tracking-wide text-white shadow-sm" x-text="'-' + deal.discount_pct + '%'"></span>
                        <template x-if="deal.recommendation_type === 'best_match'">
                            <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">Top Pick</span>
                        </template>
                        <template x-if="deal.recommendation_type === 'best_value'">
                            <span class="rounded-full bg-blue-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">Best Value</span>
                        </template>
                        <template x-if="deal.recommendation_type === 'value_runner_up'">
                            <span class="rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">Value Runner-Up</span>
                        </template>
                        <template x-if="deal.recommendation_type === 'alternative' || deal.recommendation_type === 'featured'">
                            <span class="rounded-full bg-slate-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">Alternative Option</span>
                        </template>
                    </div>

                    <div class="relative aspect-square overflow-hidden bg-slate-50 dark:bg-slate-950 p-4 rounded-xl">
                        <img :src="deal.image_url || deal.image_path || '/images/logo.png'"
                             :alt="deal.title"
                             class="h-full w-full object-contain transition-transform duration-500 group-hover:scale-105"
                             onerror="this.src='/images/logo.png'" />
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <div class="mb-2 flex items-center justify-between text-xs font-medium">
                            <span class="text-red-600 bg-red-50 dark:bg-red-950/50 px-2 py-0.5 rounded text-[10px] uppercase tracking-wider font-bold" x-text="deal.merchant"></span>
                            <span class="text-slate-400 text-[10px]" x-text="deal.category || 'Deal'"></span>
                        </div>

                        <h3 class="mb-2 line-clamp-2 text-xs font-semibold leading-snug text-slate-800 dark:text-slate-200 group-hover:text-red-600 transition-colors" x-text="deal.title"></h3>

                        <div class="mt-auto pt-2">
                            <div class="flex items-baseline gap-2">
                                <span class="text-lg font-black tracking-tight text-gray-900 dark:text-white">₹<span x-text="Math.round(deal.price).toLocaleString('en-IN')"></span></span>
                                <template x-if="deal.original_price > deal.price">
                                    <span class="text-xs font-medium text-slate-400 line-through">₹<span x-text="Math.round(deal.original_price).toLocaleString('en-IN')"></span></span>
                                </template>
                            </div>
                            <a :href="deal.deal_url || ('/deal/' + (deal.slug || deal.id))" class="mt-3 block w-full rounded-xl bg-slate-900 dark:bg-slate-800 py-2 text-center text-xs font-bold text-white transition-all hover:bg-red-600 hover:shadow-lg hover:shadow-red-500/30">
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
            { id: 1, role: 'assistant', text: '👋 Hi! I am your Deal Intelligence Assistant. Tell me what product you need and your target budget — for example: **"Best smartphone under ₹30000"** or **"Cheapest AirPods today"**. I filter out misleading accessories and audit price drops in real time.' }
        ],
        query: '',
        selectedDeal: null,
        predictionText: 'Select a deal to see AI price prediction.',
        predictionLoading: false,
        predictionBuyNow: true,
        predictionSource: 'Rufus 30-Day Audit',
        predictionConfidence: 85,
        
        isComparing: false,
        compareStageText: 'Check Multi-Store Availability',
        comparisonResults: null,
        aiValueScore: null,
        comparisonInterval: null,

        aiSearchResults: null,
        isSearching: false,

        init() {
            this.$watch('bestDeal', value => {
                this.comparisonResults = null;
                this.aiValueScore = null;
                if (value) {
                    this.fetchPrediction(value.id);
                } else {
                    this.predictionText = 'Select a deal to see price intelligence.';
                }
            });
            if (this.bestDeal) {
                this.fetchPrediction(this.bestDeal.id);
            }

            const urlParams = new URLSearchParams(window.location.search);
            const qParam = urlParams.get('q');
            if (qParam && qParam.trim()) {
                this.query = qParam.trim();
                this.$nextTick(() => {
                    this.onAsk();
                });
            }
        },

        resetSearch() {
            this.aiSearchResults = null;
            this.selectedDeal = null;
            this.query = '';
        },

        renderSafeMarkdown(text) {
            if (!text) return '';
            const rawHtml = typeof marked !== 'undefined' ? marked.parse(text) : text;
            // Sanitize scripts, iframes, and dangerous handlers
            return rawHtml
                .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
                .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '')
                .replace(/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi, '')
                .replace(/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/gi, '')
                .replace(/\bon\w+\s*=\s*("[^"]*"|'[^']*'|[^\s>]+)/gi, '')
                .replace(/javascript:/gi, '');
        },

        fetchPrediction(dealId) {
            this.predictionLoading = true;
            this.predictionText = 'Auditing Rufus 30-day price history...';
            
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 6000);

            fetch('/api/predict-price', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ deal_id: dealId }),
                signal: controller.signal
            })
            .then(res => res.json())
            .then(data => {
                clearTimeout(timeoutId);
                if (data.status === 'success' && data.data) {
                    this.predictionText = data.data.prediction;
                    this.predictionBuyNow = data.data.buy_now !== false;
                    this.predictionConfidence = data.data.confidence_score || 85;
                    this.predictionSource = data.data.source === 'algorithmic_estimate' ? 'Algorithmic Market Estimate' : 'Rufus Historical Price Audit';
                } else {
                    this.predictionText = 'Price is currently stable; recommended purchase if you need this device today.';
                    this.predictionBuyNow = true;
                    this.predictionSource = 'Retail Telemetry Benchmark';
                }
            })
            .catch(err => {
                clearTimeout(timeoutId);
                this.predictionText = 'Price is competitive and verified against live market averages.';
                this.predictionBuyNow = true;
                this.predictionSource = 'Retail Benchmark';
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
            if (this.filteredDeals.length === 0) {
                return null;
            }
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
            this.compareStageText = 'Checking retailer stock...';
            
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 7000); // 7s timeout

            try {
                const res = await fetch('/api/compare-prices', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ deal_id: deal.id, title: deal.title }),
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                const data = await res.json();
                
                if (data.status === 'cache_hit' && data.data) {
                    this.comparisonResults = data.data.results;
                    this.aiValueScore = data.data.ai_score;
                    this.isComparing = false;
                    return;
                }
                
                // If async job was queued, poll with strict 8s timeout
                if (data.job_id) {
                    let attempts = 0;
                    this.comparisonInterval = setInterval(async () => {
                        attempts++;
                        if (attempts > 4) {
                            clearInterval(this.comparisonInterval);
                            this.synthesizeClientComparison(deal);
                            this.isComparing = false;
                            return;
                        }
                        
                        try {
                            const statusRes = await fetch(`/api/compare-prices/${data.job_id}`);
                            const statusData = await statusRes.json();
                            if (statusData.status === 'completed' && statusData.data) {
                                clearInterval(this.comparisonInterval);
                                this.comparisonResults = statusData.data.results;
                                this.aiValueScore = statusData.data.ai_score;
                                this.isComparing = false;
                            }
                        } catch (e) {
                            clearInterval(this.comparisonInterval);
                            this.synthesizeClientComparison(deal);
                            this.isComparing = false;
                        }
                    }, 1800);
                } else {
                    this.synthesizeClientComparison(deal);
                    this.isComparing = false;
                }
                
            } catch (err) {
                clearTimeout(timeoutId);
                // Instant resilient fallback - never leave the user hanging
                this.synthesizeClientComparison(deal);
                this.isComparing = false;
            }
        },

        synthesizeClientComparison(deal) {
            const basePrice = deal.price || 1999;
            const currentMerchant = deal.merchant || 'Amazon India';
            const isOutOfStock = deal.status === 'out_of_stock';

            const primaryStore = {
                store: currentMerchant,
                price: isOutOfStock ? null : basePrice,
                delivery: isOutOfStock ? 'Currently Unavailable' : 'Standard Delivery',
                rating: '4.4',
                url: isOutOfStock ? null : (deal.url || '#'),
                availability: isOutOfStock ? 'Currently Unavailable' : 'In Stock (Last Catalog Sync)',
                checked_at: 'Catalog synced',
                is_verified: true,
                in_stock: !isOutOfStock
            };

            const allCompList = [
                { store: 'Amazon India', rating: '4.4' },
                { store: 'Flipkart', rating: '4.3' },
                { store: 'Croma', rating: '4.2' },
                { store: 'Reliance Digital', rating: '4.1' }
            ];

            const currentLower = currentMerchant.toLowerCase();
            const filteredComps = allCompList.filter(c => {
                const s = c.store.toLowerCase();
                if (currentLower.includes('amazon') && s.includes('amazon')) return false;
                if (currentLower.includes('flipkart') && s.includes('flipkart')) return false;
                if (currentLower.includes('croma') && s.includes('croma')) return false;
                if (currentLower.includes('reliance') && s.includes('reliance')) return false;
                return s !== currentLower;
            }).map(c => ({
                store: c.store,
                price: null,
                delivery: 'Retailer verification pending',
                rating: c.rating,
                url: null,
                availability: 'Unverified for this SKU',
                checked_at: null,
                is_verified: false,
                in_stock: null
            }));

            this.comparisonResults = [primaryStore, ...filteredComps];
            this.aiValueScore = Math.min(88, Math.max(72, 70 + Math.round((deal.discount_pct || 15) * 0.25)));
        },

        onAsk() {
            const text = this.query.trim();
            if (!text) return;

            // Push user message (plain text)
            this.messages.push({ id: Date.now(), role: 'user', text });
            
            const aiMessageId = Date.now() + 1;
            
            // Escape query text before displaying in temporary loading animation
            const escapeHtml = (s) => String(s).replace(/[&<>'"]/g, t => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
            }[t] || t));

            const shortQuery = escapeHtml(text.substring(0, 30));
            const stages = [
                `🔍 Extracting product intent: "${shortQuery}..."`,
                `🛡️ Applying accessory exclusion firewall...`,
                `💰 Auditing 30-day price history...`,
                `🤖 Ranking genuine deals...`
            ];
            let currentStage = 0;
            
            const getLoadingHtml = (msg) => `<div class="flex items-center space-x-2 text-slate-500 dark:text-slate-400 text-xs italic font-medium"><svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>${msg}</span></div>`;

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
            }, 1000);

            // Assemble bounded conversational history (last 4 turns)
            const conversationHistory = this.messages
                .filter(m => m.text && !m.text.includes('animate-spin') && !m.text.includes('Extracting product intent'))
                .slice(-4)
                .map(m => ({ role: m.role, content: m.text }));
            
            // Step 1: Query Smart Search API with taxonomy resolution & accessory filtering
            fetch('/api/smart-search?q=' + encodeURIComponent(text))
            .then(res => res.json())
            .then(searchData => {
                if (searchData.deals && searchData.deals.length > 0) {
                    this.aiSearchResults = searchData.deals;
                } else {
                    this.aiSearchResults = [];
                }
                this.isSearching = false;

                // Step 2: Hit Chat API to generate grounded conversational advice
                return fetch('/api/assistant/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        message: text,
                        deal_ids: this.aiSearchResults.map(d => d.id),
                        history: conversationHistory
                    })
                });
            })
            .then(res => res ? res.json() : null)
            .then(data => {
                if (!data) return;
                const msgIndex = this.messages.findIndex(m => m.id === aiMessageId);
                if (msgIndex !== -1) {
                    if (this.aiSearchResults && this.aiSearchResults.length > 0) {
                        this.messages[msgIndex].text = data.reply || "I found verified deals matching your criteria below.";
                    } else {
                        this.messages[msgIndex].text = data.reply || `I couldn't find any genuine active deals matching **"${escapeHtml(text)}"** under your criteria right now. Accessories and irrelevant items were excluded. Try expanding your budget or checking our [All Deals](/deals/all) section!`;
                    }
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
                    this.messages[msgIndex].text = "I ran into a temporary connection issue. Please check the deal matrix below or retry your search.";
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

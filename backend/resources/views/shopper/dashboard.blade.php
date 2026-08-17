@extends('layouts.app')
@section('title', 'My Dashboard')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="{ activeTab: 'deals' }">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">Welcome, {{ explode(' ', $user->name)[0] }}</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2">Manage your personalized deals, alerts, and watchlists.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold px-4 py-2 rounded-xl shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Logout
                </button>
            </form>
        </div>
    </div>

    <!-- KPIs Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-1">Saved Deals</div>
            <div class="text-3xl font-black text-slate-900 dark:text-white">{{ count($savedDeals) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-1">Watchlists</div>
            <div class="text-3xl font-black text-slate-900 dark:text-white">{{ count($watchlists) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-1">Alerts Triggered</div>
            <div class="text-3xl font-black text-slate-900 dark:text-white">{{ $triggeredAlerts }} <span class="text-lg text-slate-400">/ {{ count($priceAlerts) }}</span></div>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 shadow-sm text-white">
            <div class="text-sm font-bold text-emerald-100 uppercase tracking-wider mb-1">Estimated Savings</div>
            <div class="text-3xl font-black">₹{{ number_format($estimatedSavings) }}</div>
        </div>
    </div>

    <!-- Recommendations Engine (Horizontal Scroll) -->
    @if(isset($recommendedDeals) && $recommendedDeals->isNotEmpty())
    <div class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Recommended for You</h2>
        </div>
        <div class="flex space-x-4 overflow-x-auto pb-4 snap-x">
            @foreach($recommendedDeals as $deal)
            <a href="{{ route('deal.show', $deal->slug) }}" class="snap-start flex-none w-64 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-3 shadow-sm hover:shadow-md transition-shadow group">
                <div class="aspect-video rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center p-2 mb-3 overflow-hidden">
                    <img src="{{ $deal->image_url }}" alt="{{ $deal->title }}" class="max-w-full max-h-full object-contain mix-blend-multiply dark:mix-blend-normal group-hover:scale-105 transition-transform duration-500" onerror="this.src='{{ asset('images/logo.png') }}'">
                </div>
                <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm line-clamp-2 leading-tight mb-2">{{ $deal->title }}</h3>
                <div class="flex items-end justify-between">
                    <div class="text-lg font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($deal->discounted_price) }}</div>
                    @if($deal->original_price > $deal->discounted_price)
                        <div class="text-xs text-rose-600 font-bold bg-rose-50 dark:bg-rose-900/30 px-1.5 py-0.5 rounded">
                            {{ round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) }}% OFF
                        </div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recently Viewed (Horizontal Scroll) -->
    @if(isset($recentlyViewedDeals) && $recentlyViewedDeals->isNotEmpty())
    <div class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Recently Viewed</h2>
        </div>
        <div class="flex space-x-4 overflow-x-auto pb-4 snap-x opacity-90">
            @foreach($recentlyViewedDeals as $deal)
            <a href="{{ route('deal.show', $deal->slug) }}" class="snap-start flex-none w-48 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-3 shadow-sm hover:shadow-md transition-shadow group">
                <div class="aspect-square rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center p-2 mb-2 overflow-hidden">
                    <img src="{{ $deal->image_url }}" alt="{{ $deal->title }}" class="max-w-full max-h-full object-contain mix-blend-multiply dark:mix-blend-normal group-hover:scale-105 transition-transform duration-500" onerror="this.src='{{ asset('images/logo.png') }}'">
                </div>
                <h3 class="font-bold text-slate-800 dark:text-slate-200 text-xs line-clamp-2 leading-tight mb-1">{{ $deal->title }}</h3>
                <div class="text-sm font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($deal->discounted_price) }}</div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Tabs -->
    <div class="flex space-x-1 bg-slate-100 dark:bg-slate-800/50 p-1 rounded-2xl mb-8 overflow-x-auto">
        <button @click="activeTab = 'deals'" :class="{ 'bg-white dark:bg-slate-700 shadow-sm text-indigo-600 dark:text-indigo-400': activeTab === 'deals', 'text-slate-600 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700/50': activeTab !== 'deals' }" class="flex-1 whitespace-nowrap px-4 py-2.5 text-sm font-bold rounded-xl transition-all">
            My Deals
        </button>
        <button @click="activeTab = 'watchlist'" :class="{ 'bg-white dark:bg-slate-700 shadow-sm text-indigo-600 dark:text-indigo-400': activeTab === 'watchlist', 'text-slate-600 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700/50': activeTab !== 'watchlist' }" class="flex-1 whitespace-nowrap px-4 py-2.5 text-sm font-bold rounded-xl transition-all">
            Watchlist
        </button>
        <button @click="activeTab = 'alerts'" :class="{ 'bg-white dark:bg-slate-700 shadow-sm text-indigo-600 dark:text-indigo-400': activeTab === 'alerts', 'text-slate-600 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700/50': activeTab !== 'alerts' }" class="flex-1 whitespace-nowrap px-4 py-2.5 text-sm font-bold rounded-xl transition-all">
            Price Alerts
        </button>
        <button @click="activeTab = 'timeline'" :class="{ 'bg-white dark:bg-slate-700 shadow-sm text-indigo-600 dark:text-indigo-400': activeTab === 'timeline', 'text-slate-600 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700/50': activeTab !== 'timeline' }" class="flex-1 whitespace-nowrap px-4 py-2.5 text-sm font-bold rounded-xl transition-all">
            Timeline
        </button>
        <button @click="activeTab = 'profile'" :class="{ 'bg-white dark:bg-slate-700 shadow-sm text-indigo-600 dark:text-indigo-400': activeTab === 'profile', 'text-slate-600 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700/50': activeTab !== 'profile' }" class="flex-1 whitespace-nowrap px-4 py-2.5 text-sm font-bold rounded-xl transition-all">
            Settings
        </button>
    </div>

    <!-- Tab Contents -->
    <div class="space-y-6">
        
        <!-- Saved Deals -->
        <div x-show="activeTab === 'deals'" class="animate-fade-in-up">
            <h2 class="text-2xl font-black mb-6">Saved Deals</h2>
            @if($savedDeals->isEmpty())
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-12 text-center shadow-sm">
                    <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    <h3 class="text-xl font-bold text-slate-700 dark:text-slate-200">No saved deals yet</h3>
                    <p class="text-slate-500 mt-2">When you find a deal you like, click the heart icon to save it here.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($savedDeals as $deal)
                        <div class="group relative bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-4 shadow-sm hover:shadow-xl transition-all">
                            <form action="{{ route('deal.save', $deal->id) }}" method="POST" class="absolute top-4 right-4 z-10">
                                @csrf
                                <button type="submit" class="p-2 bg-rose-50 dark:bg-rose-900/30 text-rose-500 rounded-full hover:bg-rose-100 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                </button>
                            </form>
                            <a href="{{ route('deal.show', $deal->slug) }}" class="block">
                                <div class="aspect-[4/3] rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center p-4 mb-4 overflow-hidden">
                                    <img src="{{ $deal->image_url }}" alt="{{ $deal->title }}" class="max-w-full max-h-full object-contain mix-blend-multiply dark:mix-blend-normal group-hover:scale-105 transition-transform duration-500" onerror="this.src='{{ asset('images/logo.png') }}'">
                                </div>
                                <h3 class="font-bold text-slate-800 dark:text-slate-200 line-clamp-2 leading-tight mb-2">{{ $deal->title }}</h3>
                                <div class="flex items-end justify-between mt-auto">
                                    <div>
                                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($deal->discounted_price) }}</div>
                                        @if($deal->original_price > $deal->discounted_price)
                                            <div class="text-sm text-slate-400 line-through">₹{{ number_format($deal->original_price) }}</div>
                                        @endif
                                    </div>
                                    @if($deal->original_price > $deal->discounted_price)
                                        <span class="bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 text-xs font-black px-2 py-1 rounded-lg">
                                            {{ round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) }}% OFF
                                        </span>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Watchlist -->
        <div x-show="activeTab === 'watchlist'" class="animate-fade-in-up" style="display: none;">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black">My Watchlist</h2>
            </div>
            
            @if($watchlists->isEmpty())
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-12 text-center shadow-sm">
                    <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <h3 class="text-xl font-bold text-slate-700 dark:text-slate-200">Your Watchlist is empty</h3>
                    <p class="text-slate-500 mt-2">Follow brands or categories to get personalized deal recommendations.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($watchlists as $item)
                        @if($item->watchable)
                            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-3">
                                    @php
                                        $heat = $item->intelligence['heat_score'] ?? 0;
                                    @endphp
                                    @if($heat >= 80)
                                        <div class="bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 text-xs font-black px-2 py-1 rounded-lg">🔥 Heat: {{ $heat }}</div>
                                    @elseif($heat >= 40)
                                        <div class="bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 text-xs font-black px-2 py-1 rounded-lg">🟡 Heat: {{ $heat }}</div>
                                    @else
                                        <div class="bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-black px-2 py-1 rounded-lg">🟢 Heat: {{ $heat }}</div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 mb-5">
                                    <div class="w-12 h-12 rounded-xl {{ $item->watchable_type === 'App\Models\Brand' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' }} flex items-center justify-center font-black text-xl">
                                        {{ substr($item->watchable->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-xl font-bold text-slate-900 dark:text-slate-100 leading-tight">{{ $item->watchable->name }}</div>
                                        <div class="text-xs text-slate-500 uppercase tracking-wider font-semibold">{{ class_basename($item->watchable_type) }}</div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-5 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl">
                                    <div>
                                        <div class="text-xs text-slate-500 font-semibold mb-0.5">Deals Today</div>
                                        <div class="font-black text-slate-900 dark:text-white">{{ $item->intelligence['today_deals'] ?? 0 }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 font-semibold mb-0.5">Avg Discount</div>
                                        <div class="font-black text-indigo-600 dark:text-indigo-400">{{ $item->intelligence['avg_discount'] ?? 0 }}%</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 font-semibold mb-0.5">Best Discount</div>
                                        <div class="font-black text-emerald-600 dark:text-emerald-400">{{ $item->intelligence['best_discount'] ?? 0 }}%</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 font-semibold mb-0.5">Total Deals</div>
                                        <div class="font-black text-slate-900 dark:text-white">{{ $item->intelligence['deal_count'] ?? 0 }}</div>
                                    </div>
                                </div>
                                
                                <form action="{{ route('watchlist.toggle') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ class_basename($item->watchable_type) }}">
                                    <input type="hidden" name="id" value="{{ $item->watchable_id }}">
                                    <button type="submit" class="w-full text-center py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold hover:border-rose-500 hover:text-rose-500 transition-colors">
                                        Unwatch
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Price Alerts -->
        <div x-show="activeTab === 'alerts'" class="animate-fade-in-up" style="display: none;">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black">Price Alerts</h2>
                <button @click="$dispatch('open-alert-modal')" class="bg-indigo-600 text-white font-bold px-4 py-2 rounded-xl shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-colors">
                    + New Alert
                </button>
            </div>
            
            @if($priceAlerts->isEmpty())
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-12 text-center shadow-sm">
                    <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <h3 class="text-xl font-bold text-slate-700 dark:text-slate-200">No active alerts</h3>
                    <p class="text-slate-500 mt-2">Set up a price alert and we'll notify you when a product drops below your target price.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($priceAlerts as $alert)
                        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm relative overflow-hidden">
                            @if($alert->is_fulfilled)
                                <div class="absolute top-0 right-0 w-20 h-20">
                                    <div class="absolute transform rotate-45 bg-emerald-500 text-white text-[10px] font-black py-1 right-[-40px] top-[25px] w-[170px] text-center tracking-wider">TRIGGERED</div>
                                </div>
                            @endif
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">Target Keyword</div>
                                    <div class="text-xl font-black text-slate-900 dark:text-slate-100 mb-4">{{ $alert->keyword }}</div>
                                </div>
                            </div>
                            <div class="flex items-end justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">Target Price</div>
                                    <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400">₹{{ number_format($alert->target_price) }}</div>
                                </div>
                                <form action="{{ route('price-alerts.destroy', $alert->id) }}" method="POST" onsubmit="return confirm('Delete this alert?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-500 bg-slate-50 hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-rose-900/30 p-2 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                            @if($alert->is_fulfilled)
                            <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-500 uppercase">Trust Score</span>
                                    <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">96 / 100</span>
                                </div>
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-xs font-bold text-slate-500 uppercase">Deal Confidence</span>
                                    <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">98% Match</span>
                                </div>
                                <button class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-black py-2.5 rounded-xl text-center shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                                    BUY NOW
                                </button>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Activity Timeline -->
        <div x-show="activeTab === 'timeline'" class="animate-fade-in-up" style="display: none;">
            <div class="max-w-3xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-8 shadow-sm">
                <h2 class="text-2xl font-black mb-8">Activity Timeline</h2>
                
                @if(isset($timeline) && $timeline->isNotEmpty())
                    <div class="relative border-l-2 border-slate-100 dark:border-slate-800 ml-4 space-y-8">
                        @foreach($timeline as $event)
                        <div class="relative pl-8">
                            <div class="absolute left-[-9px] top-0.5 w-4 h-4 rounded-full bg-white dark:bg-slate-900 border-2 border-indigo-500"></div>
                            
                            <div class="text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">{{ $event->created_at->diffForHumans() }}</div>
                            <div class="text-lg font-bold text-slate-900 dark:text-white mb-1">
                                {{ ucwords(str_replace('_', ' ', $event->interaction_type)) }}
                            </div>
                            
                            @if(isset($event->metadata['title']))
                                <div class="text-slate-600 dark:text-slate-300 font-medium">
                                    {{ $event->metadata['title'] }}
                                </div>
                            @endif
                            @if(isset($event->metadata['price']))
                                <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                                    ₹{{ number_format($event->metadata['price']) }} 
                                    @if(isset($event->metadata['discount']))
                                    <span class="text-rose-500 bg-rose-50 dark:bg-rose-900/30 px-1.5 py-0.5 rounded ml-1">{{ $event->metadata['discount'] }}% OFF</span>
                                    @endif
                                </div>
                            @endif
                            
                            <div class="text-xs text-slate-400 mt-2 font-semibold">
                                Source: {{ ucfirst($event->source) }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-slate-500 py-10">No recent activity found.</div>
                @endif
            </div>
        </div>

        <!-- Profile Settings -->
        <div x-show="activeTab === 'profile'" class="animate-fade-in-up" style="display: none;">
            <div class="max-w-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-8 shadow-sm">
                <h2 class="text-2xl font-black mb-6">Profile Settings</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Name</label>
                        <input type="text" value="{{ $user->name }}" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email Address</label>
                        <input type="email" value="{{ $user->email }}" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" readonly>
                    </div>
                    
                    <div class="pt-6 border-t border-slate-100 dark:border-slate-800 mt-6">
                        <h3 class="text-lg font-bold text-rose-600 mb-2">Danger Zone</h3>
                        <p class="text-sm text-slate-500 mb-4">Permanently delete your account and all associated data.</p>
                        <form action="{{ route('profile.destroy') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-rose-100 text-rose-700 hover:bg-rose-200 font-bold px-4 py-2 rounded-xl transition-colors">
                                Delete Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .animate-fade-in-up {
        animation: fadeInUp 0.4s ease-out forwards;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@extends('admin.layout')

@section('title', 'Admin Dashboard & User Intelligence Center')

@section('content')
<!-- Top Level System Insights Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
    <!-- Queue Backlog Card -->
    <div class="glass-panel rounded-3xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300 shadow-lg">
        <div class="absolute top-0 right-0 -mr-6 -mt-6 w-24 h-24 rounded-full bg-red-500/10 blur-xl group-hover:bg-red-500/20 transition-colors"></div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-500 tracking-wide uppercase">Queue Backlog</h3>
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                <i data-lucide="layers" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-baseline">
            <p class="text-4xl font-black text-slate-800 tracking-tight">{{ $queueCount }}</p>
            <span class="ml-2 text-sm font-medium text-slate-500">jobs pending</span>
        </div>
    </div>
    
    <!-- Failed Jobs Card -->
    <div class="glass-panel rounded-3xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300 shadow-lg">
        <div class="absolute top-0 right-0 -mr-6 -mt-6 w-24 h-24 rounded-full bg-red-500/10 blur-xl group-hover:bg-red-500/20 transition-colors"></div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-500 tracking-wide uppercase">Failed Jobs</h3>
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-baseline">
            <p class="text-4xl font-black text-red-600 tracking-tight">{{ $failedJobs }}</p>
            <span class="ml-2 text-sm font-medium text-slate-500">requires attention</span>
        </div>
    </div>

    <!-- Platform Clicks Card -->
    <div class="glass-panel rounded-3xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300 shadow-lg">
        <div class="absolute top-0 right-0 -mr-6 -mt-6 w-24 h-24 rounded-full bg-emerald-500/10 blur-xl group-hover:bg-emerald-500/20 transition-colors"></div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-500 tracking-wide uppercase">Platform Clicks</h3>
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                <i data-lucide="mouse-pointer-click" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-baseline">
            <p class="text-4xl font-black text-slate-800 tracking-tight">{{ number_format($metrics['total_clicks'] ?? 0) }}</p>
            <span class="ml-2 text-sm font-medium text-slate-500">all time</span>
        </div>
    </div>
</div>

<!-- Dedicated User Intelligence Center (UIC) Section -->
<div class="glass-panel rounded-3xl p-8 mb-10 shadow-lg border border-red-100/60 dark:border-slate-700">
    <div class="flex items-center justify-between mb-6 border-b border-slate-200 dark:border-slate-700 pb-4">
        <div>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="brain-circuit" class="w-6 h-6 text-red-600"></i>
                User Intelligence Center (UIC)
            </h3>
            <p class="text-sm text-slate-500 mt-1">Real-time user behavior, search trends, and visitor session metrics (Last 30 Days)</p>
        </div>
        <span class="bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Active Engine</span>
    </div>

    <!-- UIC Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white/80 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Visitors</h4>
            <p class="text-3xl font-black text-slate-800 dark:text-white mt-1">{{ number_format($stats['total_visitors'] ?? 0) }}</p>
        </div>
        <div class="bg-white/80 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Sessions</h4>
            <p class="text-3xl font-black text-slate-800 dark:text-white mt-1">{{ number_format($stats['total_sessions'] ?? 0) }}</p>
        </div>
        <div class="bg-white/80 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Affiliate Clicks</h4>
            <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($stats['total_affiliate_clicks'] ?? 0) }}</p>
        </div>
        <div class="bg-white/80 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Searches</h4>
            <p class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-1">{{ number_format($stats['total_searches'] ?? 0) }}</p>
        </div>
    </div>

    <!-- UIC Breakdowns Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Searches -->
        <div class="bg-white/80 dark:bg-slate-800/80 rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    Top User Searches
                </h4>
            </div>
            <ul class="divide-y divide-slate-100 dark:divide-slate-700 max-h-60 overflow-y-auto">
                @forelse($topSearches as $search)
                    <li class="px-5 py-3 flex justify-between items-center text-sm">
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $search->search_query ?? $search->search_term ?? 'N/A' }}</span>
                        <span class="bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 px-2.5 py-0.5 rounded-full text-xs font-bold">{{ $search->count }}</span>
                    </li>
                @empty
                    <li class="px-5 py-4 text-slate-400 text-center text-sm italic">No searches recorded yet.</li>
                @endforelse
            </ul>
        </div>

        <!-- Recent Affiliate Conversions -->
        <div class="bg-white/80 dark:bg-slate-800/80 rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="external-link" class="w-4 h-4 text-slate-400"></i>
                    Recent Affiliate Conversions
                </h4>
            </div>
            <ul class="divide-y divide-slate-100 dark:divide-slate-700 max-h-60 overflow-y-auto">
                @forelse($recentClicks as $click)
                    <li class="px-5 py-3 flex justify-between items-start text-sm">
                        <div>
                            <p class="font-medium text-slate-800 dark:text-white line-clamp-1">{{ $click->deal->title ?? 'Direct Link' }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $click->created_at ? $click->created_at->diffForHumans() : 'Recently' }} via {{ $click->deal->merchant->name ?? 'Amazon India' }}</p>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-4 text-slate-400 text-center text-sm italic">No affiliate conversions logged yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<!-- Quick System Actions -->
<div class="glass-panel rounded-3xl p-8 mb-10 shadow-lg">
    <div class="flex items-center justify-between mb-6 border-b border-slate-200 pb-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">System Actions</h3>
            <p class="text-sm text-slate-500 mt-1">Manage background workers and system health</p>
        </div>
        <i data-lucide="cpu" class="w-8 h-8 text-slate-300"></i>
    </div>
    
    <div class="flex flex-wrap gap-4">
        <form action="{{ route('admin.queue.work') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 text-white font-medium rounded-xl shadow-md hover:shadow-xl hover:from-red-700 hover:to-rose-700 transition-all transform hover:-translate-y-0.5">
                <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                Run Queue Worker
            </button>
        </form>
        <form action="{{ route('admin.queue.clear') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center px-6 py-3 bg-white border border-slate-200 text-slate-700 font-medium rounded-xl shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all transform hover:-translate-y-0.5">
                <i data-lucide="trash-2" class="w-4 h-4 mr-2 text-red-500"></i>
                Clear Failed Jobs
            </button>
        </form>
    </div>
</div>

<!-- System Stats & Performance Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    <!-- Click Stats -->
    <div class="glass-panel rounded-3xl p-8 shadow-lg">
        <div class="flex items-center justify-between mb-6 border-b border-slate-200 pb-4">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Click Stats</h3>
                <p class="text-sm text-slate-500 mt-1">Affiliate clicks breakdown</p>
            </div>
            <i data-lucide="bar-chart-2" class="w-8 h-8 text-slate-300"></i>
        </div>
        
        <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
            @forelse($clickStats as $stat)
                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-slate-100 transition-colors">
                    <div>
                        <p class="font-bold text-slate-800">{{ $stat->name }}</p>
                        <p class="text-xs text-slate-500">{{ $stat->domain }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-black text-emerald-600">{{ $stat->click_count }}</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Clicks</p>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-slate-500 italic bg-slate-50 rounded-xl">
                    No clicks recorded yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Top Clicked Products -->
    <div class="glass-panel rounded-3xl p-8 shadow-lg">
        <div class="flex items-center justify-between mb-6 border-b border-slate-200 pb-4">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Top Products</h3>
                <p class="text-sm text-slate-500 mt-1">Most clicked deals</p>
            </div>
            <i data-lucide="mouse-pointer-2" class="w-8 h-8 text-slate-300"></i>
        </div>
        
        <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
            @forelse($topProducts as $product)
                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-slate-100 transition-colors gap-3">
                    <img src="{{ $product->image_path ? asset($product->image_path) : 'https://picsum.photos/seed/deal-'.$product->id.'/100/100' }}" alt="Product" class="w-12 h-12 rounded-lg object-cover flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-800 text-sm truncate" title="{{ $product->title }}">{{ $product->title }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-black text-emerald-600">{{ $product->click_count }}</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Clicks</p>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-slate-500 italic bg-slate-50 rounded-xl">
                    No product clicks recorded yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Product Volume by Source -->
    <div class="glass-panel rounded-3xl p-8 shadow-lg flex flex-col">
        <div class="flex items-center justify-between mb-6 border-b border-slate-200 pb-4">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Product Volume by Source</h3>
                <p class="text-sm text-slate-500 mt-1">Total products tracked per merchant</p>
            </div>
            <i data-lucide="database" class="w-8 h-8 text-slate-300"></i>
        </div>
        
        <div class="space-y-2 max-h-80 overflow-y-auto pr-2">
            @forelse($scraperStats['source_counts'] ?? [] as $source)
                <div class="flex items-center justify-between p-3 rounded-lg border border-slate-100 bg-slate-50 hover:bg-slate-100 transition-colors">
                    <span class="text-sm font-medium text-slate-700">{{ $source->name ?? 'Merchant (' . ($source->merchant_id ?? '') . ')' }}</span>
                    <span class="text-sm font-black text-slate-800">{{ number_format($source->total) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500 italic text-center p-4">No products found.</p>
            @endforelse
        </div>
    </div>

    <!-- Monetization Stats -->
    <div class="glass-panel rounded-3xl p-8 shadow-lg lg:col-span-3">
        <div class="flex items-center justify-between mb-6 border-b border-slate-200 pb-4">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Category Monetization & Clicks</h3>
                <p class="text-sm text-slate-500 mt-1">Potential earnings tracking based on a 3% conversion rate (For Admin Only)</p>
            </div>
            <i data-lucide="indian-rupee" class="w-8 h-8 text-slate-300"></i>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">Category</th>
                        <th class="px-4 py-3 text-right">Commission Rate</th>
                        <th class="px-4 py-3 text-right">Clicks</th>
                        <th class="px-4 py-3 text-right rounded-r-lg">Estimated Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categoryStats as $stat)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 font-semibold text-slate-800">{{ $stat->name }}</td>
                            <td class="px-4 py-4 text-right">
                                <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-md text-xs font-bold">{{ $stat->commission_rate }}%</span>
                            </td>
                            <td class="px-4 py-4 text-right font-black text-slate-700">{{ $stat->click_count }}</td>
                            <td class="px-4 py-4 text-right font-black text-emerald-600">₹{{ number_format($stat->estimated_revenue ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500 italic">No category clicks recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

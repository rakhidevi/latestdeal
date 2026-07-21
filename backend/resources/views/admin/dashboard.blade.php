@extends('admin.layout')

@section('title', 'System Insights')

@section('content')
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

    <!-- Total Clicks Card -->
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

<!-- Quick Actions -->
<div class="glass-panel rounded-3xl p-8 shadow-lg">
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

<!-- Second Row: Stats and Scraper Monitor -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8 mb-10">
    
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
            @forelse($scraperStats['source_counts'] as $source)
                <div class="flex items-center justify-between p-3 rounded-lg border border-slate-100 bg-slate-50 hover:bg-slate-100 transition-colors">
                    <span class="text-sm font-medium text-slate-700">{{ $source->merchant->name ?? 'Unknown (' . $source->merchant_id . ')' }}</span>
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

<script>
    function scraperTerminal() {
        return {
            isRunning: false,
            logs: [],
            pollInterval: null,
            scrapeUrlInput: '',
            isSubmitting: false,
            init() {
                this.fetchStatus();
                // Poll every 1 second
                this.pollInterval = setInterval(() => {
                    this.fetchStatus();
                }, 1000);
            },
            async fetchStatus() {
                try {
                    const response = await fetch('{{ route("admin.scraper.status") }}');
                    if (response.ok) {
                        const data = await response.json();
                        this.isRunning = data.running;
                        
                        // Check if new logs came in, if so, auto-scroll to bottom
                        const oldLength = this.logs.length;
                        this.logs = data.logs || [];
                        
                        if (this.logs.length !== oldLength && this.logs.length > 0) {
                            this.$nextTick(() => {
                                const el = document.getElementById('terminal-output');
                                if (el) {
                                    // Only auto-scroll if we're already near the bottom to allow manual scrolling up
                                    if (el.scrollHeight - el.scrollTop <= el.clientHeight + 100) {
                                        el.scrollTop = el.scrollHeight;
                                    }
                                }
                            });
                        }
                    }
                } catch (e) {
                    console.error("Failed to fetch scraper status:", e);
                }
            },
            async startScraper() {
                try {
                    await fetch('{{ route("admin.scraper.start") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                    this.fetchStatus();
                } catch (e) {
                    console.error("Failed to start scraper", e);
                }
            },
            async stopScraper() {
                try {
                    await fetch('{{ route("admin.scraper.stop") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                    this.fetchStatus();
                } catch (e) {
                    console.error("Failed to stop scraper", e);
                }
            },
            async submitScrape() {
                if (!this.scrapeUrlInput) return;
                this.isSubmitting = true;
                try {
                    await fetch('{{ route("admin.scraper.scrape") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ url: this.scrapeUrlInput })
                    });
                    this.scrapeUrlInput = '';
                } catch (e) {
                    console.error("Failed to submit scrape", e);
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
</script>
</div>

@endsection

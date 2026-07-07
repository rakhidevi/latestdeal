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
                        <p class="text-lg font-black text-red-600">{{ number_format($stat->click_count) }}</p>
                        <p class="text-xs text-slate-500">clicks</p>
                    </div>
                </div>
            @empty
                <div class="text-center p-6 text-slate-500">
                    <p>No click data available yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Scraper Monitoring -->
    <div class="glass-panel rounded-3xl p-8 shadow-lg flex flex-col">
        <div class="flex items-center justify-between mb-6 border-b border-slate-200 pb-4">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Scraper Control Center</h3>
                <p class="text-sm text-slate-500 mt-1">Realtime ingestion metrics & live terminal</p>
            </div>
            <i data-lucide="activity" class="w-8 h-8 text-slate-300"></i>
        </div>
        
        <div class="mb-6">
            <div class="p-6 bg-red-50 rounded-xl border border-red-100 flex items-center space-x-4">
                <div class="p-3 bg-white rounded-lg shadow-sm text-red-600">
                    <i data-lucide="settings" class="w-6 h-6"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900">Deal Approval Pipeline</h4>
                    <p class="text-sm text-gray-500">Require manual review before deals go live.</p>
                </div>
                <div>
                    <!-- Toggle Switch -->
                    <form action="{{ route('admin.settings.toggle') }}" method="POST">
                        @csrf
                        <input type="hidden" name="key" value="deal_approval_pipeline">
                        <input type="hidden" name="value" value="{{ $pipelineEnabled ? 'disabled' : 'enabled' }}">
                        <button type="submit" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 {{ $pipelineEnabled ? 'bg-red-600' : 'bg-gray-200' }}" role="switch" aria-checked="{{ $pipelineEnabled ? 'true' : 'false' }}">
                            <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200 {{ $pipelineEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6 text-center">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">1H Ingest</p>
                <p class="text-2xl font-black text-slate-800">{{ $scraperStats['ingested_1h'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">24H Ingest</p>
                <p class="text-2xl font-black text-slate-800">{{ $scraperStats['ingested_24h'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-red-50 border border-red-100">
                <p class="text-xs text-red-500 uppercase font-bold tracking-wider mb-1">24H Publish</p>
                <p class="text-2xl font-black text-red-600">{{ $scraperStats['published_24h'] }}</p>
            </div>
        </div>

        <h4 class="text-sm font-bold text-slate-700 mb-3">Live Terminal</h4>
        <div x-data="scraperTerminal()" x-init="init()" class="flex-1 flex flex-col">
            <!-- Controls -->
            <div class="flex items-center justify-between bg-gray-800 text-gray-200 p-2 rounded-t-lg border-b border-gray-700">
                <div class="flex items-center space-x-2 px-2">
                    <span class="relative flex h-3 w-3">
                        <span x-show="isRunning" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3" :class="isRunning ? 'bg-green-500' : 'bg-gray-500'"></span>
                    </span>
                    <span class="text-xs font-mono font-bold" x-text="isRunning ? 'Running' : 'Idle'"></span>
                </div>
                <div class="flex space-x-2">
                    <button @click="startScraper" x-show="!isRunning" class="text-xs bg-green-600 hover:bg-green-500 text-white px-3 py-1 rounded shadow transition">Start</button>
                    <button @click="stopScraper" x-show="isRunning" class="text-xs bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded shadow transition">Stop</button>
                </div>
            </div>

            <!-- Manual Scrape Form -->
            <div class="bg-gray-800 p-3 border-b border-gray-700 flex space-x-2">
                <input type="url" x-model="scrapeUrlInput" placeholder="Enter Amazon/Flipkart URL to scrape..." class="flex-1 bg-gray-900 border border-gray-600 text-white text-xs rounded px-3 py-2 focus:outline-none focus:border-green-500">
                <button @click="submitScrape" :disabled="!isRunning || isSubmitting" class="text-xs bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white px-4 py-2 rounded shadow transition flex items-center">
                    <span x-show="!isSubmitting">Scrape</span>
                    <span x-show="isSubmitting">Loading...</span>
                </button>
            </div>
            
            <!-- Logs output -->
            <div class="bg-gray-900 text-green-400 font-mono text-xs p-4 h-64 overflow-y-auto rounded-b-lg border border-gray-800 shadow-inner" id="terminal-output">
                <template x-for="(log, index) in logs" :key="index">
                    <div x-text="log" class="whitespace-pre-wrap break-words"></div>
                </template>
                <div x-show="logs.length === 0" class="text-gray-500 italic">Waiting for logs...</div>
            </div>
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

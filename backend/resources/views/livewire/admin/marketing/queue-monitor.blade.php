<div wire:poll.10s>
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Queue Monitor</h2>
            <p class="text-sm text-slate-500 mt-1">Live visibility into background workers and message throughput.</p>
        </div>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors shadow-sm flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Restart Workers
            </button>
            <button class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700 transition-colors shadow-sm">
                Clear Failed
            </button>
        </div>
    </div>

    <!-- Active Metrics -->
    <div class="mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Queue Size -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-blue-50 opacity-50"></div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    @if($metrics['jobs_waiting'] > 1000)
                        <x-status-badge status="warning" />
                    @else
                        <x-status-badge status="healthy" />
                    @endif
                </div>
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1 relative z-10">Queue Size</h3>
                <div class="text-3xl font-black text-slate-800 relative z-10">{{ number_format($metrics['jobs_waiting']) }}</div>
            </div>

            <!-- Failed Jobs -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-red-50 opacity-50"></div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    @if($metrics['jobs_failed'] > 0)
                        <x-status-badge status="error" />
                    @else
                        <x-status-badge status="healthy" />
                    @endif
                </div>
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1 relative z-10">Failed Jobs</h3>
                <div class="text-3xl font-black text-slate-800 relative z-10">{{ number_format($metrics['jobs_failed']) }}</div>
            </div>

            <!-- Throughput -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-green-50 opacity-50"></div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-md">Live</span>
                </div>
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1 relative z-10">Throughput / Min</h3>
                <div class="text-3xl font-black text-slate-800 relative z-10">~{{ number_format($metrics['throughput_min']) }}</div>
            </div>

            <!-- Latency -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-purple-50 opacity-50"></div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    @if($metrics['latency_mins'] > 10)
                        <x-status-badge status="warning" />
                    @else
                        <x-status-badge status="healthy" />
                    @endif
                </div>
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1 relative z-10">Latency</h3>
                <div class="text-3xl font-black text-slate-800 relative z-10">{{ $metrics['latency_mins'] }} <span class="text-lg text-slate-500 font-medium">min</span></div>
            </div>

            <!-- Oldest Job -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-orange-50 opacity-50"></div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center">
                        <i data-lucide="history" class="w-5 h-5"></i>
                    </div>
                </div>
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1 relative z-10">Oldest Job Age</h3>
                <div class="text-3xl font-black text-slate-800 relative z-10">{{ $metrics['oldest_job_age'] }}</div>
            </div>

            <!-- Workers -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-indigo-50 opacity-50"></div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <i data-lucide="cpu" class="w-5 h-5"></i>
                    </div>
                    <x-status-badge status="processing" />
                </div>
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1 relative z-10">Active Workers</h3>
                <div class="text-3xl font-black text-slate-800 relative z-10">{{ $metrics['active_workers'] }}</div>
            </div>

        </div>
    </div>
</div>

<div wire:poll.30s>
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-800">Marketing Overview</h2>
        <span class="text-xs text-slate-400 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse inline-block"></span>
            Live data
        </span>
    </div>

    <!-- Campaign Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="glass-panel rounded-2xl p-5 border border-white/40">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Active Campaigns</div>
            <div class="text-3xl font-black text-slate-800">{{ $metrics['active_campaigns'] }}</div>
        </div>
        <div class="glass-panel rounded-2xl p-5 border border-white/40">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Drafts / Scheduled</div>
            <div class="text-3xl font-black text-blue-600">{{ $metrics['scheduled_campaigns'] }}</div>
        </div>
        <div class="glass-panel rounded-2xl p-5 border border-white/40">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Completed Today</div>
            <div class="text-3xl font-black text-green-600">{{ $metrics['sent_today'] }}</div>
        </div>
        <div class="glass-panel rounded-2xl p-5 border border-white/40">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Total Campaigns</div>
            <div class="text-3xl font-black text-slate-800">{{ $metrics['total_campaigns'] }}</div>
        </div>
    </div>

    <!-- Queue Health -->
    <div class="glass-panel rounded-2xl p-5 border border-white/40">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Queue Health</h3>
        <div class="flex items-center gap-8">
            <div>
                <div class="text-xs text-slate-400 mb-1">Jobs Waiting</div>
                <div class="text-2xl font-black {{ $queueHealth['jobs_waiting'] > 0 ? 'text-yellow-500' : 'text-green-600' }}">
                    {{ $queueHealth['jobs_waiting'] }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-400 mb-1">Failed Jobs</div>
                <div class="text-2xl font-black {{ $queueHealth['jobs_failed'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $queueHealth['jobs_failed'] }}
                </div>
            </div>
            <div class="ml-auto">
                @if($queueHealth['jobs_failed'] > 0)
                    <span class="bg-red-100 text-red-700 px-3 py-1.5 rounded-full text-xs font-bold">⚠ Requires Attention</span>
                @else
                    <span class="bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-xs font-bold">✓ All Clear</span>
                @endif
            </div>
        </div>
    </div>
</div>

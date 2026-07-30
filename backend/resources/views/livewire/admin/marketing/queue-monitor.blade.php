<div wire:poll.10s>
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-800">Queue Monitor</h3>
        <span class="text-xs text-slate-400">Updates every 10s</span>
    </div>
    <div class="space-y-3">
        <div class="flex items-center justify-between p-4 bg-white/80 rounded-xl border border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <i data-lucide="inbox" class="w-4 h-4 text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">All Queued Jobs</p>
                    <p class="text-xs text-slate-400">Waiting to process</p>
                </div>
            </div>
            <span class="text-xl font-black {{ $metrics['jobs_waiting'] > 0 ? 'text-yellow-600' : 'text-green-600' }}">
                {{ $metrics['jobs_waiting'] }}
            </span>
        </div>

        <div class="flex items-center justify-between p-4 bg-white/80 rounded-xl border border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Failed Jobs</p>
                    <p class="text-xs text-slate-400">Requires manual retry</p>
                </div>
            </div>
            <span class="text-xl font-black {{ $metrics['jobs_failed'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ $metrics['jobs_failed'] }}
            </span>
        </div>

        <div class="flex items-center justify-between p-4 bg-white/80 rounded-xl border border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i data-lucide="mail" class="w-4 h-4 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Marketing Email Queue</p>
                    <p class="text-xs text-slate-400">marketing_emails channel</p>
                </div>
            </div>
            <span class="text-xl font-black text-slate-700">{{ $metrics['marketing_jobs'] }}</span>
        </div>
    </div>
</div>

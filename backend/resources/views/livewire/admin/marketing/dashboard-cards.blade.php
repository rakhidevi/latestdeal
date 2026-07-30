<div wire:poll.30s>
    <!-- Section 1: Global Health (Top Row) -->
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Global Health</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <!-- Workers -->
            <div class="glass-panel rounded-xl p-4 border border-white/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Workers</div>
                    <div class="text-2xl font-black text-slate-800">4</div>
                </div>
                <x-status-badge status="healthy" />
            </div>
            <!-- Queue -->
            <div class="glass-panel rounded-xl p-4 border border-white/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Queue</div>
                    <div class="text-2xl font-black text-slate-800">{{ $queueHealth['jobs_waiting'] ?? 0 }}</div>
                </div>
                @if(($queueHealth['jobs_waiting'] ?? 0) > 1000)
                    <x-status-badge status="warning" />
                @else
                    <x-status-badge status="healthy" />
                @endif
            </div>
            <!-- Mail -->
            <div class="glass-panel rounded-xl p-4 border border-white/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Mail</div>
                    <div class="text-2xl font-black text-slate-800">SES</div>
                </div>
                <x-status-badge status="healthy" />
            </div>
            <!-- Scheduler -->
            <div class="glass-panel rounded-xl p-4 border border-white/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Scheduler</div>
                    <div class="text-2xl font-black text-slate-800">Active</div>
                </div>
                <x-status-badge status="healthy" />
            </div>
            <!-- Storage -->
            <div class="glass-panel rounded-xl p-4 border border-white/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Storage</div>
                    <div class="text-2xl font-black text-slate-800">24%</div>
                </div>
                <x-status-badge status="healthy" />
            </div>
            <!-- Health -->
            <div class="glass-panel rounded-xl p-4 border border-white/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">System</div>
                    <div class="text-2xl font-black text-slate-800">All OK</div>
                </div>
                <x-status-badge status="healthy" />
            </div>
        </div>
    </div>

    <!-- Section 2: Campaign Metrics -->
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Campaign Metrics</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Active</div>
                <div class="text-2xl font-black text-slate-800">{{ $metrics['active_campaigns'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Scheduled</div>
                <div class="text-2xl font-black text-slate-800">{{ $metrics['scheduled_campaigns'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Sending</div>
                <div class="text-2xl font-black text-blue-600">0</div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Completed</div>
                <div class="text-2xl font-black text-green-600">{{ $metrics['sent_today'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Recipients</div>
                <div class="text-2xl font-black text-slate-800">12,450</div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Avg. CTR</div>
                <div class="text-2xl font-black text-purple-600">4.2%</div>
            </div>
        </div>
    </div>

    <!-- Section 3: Action Center & Timeline -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Action Center -->
        <div class="lg:col-span-2">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Action Center</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('admin.marketing.campaigns') }}" class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 bg-white hover:border-red-300 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0 group-hover:bg-red-100 transition-colors">
                        <i data-lucide="plus" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Create Campaign</h4>
                        <p class="text-sm text-slate-500 mt-0.5">Start a new email, SMS, or push campaign.</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.marketing.campaigns') }}" class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 bg-white hover:border-yellow-300 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center flex-shrink-0 group-hover:bg-yellow-100 transition-colors">
                        <i data-lucide="file-edit" class="w-5 h-5 text-yellow-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Review Drafts</h4>
                        <p class="text-sm text-slate-500 mt-0.5">You have 3 drafts pending completion.</p>
                    </div>
                </a>

                <a href="{{ route('admin.marketing.queue') }}" class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 bg-white hover:border-red-300 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0 group-hover:bg-red-100 transition-colors">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Review Failed Jobs</h4>
                        <p class="text-sm text-slate-500 mt-0.5">{{ $queueHealth['jobs_failed'] ?? 0 }} jobs require your attention.</p>
                    </div>
                </a>

                <a href="{{ route('admin.marketing.templates') }}" class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 bg-white hover:border-blue-300 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition-colors">
                        <i data-lucide="layout-template" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Update Templates</h4>
                        <p class="text-sm text-slate-500 mt-0.5">2 templates modified recently.</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Timeline -->
        <div>
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Operational Timeline</h3>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <div class="space-y-5">
                    <div class="flex gap-3">
                        <div class="relative flex-shrink-0 w-8 h-8 rounded-full bg-green-50 flex items-center justify-center z-10">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                            <div class="absolute top-8 bottom-[-20px] left-1/2 -ml-px w-px bg-slate-100 -z-10"></div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Campaign Launched</p>
                            <p class="text-xs text-slate-500">"Black Friday Preview" started sending.</p>
                            <p class="text-xs text-slate-400 mt-1">10 minutes ago</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <div class="relative flex-shrink-0 w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center z-10">
                            <i data-lucide="settings" class="w-4 h-4 text-blue-600"></i>
                            <div class="absolute top-8 bottom-[-20px] left-1/2 -ml-px w-px bg-slate-100 -z-10"></div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Provider Switched</p>
                            <p class="text-xs text-slate-500">Failover activated. Switched to Mailgun.</p>
                            <p class="text-xs text-slate-400 mt-1">45 minutes ago</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="relative flex-shrink-0 w-8 h-8 rounded-full bg-yellow-50 flex items-center justify-center z-10">
                            <i data-lucide="pause-circle" class="w-4 h-4 text-yellow-600"></i>
                            <div class="absolute top-8 bottom-[-20px] left-1/2 -ml-px w-px bg-slate-100 -z-10"></div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Campaign Paused</p>
                            <p class="text-xs text-slate-500">"Weekend Offer" paused due to bounce rate.</p>
                            <p class="text-xs text-slate-400 mt-1">2 hours ago</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="relative flex-shrink-0 w-8 h-8 rounded-full bg-red-50 flex items-center justify-center z-10">
                            <i data-lucide="server-off" class="w-4 h-4 text-red-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Worker Offline</p>
                            <p class="text-xs text-slate-500">Worker process crashed and restarted.</p>
                            <p class="text-xs text-slate-400 mt-1">Yesterday at 14:32</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 4: Performance Graphs (Placeholders) -->
    <div>
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Performance</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm h-64 flex flex-col items-center justify-center text-slate-400">
                <i data-lucide="bar-chart-3" class="w-12 h-12 mb-3 opacity-50"></i>
                <p class="font-medium">Throughput (Emails/min) Chart</p>
                <p class="text-xs mt-1">Requires metrics aggregation</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm h-64 flex flex-col items-center justify-center text-slate-400">
                <i data-lucide="line-chart" class="w-12 h-12 mb-3 opacity-50"></i>
                <p class="font-medium">Queue Latency Chart</p>
                <p class="text-xs mt-1">Requires metrics aggregation</p>
            </div>
        </div>
    </div>
</div>

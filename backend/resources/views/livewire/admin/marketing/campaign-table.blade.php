<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Campaigns</h2>
            <p class="text-sm text-slate-500 mt-1">Manage and monitor all omnichannel broadcasts.</p>
        </div>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                Import Template
            </button>
            <a href="{{ route('admin.marketing.campaigns') }}" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700 transition-colors shadow-sm flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Create Campaign
            </a>
        </div>
    </div>

    <!-- Tabs & Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <!-- Tabs -->
        <div class="border-b border-slate-200 px-2 flex space-x-1 overflow-x-auto">
            @foreach(['all' => 'All', 'draft' => 'Drafts', 'scheduled' => 'Scheduled', 'sending' => 'Sending', 'completed' => 'Completed', 'archived' => 'Archived'] as $key => $label)
                <button wire:click="$set('tab', '{{ $key }}')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $tab === $key ? 'border-red-500 text-red-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Toolbar: Shows Bulk Actions when rows selected, else Filters -->
        <div class="p-4 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4 min-h-[64px]">
            @if(count($selectedCampaigns) > 0)
                <div class="flex items-center gap-3 w-full animate-fade-in">
                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-1 rounded-full">
                        {{ count($selectedCampaigns) }} selected
                    </span>
                    <div class="h-4 w-px bg-slate-300"></div>
                    <button wire:click="pauseSelected" class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center gap-1.5"><i data-lucide="pause-circle" class="w-4 h-4"></i> Pause</button>
                    <button wire:click="resumeSelected" class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center gap-1.5"><i data-lucide="play-circle" class="w-4 h-4"></i> Resume</button>
                    <button wire:click="cancelSelected" class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center gap-1.5"><i data-lucide="x-circle" class="w-4 h-4"></i> Cancel</button>
                    <button class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center gap-1.5"><i data-lucide="download" class="w-4 h-4"></i> Export</button>
                    <div class="h-4 w-px bg-slate-300 ml-auto hidden sm:block"></div>
                    <button wire:click="deleteSelected" class="text-sm font-medium text-red-600 hover:text-red-700 flex items-center gap-1.5 ml-auto sm:ml-0" onclick="confirm('Permanent deletion?') || event.stopImmediatePropagation()"><i data-lucide="trash-2" class="w-4 h-4"></i> Delete</button>
                </div>
            @else
                <div class="flex flex-wrap items-center gap-4 w-full">
                    <div class="relative flex-1 max-w-md">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search campaigns..." class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                    </div>
                    <div class="flex items-center gap-3">
                        <select wire:model.live="filters.provider" class="text-sm border-slate-300 rounded-lg py-2 pl-3 pr-8 focus:ring-red-500 focus:border-red-500 text-slate-600">
                            <option value="">Any Provider</option>
                            <option value="ses">Amazon SES</option>
                            <option value="mailgun">Mailgun</option>
                            <option value="sendgrid">SendGrid</option>
                        </select>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="filters.hasFailures" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                            <span class="text-sm text-slate-600 font-medium">Has failures</span>
                        </label>
                    </div>
                </div>
            @endif
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-t border-slate-200">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                        </th>
                        <th class="py-3 px-4">Campaign</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 w-48">Progress</th>
                        <th class="py-3 px-4">Recipients</th>
                        <th class="py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($campaigns as $campaign)
                        @php
                            $progress = $aggregateService->getProgress($campaign);
                            $total = $campaign->total_recipients > 0 ? $campaign->total_recipients : 1;
                            $processed = $campaign->sent_count + $campaign->failed_count + $campaign->cancelled_count;
                            $statusLabel = strtolower($campaign->status);
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="py-4 px-4 text-center">
                                <input type="checkbox" wire:model.live="selectedCampaigns" value="{{ $campaign->id }}" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800">{{ $campaign->name }}</div>
                                <div class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                                    <span class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-500 font-mono text-[10px]">#{{ $campaign->id }}</span>
                                    {{ Str::limit($campaign->subject, 40) }}
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <x-status-badge :status="$statusLabel" />
                                @if(in_array($statusLabel, ['scheduled']))
                                    <div class="text-[10px] font-medium text-slate-500 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i> 
                                        {{ \Carbon\Carbon::parse($campaign->scheduled_at)->format('M d, g:i A') }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <div class="w-full bg-slate-100 rounded-full h-3 mb-1.5 overflow-hidden border border-slate-200">
                                    <div class="h-full rounded-full {{ $statusLabel === 'failed' ? 'bg-red-500' : 'bg-slate-800' }}" style="width: {{ $progress }}%"></div>
                                </div>
                                <div class="text-[11px] font-medium flex justify-between">
                                    <span class="text-slate-500">{{ number_format($processed) }} / {{ number_format($total) }}</span>
                                    <span class="text-slate-800 font-bold">{{ $progress }}%</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-sm font-semibold text-slate-800">{{ number_format($campaign->total_recipients) }}</div>
                                @if($campaign->failed_count > 0)
                                    <div class="text-[11px] font-medium text-red-500 mt-1">{{ number_format($campaign->failed_count) }} failed</div>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200 rounded" title="Preview"><i data-lucide="eye" class="w-4 h-4"></i></button>
                                    <button class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200 rounded" title="Clone"><i data-lucide="copy" class="w-4 h-4"></i></button>
                                    <button class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200 rounded" title="Audit Logs"><i data-lucide="history" class="w-4 h-4"></i></button>
                                    <div class="w-px h-4 bg-slate-300"></div>
                                    <button class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200 rounded" title="More"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 px-4 text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                                    <i data-lucide="mail-search" class="w-8 h-8 text-slate-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800 mb-1">No campaigns found</h3>
                                <p class="text-sm text-slate-500 mb-6">Create your first campaign to start engaging with your audience.</p>
                                <div class="flex items-center justify-center gap-3">
                                    <button onclick="alert('The Campaign Creation Wizard will open in the next sprint.')" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700 transition-colors shadow-sm">
                                        Create Campaign
                                    </button>
                                    <a href="{{ route('admin.marketing.templates') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                                        Browse Templates
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($campaigns->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>
</div>

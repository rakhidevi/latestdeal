<div class="space-y-6">
    <!-- Header & Bulk Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center space-x-4 w-full sm:w-auto mb-4 sm:mb-0">
            <h2 class="text-xl font-bold text-gray-800">Campaigns</h2>
            @if(count($selectedCampaigns) > 0)
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                    {{ count($selectedCampaigns) }} selected
                </span>
                
                <div class="flex items-center space-x-2">
                    <!-- Safe Actions -->
                    <button wire:click="pauseSelected" class="px-3 py-1 bg-yellow-500 text-white text-sm rounded hover:bg-yellow-600 transition">Pause</button>
                    <button wire:click="resumeSelected" class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition">Resume</button>
                    
                    <!-- Dangerous Actions -->
                    <button wire:click="cancelSelected" class="px-3 py-1 bg-gray-500 text-white text-sm rounded hover:bg-gray-600 transition" onclick="confirm('Are you sure you want to cancel these campaigns?') || event.stopImmediatePropagation()">Cancel</button>
                    <button wire:click="deleteSelected" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition" onclick="confirm('This is permanent. Delete selected campaigns?') || event.stopImmediatePropagation()">Delete</button>
                </div>
            @endif
        </div>
        
        <div class="flex space-x-2 w-full sm:w-auto">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by ID, Name, Subject..." class="w-full sm:w-64 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-wrap items-center gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
            <select wire:model.live="filters.status" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                <option value="Draft">Draft</option>
                <option value="Scheduled">Scheduled</option>
                <option value="Queued">Queued</option>
                <option value="Sending">Sending</option>
                <option value="Paused">Paused</option>
                <option value="Completed">Completed</option>
                <option value="Failed">Failed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Queue</label>
            <select wire:model.live="filters.queue" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All Queues</option>
                <option value="marketing_emails">Marketing Emails</option>
                <option value="default">Default</option>
            </select>
        </div>
        <div class="flex items-center space-x-4 pt-4">
            <label class="inline-flex items-center">
                <input type="checkbox" wire:model.live="filters.hasFailures" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-600">Has Failures</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" wire:model.live="filters.scheduledToday" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-600">Scheduled Today</span>
            </label>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID / Campaign</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule / ETA</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($campaigns as $campaign)
                    @php
                        $progress = $aggregateService->getProgress($campaign);
                        $successRate = $aggregateService->getSuccessRate($campaign);
                        $eta = $aggregateService->getETA($campaign, 1500); // hardcoded throughput example for now
                    @endphp
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <input type="checkbox" wire:model.live="selectedCampaigns" value="{{ $campaign->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">#{{ $campaign->id }} - {{ $campaign->name }}</div>
                            <div class="text-xs text-gray-500">{{ Str::limit($campaign->subject, 30) }}</div>
                            <div class="text-xs text-gray-400 mt-1">Queue: {{ $campaign->queue ?? 'default' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ match($campaign->status) {
                                    'Completed' => 'bg-green-100 text-green-800',
                                    'Sending' => 'bg-blue-100 text-blue-800',
                                    'Queued', 'Preparing' => 'bg-indigo-100 text-indigo-800',
                                    'Failed' => 'bg-red-100 text-red-800',
                                    'Paused' => 'bg-yellow-100 text-yellow-800',
                                    'Cancelled', 'Archived' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800'
                                } }}">
                                {{ $campaign->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1">
                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="text-xs text-gray-500 flex justify-between">
                                <span>{{ number_format($campaign->sent_count + $campaign->failed_count + $campaign->cancelled_count) }} / {{ number_format($campaign->total_recipients) }}</span>
                                <span>{{ $progress }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm {{ $successRate >= 95 ? 'text-green-600' : ($successRate >= 80 ? 'text-yellow-600' : 'text-red-600') }} font-semibold">
                                {{ $successRate }}%
                            </div>
                            @if($campaign->failed_count > 0)
                                <div class="text-xs text-red-500">{{ number_format($campaign->failed_count) }} failed</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($campaign->status === 'Sending')
                                <div class="text-sm font-medium text-blue-600">ETA: {{ $eta }}</div>
                            @else
                                <div class="text-sm text-gray-900">{{ $campaign->scheduled_at ? \Carbon\Carbon::parse($campaign->scheduled_at)->format('M d, g:i A') : 'Not Scheduled' }}</div>
                            @endif
                            <div class="text-xs text-gray-500">Created: {{ $campaign->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900" title="View Recipients">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </a>
                                <a href="#" class="text-gray-600 hover:text-gray-900" title="Download Report">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No campaigns found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new campaign.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($campaigns->hasPages())
        <div class="mt-4">
            {{ $campaigns->links() }}
        </div>
    @endif
</div>

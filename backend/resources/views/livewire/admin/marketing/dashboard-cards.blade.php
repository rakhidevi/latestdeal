<div wire:poll.5s>
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Marketing Overview</h2>
        <span class="text-sm text-gray-400">Auto-updating...</span>
    </div>

    <!-- Campaigns Section -->
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Campaigns</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Active</div>
                <div class="text-2xl font-semibold text-gray-800">{{ number_format($dashboard->campaignMetrics->activeCampaigns) }}</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Drafts</div>
                <div class="text-2xl font-semibold text-gray-800">{{ number_format($dashboard->campaignMetrics->draftCampaigns) }}</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Scheduled</div>
                <div class="text-2xl font-semibold text-blue-600">{{ number_format($dashboard->campaignMetrics->scheduledCampaigns) }}</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Sending</div>
                <div class="text-2xl font-semibold text-indigo-600">{{ number_format($dashboard->campaignMetrics->sendingCampaigns) }}</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Sent Today</div>
                <div class="text-2xl font-semibold text-green-600">{{ number_format($dashboard->campaignMetrics->sentToday) }}</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Failed Today</div>
                <div class="text-2xl font-semibold text-red-600">{{ number_format($dashboard->campaignMetrics->failedToday) }}</div>
            </div>

        </div>
    </div>

    <!-- Infrastructure & Health Section -->
    <div>
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Infrastructure & Health</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            
            @if(config('marketing.features.queue_widget', true))
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Queue Size</div>
                    @if(array_sum(array_column($dashboard->queueMetrics->queues, 'size')) > 0)
                        <span class="flex h-2 w-2 rounded-full bg-yellow-400"></span>
                    @else
                        <span class="flex h-2 w-2 rounded-full bg-green-400"></span>
                    @endif
                </div>
                <div class="text-2xl font-semibold text-gray-800">{{ number_format(array_sum(array_column($dashboard->queueMetrics->queues, 'size'))) }}</div>
                <div class="text-xs text-gray-400 mt-1">across {{ count($dashboard->queueMetrics->queues) }} queue(s)</div>
            </div>
            @endif

            @if(config('marketing.features.health_widget', true))
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Worker Status</div>
                    @if($dashboard->healthMetrics->workerStatus === 'Running')
                        <span class="flex h-2 w-2 rounded-full bg-green-400"></span>
                    @else
                        <span class="flex h-2 w-2 rounded-full bg-red-400"></span>
                    @endif
                </div>
                <div class="text-2xl font-semibold text-gray-800">{{ $dashboard->healthMetrics->workerStatus }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $dashboard->queueMetrics->workers }} active worker(s)</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Mail Provider</div>
                    <span class="flex h-2 w-2 rounded-full bg-green-400"></span>
                </div>
                <div class="text-2xl font-semibold text-gray-800">{{ $dashboard->healthMetrics->mailProvider }}</div>
                <div class="text-xs text-gray-400 mt-1">Active Integration</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Rate Limit</div>
                </div>
                <div class="text-2xl font-semibold text-gray-800">{{ $dashboard->healthMetrics->rateLimit }}</div>
                <div class="text-xs text-gray-400 mt-1">Based on current plan</div>
            </div>
            @endif

        </div>
    </div>
</div>

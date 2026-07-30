<div wire:poll.5s>
    <div class="mb-6 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800">Queue Infrastructure</h3>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Workers Online</div>
            <div class="text-2xl font-semibold text-green-600">{{ $metrics->workers }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Failed Jobs</div>
            <div class="text-2xl font-semibold {{ $metrics->failedJobs > 0 ? 'text-red-600' : 'text-gray-800' }}">
                {{ number_format($metrics->failedJobs) }}
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Throughput / min</div>
            <div class="text-2xl font-semibold text-gray-800">{{ number_format($metrics->throughput) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Oldest Pending</div>
            <div class="text-xl font-semibold text-gray-800">
                @if($metrics->oldestPending)
                    {{ \Carbon\Carbon::createFromTimestamp($metrics->oldestPending)->diffForHumans() }}
                @else
                    None
                @endif
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700">Active Queues</h4>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Queue Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($metrics->queues as $queueName => $queueData)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $queueName }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($queueData['size']) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            {{ ucfirst($queueData['status']) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No active queues found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

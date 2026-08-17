<div>
    <div class="mb-6 border-b border-gray-200 pb-5 sm:flex sm:items-center sm:justify-between">
        <div>
            <h3 class="text-2xl font-bold leading-6 text-gray-900">Pipeline Viewer</h3>
            <p class="mt-2 text-sm text-gray-500">Aggregated operational health and throughput of the Commerce Intelligence data flow.</p>
        </div>
        <div class="mt-3 sm:ml-4 sm:mt-0 flex items-center space-x-4">
            <div class="flex space-x-2 border border-gray-300 rounded-md p-1 bg-gray-50 text-sm">
                <button wire:click="setTimeframe('live')" class="{{ $timeframe === 'live' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700' }} px-3 py-1 rounded-md font-medium">Live</button>
                <button wire:click="setTimeframe('15m')" class="{{ $timeframe === '15m' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700' }} px-3 py-1 rounded-md font-medium">15m</button>
                <button wire:click="setTimeframe('1h')" class="{{ $timeframe === '1h' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700' }} px-3 py-1 rounded-md font-medium">1h</button>
                <button wire:click="setTimeframe('24h')" class="{{ $timeframe === '24h' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700' }} px-3 py-1 rounded-md font-medium">24h</button>
                <button wire:click="setTimeframe('7d')" class="{{ $timeframe === '7d' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700' }} px-3 py-1 rounded-md font-medium">7d</button>
            </div>
        </div>
    </div>

    @if($polling)
        <div wire:poll.5s="fetchPipelineData"></div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative">
            <!-- Connecting Line -->
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t-4 border-gray-200"></div>
            </div>

            <ul role="list" class="relative flex justify-between">
                @foreach($pipelineNodes as $node)
                <li class="relative">
                    <div class="flex flex-col items-center group relative cursor-pointer">
                        <!-- Node Icon -->
                        @php
                            $colorClass = match($node['status']) {
                                'HEALTHY' => 'bg-green-500',
                                'WARNING' => 'bg-yellow-400',
                                'CRITICAL' => 'bg-red-500',
                                'OFFLINE' => 'bg-gray-400',
                                default => 'bg-indigo-600',
                            };
                            $trendIcon = match($node['trend']) {
                                'improving' => '↑',
                                'degrading' => '↓',
                                default => '→',
                            };
                        @endphp
                        <span class="flex h-16 w-16 items-center justify-center rounded-full ring-8 ring-white shadow-lg z-10 transition-transform transform group-hover:scale-110 {{ $colorClass }}">
                            @if($node['status'] === 'CRITICAL' || $node['status'] === 'WARNING')
                                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            @else
                                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                        </span>

                        <!-- Node Label -->
                        <div class="mt-4 text-center">
                            <h4 class="text-sm font-bold text-gray-900">{{ $node['name'] }}</h4>
                        </div>

                        <!-- Dropdown Metrics (Hover) -->
                        <div class="absolute top-24 opacity-0 group-hover:opacity-100 transition-opacity z-20 bg-white shadow-xl rounded-lg border border-gray-200 p-4 w-56 text-left pointer-events-none">
                            <h5 class="text-xs font-semibold text-gray-500 uppercase mb-2 border-b pb-1">{{ $node['display_name'] }} Health</h5>
                            <dl class="space-y-1 mb-3">
                                <div class="flex justify-between">
                                    <dt class="text-xs font-medium text-gray-500">Processed</dt>
                                    <dd class="text-xs font-bold text-gray-900">{{ number_format($node['events_processed']) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-xs font-medium text-gray-500">Failed</dt>
                                    <dd class="text-xs font-bold {{ $node['events_failed'] > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($node['events_failed']) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-xs font-medium text-gray-500">Success Rate</dt>
                                    <dd class="text-xs font-bold text-gray-900">{{ $node['success_rate'] }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-xs font-medium text-gray-500">Avg Latency</dt>
                                    <dd class="text-xs font-bold text-gray-900">{{ $node['average_latency_ms'] }}ms</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-xs font-medium text-gray-500">Queue Depth</dt>
                                    <dd class="text-xs font-bold text-gray-900">{{ number_format($node['queue_depth']) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-xs font-medium text-gray-500">Trend</dt>
                                    <dd class="text-xs font-bold text-gray-900">{{ $trendIcon }} {{ ucfirst($node['trend']) }}</dd>
                                </div>
                            </dl>
                            <div class="space-y-2 border-t pt-2 pointer-events-auto">
                                <a href="{{ route('admin.studio.live-event-stream', ['filterType' => $node['stage']]) }}" class="block text-center text-xs font-semibold text-indigo-600 hover:text-indigo-500 bg-indigo-50 rounded py-1">
                                    View Live Stream
                                </a>
                                @if($node['last_failed_trace_id'])
                                <a href="{{ route('admin.studio.universal-trace-viewer', ['id' => $node['last_failed_trace_id']]) }}" class="block text-center text-xs font-semibold text-red-600 hover:text-red-500 bg-red-50 rounded py-1">
                                    Debug Last Failure
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

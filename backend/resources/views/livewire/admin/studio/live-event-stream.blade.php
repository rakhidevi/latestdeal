<div>
    <div class="mb-4 sm:flex sm:items-center sm:justify-between">
        <div>
            <h3 class="text-2xl font-bold leading-6 text-gray-900">Live Event Stream</h3>
            <p class="mt-2 text-sm text-gray-500">Real-time immutable tail of the UCDP EventStore.</p>
        </div>
        <div class="mt-4 flex flex-wrap gap-3 sm:mt-0 sm:ml-4 items-center">
            <input type="text" wire:model.live.debounce.500ms="filterType" placeholder="Type..." class="block w-32 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
            <input type="text" wire:model.live.debounce.500ms="filterProvider" placeholder="Provider..." class="block w-32 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
            <select wire:model.live="filterSeverity" class="block w-32 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                <option value="">Any Severity</option>
                <option value="INFO">INFO</option>
                <option value="SUCCESS">SUCCESS</option>
                <option value="WARNING">WARNING</option>
                <option value="ERROR">ERROR</option>
                <option value="CRITICAL">CRITICAL</option>
            </select>
            <input type="text" wire:model.live.debounce.500ms="filterTraceId" placeholder="Trace ID..." class="block w-32 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
        </div>
    </div>

    <!-- Performance Metrics Header -->
    @if(!empty($metrics))
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-7">
        <div class="bg-white px-4 py-3 shadow rounded-lg border border-gray-200">
            <dt class="text-xs font-medium text-gray-500 truncate">Events/sec</dt>
            <dd class="mt-1 text-xl font-semibold text-gray-900">{{ $metrics['events_per_sec'] }}</dd>
        </div>
        <div class="bg-white px-4 py-3 shadow rounded-lg border border-gray-200">
            <dt class="text-xs font-medium text-gray-500 truncate">Queue Depth</dt>
            <dd class="mt-1 text-xl font-semibold text-gray-900">{{ number_format($metrics['queue_depth']) }}</dd>
        </div>
        <div class="bg-white px-4 py-3 shadow rounded-lg border border-gray-200">
            <dt class="text-xs font-medium text-gray-500 truncate">Workers</dt>
            <dd class="mt-1 text-xl font-semibold text-gray-900">{{ $metrics['active_workers'] }}</dd>
        </div>
        <div class="bg-white px-4 py-3 shadow rounded-lg border border-gray-200">
            <dt class="text-xs font-medium text-gray-500 truncate">Latency</dt>
            <dd class="mt-1 text-xl font-semibold text-gray-900">{{ $metrics['latency_ms'] }}ms</dd>
        </div>
        <div class="bg-white px-4 py-3 shadow rounded-lg border border-gray-200">
            <dt class="text-xs font-medium text-gray-500 truncate">Errors/min</dt>
            <dd class="mt-1 text-xl font-semibold text-gray-900">{{ $metrics['errors_per_min'] }}</dd>
        </div>
        <div class="bg-white px-4 py-3 shadow rounded-lg border border-gray-200">
            <dt class="text-xs font-medium text-gray-500 truncate">Memory</dt>
            <dd class="mt-1 text-xl font-semibold text-gray-900">{{ $metrics['memory_usage'] }}</dd>
        </div>
        <div class="bg-white px-4 py-3 shadow rounded-lg border border-gray-200">
            <dt class="text-xs font-medium text-gray-500 truncate">CPU</dt>
            <dd class="mt-1 text-xl font-semibold text-gray-900">{{ $metrics['cpu_usage'] }}</dd>
        </div>
    </div>
    @endif

    <!-- Stream Controls -->
    <div class="mb-2 flex items-center justify-between text-sm">
        <div class="flex space-x-2 border border-gray-300 rounded-md p-1 bg-gray-50">
            <button wire:click="setMode('live')" class="{{ $mode === 'live' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700' }} px-3 py-1 rounded-md font-medium">Live</button>
            <button wire:click="setMode('10m')" class="{{ $mode === '10m' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700' }} px-3 py-1 rounded-md font-medium">Last 10m</button>
            <button wire:click="setMode('1h')" class="{{ $mode === '1h' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700' }} px-3 py-1 rounded-md font-medium">Last 1h</button>
            <button wire:click="setMode('yesterday')" class="{{ $mode === 'yesterday' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700' }} px-3 py-1 rounded-md font-medium">Yesterday</button>
        </div>
        <div class="flex space-x-2">
            @if($mode === 'live')
                <button wire:click="togglePolling" class="inline-flex items-center rounded-md px-3 py-1.5 text-xs font-semibold text-white shadow-sm {{ $polling ? 'bg-red-600 hover:bg-red-500' : 'bg-green-600 hover:bg-green-500' }}">
                    {{ $polling ? 'Pause' : 'Resume' }}
                </button>
            @endif
            <button wire:click="clearStream" class="inline-flex items-center rounded-md px-3 py-1.5 text-xs font-semibold bg-gray-200 text-gray-700 shadow-sm hover:bg-gray-300">
                Clear
            </button>
        </div>
    </div>

    <!-- Polling Directive -->
    @if($polling)
        <div wire:poll.2s="fetchEvents"></div>
    @endif

    <div class="bg-gray-900 rounded-lg shadow-lg overflow-hidden border border-gray-700">
        <div class="px-4 py-2 border-b border-gray-800 flex justify-between items-center bg-black">
            <div class="flex space-x-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
            </div>
            <div class="text-xs font-mono text-gray-400">tail -f /var/log/ucdp_events.log</div>
            <div class="flex items-center">
                @if($polling)
                    <span class="flex h-3 w-3 relative mr-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <span class="text-xs text-green-400">LIVE</span>
                @else
                    <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                    <span class="text-xs text-red-400">PAUSED</span>
                @endif
            </div>
        </div>
        
        <div class="p-6 font-mono text-sm text-gray-300 h-screen max-h-[70vh] overflow-y-auto space-y-1" x-data="{ selectedEvent: null, drawerOpen: false }">
            @forelse($events as $event)
                <div class="flex space-x-4 hover:bg-gray-800 px-2 py-1.5 rounded-sm transition-colors group relative" wire:key="{{ $event['id'] }}">
                    <span class="text-gray-500 w-24 flex-shrink-0">[{{ explode('T', $event['timestamp'])[1] ?? $event['timestamp'] }}]</span>
                    
                    @php
                        $colorClass = match($event['level']) {
                            'CRITICAL' => 'text-red-500',
                            'ERROR' => 'text-orange-500',
                            'WARNING' => 'text-yellow-400',
                            'SUCCESS' => 'text-green-400',
                            default => 'text-gray-400',
                        };
                    @endphp
                    <span class="{{ $colorClass }} font-bold w-20 flex-shrink-0">{{ substr($event['level'], 0, 4) }}</span>
                    <span class="text-blue-400 font-bold w-48 flex-shrink-0" title="{{ $event['type'] }}">{{ $event['type'] }}</span>
                    
                    <span class="text-purple-400 w-24 flex-shrink-0">[{{ strtoupper($event['provider']) }}]</span>
                    <span class="text-yellow-300 flex-1 truncate">uuid: 
                        <a href="{{ route('admin.studio.universal-trace-viewer', ['id' => $event['trace_id']]) }}" class="hover:underline hover:text-white" title="Open Trace {{ $event['trace_id'] }}">{{ $event['uuid'] }}</a>
                    </span>
                    <span class="text-gray-500 w-32 truncate hidden xl:inline-block">{{ $event['category'] }}</span>

                    <!-- Quick Actions -->
                    <div class="absolute right-2 bg-gray-800 opacity-0 group-hover:opacity-100 flex items-center space-x-3 px-2 py-1 rounded">
                        <button @click="selectedEvent = {{ json_encode($event) }}; drawerOpen = true;" class="text-xs text-white hover:text-indigo-400 font-sans font-semibold">Inspect</button>
                        <a href="{{ route('admin.studio.universal-trace-viewer', ['id' => $event['trace_id']]) }}" class="text-xs text-white hover:text-blue-400 font-sans font-semibold">Trace</a>
                        <button class="text-xs text-white hover:text-yellow-400 font-sans font-semibold">Replay</button>
                    </div>
                </div>
            @empty
                <div class="text-gray-500 italic px-2 py-1">No events matching filters...</div>
            @endforelse
            
            @if($polling)
            <div class="animate-pulse flex space-x-4 px-2 py-1 mt-4">
                <span class="text-gray-600 block w-2 h-4 bg-gray-500"></span>
            </div>
            @endif

            <!-- Event Drawer Slide-over -->
            <div x-show="drawerOpen" class="relative z-50 font-sans" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="drawerOpen = false"></div>
                <div class="fixed inset-0 overflow-hidden">
                    <div class="absolute inset-0 overflow-hidden">
                        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                            <div class="pointer-events-auto w-screen max-w-md transform transition-all bg-white shadow-xl flex flex-col h-full">
                                <div class="px-4 py-6 bg-gray-50 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                                    <div>
                                        <h2 class="text-base font-semibold leading-6 text-gray-900" id="slide-over-title" x-text="selectedEvent?.type">Event Details</h2>
                                        <p class="text-xs text-gray-500 mt-1 font-mono" x-text="selectedEvent?.uuid"></p>
                                    </div>
                                    <button @click="drawerOpen = false" class="text-gray-400 hover:text-gray-500">
                                        <span class="sr-only">Close panel</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                <div class="flex-1 overflow-y-auto p-4 space-y-6">
                                    <!-- Meta -->
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 border-b pb-1 mb-2">Metadata</h3>
                                        <dl class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                                            <dt>Category</dt><dd class="text-gray-900" x-text="selectedEvent?.category"></dd>
                                            <dt>Provider</dt><dd class="text-gray-900" x-text="selectedEvent?.provider"></dd>
                                            <dt>Worker</dt><dd class="text-gray-900" x-text="selectedEvent?.worker"></dd>
                                            <dt>Timestamp</dt><dd class="text-gray-900" x-text="selectedEvent?.timestamp"></dd>
                                        </dl>
                                    </div>
                                    <!-- Payload -->
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 border-b pb-1 mb-2">Payload (Raw JSON)</h3>
                                        <pre class="bg-gray-100 p-2 rounded text-xs text-gray-800 overflow-x-auto border border-gray-200" x-text="JSON.stringify(selectedEvent?.payload, null, 2)"></pre>
                                    </div>
                                    <!-- Links -->
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 border-b pb-1 mb-2">Relationships</h3>
                                        <ul class="text-sm space-y-2">
                                            <li>
                                                <a :href="`/admin/studio/trace-viewer/${selectedEvent?.trace_id}`" class="text-indigo-600 hover:underline flex items-center">
                                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                                    Parent Trace
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

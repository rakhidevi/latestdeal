<div>
    <div wire:poll.10s="fetchData"></div>
    
    <!-- Executive Health Bar -->
    <div class="mb-6 bg-white shadow rounded-lg px-6 py-4 border-l-4 {{ $executiveHealth['platform_status'] === 'HEALTHY' ? 'border-green-500' : 'border-red-500' }}">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 text-center">
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Platform Status</dt>
                <dd class="mt-1 text-xl font-bold {{ $executiveHealth['platform_status'] === 'HEALTHY' ? 'text-green-600' : 'text-red-600' }}">{{ $executiveHealth['platform_status'] }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Active Workers</dt>
                <dd class="mt-1 text-xl font-bold text-gray-900">{{ $executiveHealth['active_workers'] }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Global Queue</dt>
                <dd class="mt-1 text-xl font-bold text-gray-900">{{ number_format($executiveHealth['queue_depth']) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Revenue Today</dt>
                <dd class="mt-1 text-xl font-bold text-gray-900">₹{{ number_format($executiveHealth['revenue_today']) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Rollout</dt>
                <dd class="mt-1 text-xl font-bold text-gray-900">{{ $executiveHealth['rollout_percent'] }}%</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Active Alerts</dt>
                <dd class="mt-1 text-xl font-bold {{ $executiveHealth['active_alerts'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $executiveHealth['active_alerts'] }}</dd>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- Left Column: Provider & Queue -->
        <div class="space-y-6">
            
            <!-- Provider Health -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50"><h4 class="font-bold text-gray-800 text-sm">Provider Health</h4></div>
                <div class="p-4 space-y-4">
                    @foreach($providerHealth as $ph)
                    <div class="border rounded p-3 {{ $ph['status'] === 'HEALTHY' ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }}">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-gray-900">{{ $ph['provider'] }}</span>
                            <span class="text-xs px-2 py-1 rounded {{ $ph['status'] === 'HEALTHY' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">{{ $ph['status'] }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                            <div>Availability: <span class="font-bold">{{ $ph['availability'] }}</span></div>
                            <div>Selector Health: <span class="font-bold">{{ $ph['selector_health'] }}</span></div>
                            <div>Captcha Rate: <span class="font-bold">{{ $ph['captcha_rate'] }}</span></div>
                            <div>Latency: <span class="font-bold">{{ $ph['latency'] }}</span></div>
                        </div>
                        <div class="mt-2 text-right">
                            <a href="{{ route('admin.studio.universal-object-explorer', ['query' => $ph['provider']]) }}" class="text-xs text-indigo-600 hover:underline">Inspect Provider &rarr;</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Queue Center -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50"><h4 class="font-bold text-gray-800 text-sm">Queue Center</h4></div>
                <div class="p-4">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-gray-500 uppercase border-b">
                            <tr><th>Queue</th><th>Waiting</th><th>Proc</th><th>Fail</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($queueHealth as $qName => $qMetrics)
                            <tr>
                                <td class="py-2 font-medium capitalize">{{ $qName }}</td>
                                <td class="py-2">{{ number_format($qMetrics['waiting']) }}</td>
                                <td class="py-2 text-indigo-600">{{ $qMetrics['processing'] }}</td>
                                <td class="py-2 {{ $qMetrics['failed'] > 0 ? 'text-red-600 font-bold' : '' }}">{{ $qMetrics['failed'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>

        <!-- Middle Column: Alerts & Mini Pipeline -->
        <div class="space-y-6">
            
            <!-- Active Alerts -->
            <div class="bg-white shadow rounded-lg border border-red-200">
                <div class="px-4 py-3 border-b border-red-200 bg-red-50 flex justify-between items-center">
                    <h4 class="font-bold text-red-800 text-sm">Active Alerts</h4>
                    <span class="bg-red-200 text-red-800 text-xs px-2 py-0.5 rounded-full font-bold">{{ count($activeAlerts) }}</span>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($activeAlerts as $alert)
                    <div class="border-l-4 {{ $alert['type'] === 'CRITICAL' ? 'border-red-600 bg-red-50' : 'border-yellow-400 bg-yellow-50' }} p-3 rounded-r">
                        <div class="flex justify-between">
                            <span class="text-xs font-bold {{ $alert['type'] === 'CRITICAL' ? 'text-red-800' : 'text-yellow-800' }}">{{ $alert['type'] }}</span>
                            <span class="text-xs text-gray-500">{{ $alert['timestamp'] }}</span>
                        </div>
                        <p class="text-sm font-medium mt-1">{{ $alert['message'] }}</p>
                        <div class="mt-2">
                            <a href="{{ route('admin.studio.universal-object-explorer', ['query' => $alert['related_trace']]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Investigate Root Cause &rarr;</a>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 italic text-center py-4">No active alerts.</p>
                    @endforelse
                </div>
            </div>

            <!-- Mini Pipeline View -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h4 class="font-bold text-gray-800 text-sm">Throughput Pipeline (15m)</h4>
                    <a href="{{ route('admin.studio.pipeline-viewer') }}" class="text-xs text-indigo-600 hover:underline">Full View</a>
                </div>
                <div class="p-4">
                    <div class="flex justify-between items-center relative">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-gray-300 border-dashed"></div>
                        </div>
                        @foreach(array_slice($miniPipeline, 0, 4) as $node) <!-- Show first 4 for space -->
                        <div class="relative flex flex-col items-center bg-white px-2">
                            <span class="h-8 w-8 rounded-full flex items-center justify-center text-white text-xs {{ $node['status'] === 'HEALTHY' ? 'bg-green-500' : ($node['status'] === 'WARNING' ? 'bg-yellow-400' : 'bg-red-500') }}">
                                {{ substr($node['display_name'], 0, 1) }}
                            </span>
                            <span class="text-[10px] mt-1 font-medium text-gray-600">{{ $node['success_rate'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Worker Monitor -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50"><h4 class="font-bold text-gray-800 text-sm">Worker Monitor</h4></div>
                <div class="p-4">
                    <ul class="divide-y divide-gray-200">
                        @foreach($workerMetrics as $worker)
                        <li class="py-2 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $worker['id'] }}</p>
                                <p class="text-xs text-gray-500">CPU: {{ $worker['cpu'] }} | RAM: {{ $worker['ram'] }}</p>
                            </div>
                            <a href="{{ route('admin.studio.universal-object-explorer', ['query' => $worker['id']]) }}" class="text-xs text-indigo-600 hover:underline">Inspect</a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>

        <!-- Right Column: Revenue, Rollout, Actions & Event Tail -->
        <div class="space-y-6">
            
            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50"><h4 class="font-bold text-gray-800 text-sm">Quick Actions</h4></div>
                <div class="p-4 space-y-2">
                    <!-- Session messages -->
                    @if (session()->has('message'))
                        <div class="p-2 bg-green-100 text-green-700 text-xs rounded">{{ session('message') }}</div>
                    @endif
                    @if (session()->has('error'))
                        <div class="p-2 bg-red-100 text-red-700 text-xs rounded font-bold">{{ session('error') }}</div>
                    @endif

                    <button wire:click="pauseRollout" class="w-full text-left px-3 py-2 bg-gray-50 hover:bg-gray-100 border rounded text-sm font-medium text-gray-700">⏸ Pause Rollout</button>
                    <button wire:click="enableShadowMode" class="w-full text-left px-3 py-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded text-sm font-medium text-indigo-700">👻 Enable Shadow Mode</button>
                    <a href="#" class="block w-full text-left px-3 py-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded text-sm font-medium text-blue-700">🔬 Open Regression Lab</a>
                    
                    @if(auth()->user()->can('trigger_kill_switch'))
                    <button wire:click="triggerKillSwitch" class="w-full text-left px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-bold">☠ TRIGGER KILL SWITCH</button>
                    @endif
                </div>
            </div>

            <!-- Economics Engine -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50"><h4 class="font-bold text-gray-800 text-sm">Economics Engine</h4></div>
                <div class="p-4 grid grid-cols-2 gap-4 text-center">
                    <div><dt class="text-xs text-gray-500">Today</dt><dd class="font-bold text-lg text-green-600">{{ $revenueMetrics['today'] }}</dd></div>
                    <div><dt class="text-xs text-gray-500">This Week</dt><dd class="font-bold text-lg text-gray-900">{{ $revenueMetrics['this_week'] }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Cost/Crawl</dt><dd class="font-bold text-sm text-gray-900">{{ $revenueMetrics['cost_per_crawl'] }}</dd></div>
                    <div><dt class="text-xs text-gray-500">ROI</dt><dd class="font-bold text-sm text-green-600">{{ $revenueMetrics['roi'] }}</dd></div>
                </div>
            </div>

            <!-- Rollout Details -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50"><h4 class="font-bold text-gray-800 text-sm">Rollout Target</h4></div>
                <div class="p-4">
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                      <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $rolloutStatus['traffic'] }}"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600">
                        <span>Mode: <span class="font-bold">{{ $rolloutStatus['mode'] }}</span></span>
                        <span>Auto-Rollback: <span class="font-bold text-green-600">{{ $rolloutStatus['automatic_rollback'] }}</span></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <!-- Mini Event Stream (Tail) -->
    <div class="bg-gray-900 shadow rounded-lg border border-gray-800 overflow-hidden">
        <div class="px-4 py-2 border-b border-gray-800 bg-gray-950 flex justify-between items-center">
            <h4 class="font-bold text-gray-400 text-xs uppercase">Live Event Tail</h4>
            <a href="{{ route('admin.studio.live-event-stream') }}" class="text-xs text-indigo-400 hover:text-indigo-300">View Full Stream &rarr;</a>
        </div>
        <div class="p-4 h-48 overflow-y-auto font-mono text-[10px] text-gray-300 space-y-1">
            @foreach($miniEventStream as $event)
            <div class="flex space-x-2">
                <span class="text-gray-500">{{ \Carbon\Carbon::parse($event['timestamp'])->format('H:i:s.v') }}</span>
                <span class="{{ $event['level'] === 'ERROR' || $event['level'] === 'CRITICAL' ? 'text-red-400' : ($event['level'] === 'WARNING' ? 'text-yellow-400' : 'text-green-400') }} w-16">[{{ $event['level'] }}]</span>
                <span class="text-indigo-400 w-24">[{{ $event['category'] }}]</span>
                <span class="text-gray-100 truncate">{{ $event['message'] }}</span>
                <a href="{{ route('admin.studio.universal-object-explorer', ['query' => $event['trace_id']]) }}" class="text-blue-400 hover:underline">inspect</a>
            </div>
            @endforeach
        </div>
    </div>

</div>

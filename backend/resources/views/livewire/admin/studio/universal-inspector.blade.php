<div class="bg-white shadow rounded-lg h-full border border-gray-200 flex flex-col">
    @if($entityData)
        <!-- Header -->
        <div class="px-4 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Inspector: {{ $entityType }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                UUID: <span class="font-mono text-xs">{{ $entityId }}</span>
            </p>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-4 px-4" aria-label="Tabs" x-data="{ tab: 'summary' }">
                <button @click="tab = 'summary'" :class="{'border-indigo-500 text-indigo-600': tab === 'summary', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'summary'}" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">Summary</button>
                <button @click="tab = 'dto'" :class="{'border-indigo-500 text-indigo-600': tab === 'dto', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'dto'}" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">DTO</button>
                <button @click="tab = 'raw'" :class="{'border-indigo-500 text-indigo-600': tab === 'raw', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'raw'}" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">Raw JSON</button>
                <button @click="tab = 'events'" :class="{'border-indigo-500 text-indigo-600': tab === 'events', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'events'}" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">Events</button>
                <button @click="tab = 'evidence'" :class="{'border-indigo-500 text-indigo-600': tab === 'evidence', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'evidence'}" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">Evidence</button>
                <button @click="tab = 'performance'" :class="{'border-indigo-500 text-indigo-600': tab === 'performance', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'performance'}" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">Performance</button>
                <button @click="tab = 'relationships'" :class="{'border-indigo-500 text-indigo-600': tab === 'relationships', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'relationships'}" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">Relationships</button>

                <!-- Tab Panels -->
                <div class="mt-4 flex-1 overflow-auto bg-gray-50 p-4 absolute inset-x-0 bottom-0 top-[110px]">
                    
                    <!-- Summary -->
                    <div x-show="tab === 'summary'" class="space-y-4">
                        <div class="bg-white p-3 rounded border">
                            <h4 class="text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Node Details</h4>
                            <dl class="grid grid-cols-2 gap-4 text-sm">
                                <div><dt class="text-gray-500">Name:</dt><dd class="text-gray-900 font-medium">{{ $entityData['name'] ?? 'N/A' }}</dd></div>
                                <div><dt class="text-gray-500">Status:</dt><dd class="text-green-600 font-bold uppercase">{{ $entityData['status'] ?? 'SUCCESS' }}</dd></div>
                                <div><dt class="text-gray-500">Timestamp:</dt><dd class="text-gray-900">{{ $entityData['timestamp'] ?? 'N/A' }}</dd></div>
                                <div><dt class="text-gray-500">Worker:</dt><dd class="text-gray-900">{{ $entityData['worker'] ?? 'N/A' }}</dd></div>
                            </dl>
                        </div>
                    </div>

                    <!-- DTO -->
                    <div x-show="tab === 'dto'" style="display: none;">
                        <pre class="text-xs text-indigo-800 bg-indigo-50 p-3 rounded border border-indigo-200 overflow-auto">{{ json_encode($entityData['payload'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                    </div>

                    <!-- Raw JSON -->
                    <div x-show="tab === 'raw'" style="display: none;">
                        <pre class="text-xs text-gray-800 bg-gray-100 p-3 rounded border border-gray-300 overflow-auto">{{ json_encode($entityData, JSON_PRETTY_PRINT) }}</pre>
                    </div>

                    <!-- Events -->
                    <div x-show="tab === 'events'" style="display: none;">
                        <p class="text-sm text-gray-500 italic">No sub-events generated for this node.</p>
                    </div>

                    <!-- Evidence -->
                    <div x-show="tab === 'evidence'" style="display: none;">
                        <p class="text-sm text-gray-500 italic">No mathematical evidence attached to this node.</p>
                    </div>

                    <!-- Performance -->
                    <div x-show="tab === 'performance'" style="display: none;">
                        <div class="bg-white p-3 rounded border">
                            <h4 class="text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Telemetry</h4>
                            <dl class="grid grid-cols-2 gap-4 text-sm">
                                @foreach($entityData['metrics'] ?? [] as $key => $val)
                                <div><dt class="text-gray-500">{{ $key }}:</dt><dd class="text-gray-900 font-mono">{{ $val }}</dd></div>
                                @endforeach
                            </dl>
                        </div>
                    </div>

                    <!-- Relationships -->
                    <div x-show="tab === 'relationships'" style="display: none;">
                        <ul class="text-sm text-blue-600 space-y-2">
                            <li><a href="#" class="hover:underline">Parent Trace: {{ $entityData['trace_id'] ?? 'unknown' }}</a></li>
                        </ul>
                    </div>

                </div>
            </nav>
        </div>
    @else
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Universal Inspector</h3>
                <p class="mt-1 text-sm text-gray-500">Select an object to inspect its payload.</p>
            </div>
        </div>
    @endif
</div>

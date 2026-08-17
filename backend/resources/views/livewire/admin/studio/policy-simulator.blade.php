<div class="h-[calc(100vh-100px)] flex flex-col">
    <!-- Header -->
    <div class="mb-4 border-b border-gray-200 pb-4 flex justify-between items-center px-4 pt-4">
        <div>
            <h3 class="text-2xl font-bold leading-6 text-gray-900">Policy Simulator Sandbox</h3>
            <p class="mt-2 text-sm text-gray-500">Test business logic against historical payloads before deploying to production.</p>
        </div>
        <div>
            <button wire:click="runSimulation" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg wire:loading wire:target="runSimulation" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Run Simulation
            </button>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 flex overflow-hidden mx-4 mb-4 space-x-4">
        
        <!-- Left Pane: Configuration & Editor -->
        <div class="w-1/3 flex flex-col space-y-4">
            
            <!-- Payload Source -->
            <div class="bg-white shadow rounded-lg border border-gray-200 p-4">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Payload Injection</h4>
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center text-sm font-medium text-gray-700">
                            <input type="radio" wire:model="payloadSource" value="mock_recent" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-600">
                            <span class="ml-2">Recent System Mocks (10 items)</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center text-sm font-medium text-gray-700">
                            <input type="radio" wire:model="payloadSource" value="historical_trace" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-600">
                            <span class="ml-2">Inject specific Trace ID Payload</span>
                        </label>
                        @if($payloadSource === 'historical_trace')
                        <input type="text" wire:model.defer="traceId" placeholder="trace-uuid-1234" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rule Editor -->
            <div class="bg-white shadow rounded-lg border border-gray-200 flex-1 flex flex-col overflow-hidden">
                <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Policy Rules (YAML Sandbox)</span>
                </div>
                <textarea wire:model.defer="policyRules" class="flex-1 w-full bg-gray-900 text-green-400 p-4 font-mono text-sm border-0 focus:ring-0 resize-none outline-none" spellcheck="false"></textarea>
            </div>

        </div>

        <!-- Right Pane: Execution Results -->
        <div class="w-2/3 bg-white shadow rounded-lg border border-gray-200 flex flex-col overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Simulation Output</span>
            </div>
            
            <div class="flex-1 p-6 overflow-y-auto bg-gray-50">
                @if($simulationResult)
                    
                    <!-- KPI Header -->
                    <div class="grid grid-cols-4 gap-4 mb-8 text-center">
                        <div class="bg-white p-4 rounded-lg border shadow-sm">
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Payloads Evaluated</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $simulationResult['total_payloads_processed'] }}</dd>
                        </div>
                        <div class="bg-white p-4 rounded-lg border shadow-sm">
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Passed</dt>
                            <dd class="mt-1 text-2xl font-bold text-green-600">{{ $simulationResult['passed_count'] }}</dd>
                        </div>
                        <div class="bg-white p-4 rounded-lg border shadow-sm">
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Failed / Dropped</dt>
                            <dd class="mt-1 text-2xl font-bold text-red-600">{{ $simulationResult['failed_count'] }}</dd>
                        </div>
                        <div class="bg-white p-4 rounded-lg border shadow-sm">
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Avg Latency</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $simulationResult['average_latency_ms'] }}ms</dd>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <!-- Sample Failures -->
                        <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
                            <div class="px-4 py-2 border-b border-red-200 bg-red-50">
                                <h4 class="font-bold text-red-800 text-xs uppercase tracking-wider">Sample Failures</h4>
                            </div>
                            <div class="p-4 space-y-3">
                                @forelse($simulationResult['sample_failures'] as $fail)
                                    <div class="border border-red-100 bg-red-50/30 rounded p-2 text-xs">
                                        <div class="font-bold text-red-700 mb-1">Reason: {{ $fail['reason'] }}</div>
                                        <pre class="text-[10px] text-gray-600 font-mono mt-1 overflow-x-auto">{{ json_encode($fail['payload'], JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 italic text-center py-4">No payloads failed.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Sample Passes -->
                        <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
                            <div class="px-4 py-2 border-b border-green-200 bg-green-50">
                                <h4 class="font-bold text-green-800 text-xs uppercase tracking-wider">Sample Passes</h4>
                            </div>
                            <div class="p-4 space-y-3">
                                @forelse($simulationResult['sample_passes'] as $pass)
                                    <div class="border border-green-100 bg-green-50/30 rounded p-2 text-xs">
                                        <pre class="text-[10px] text-gray-600 font-mono overflow-x-auto">{{ json_encode($pass['payload'], JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 italic text-center py-4">No payloads passed.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                @else
                    <div class="h-full flex flex-col items-center justify-center text-gray-500">
                        <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <p class="text-sm font-medium text-gray-900">Sandbox Ready</p>
                        <p class="text-xs mt-1">Configure your policy rules on the left and hit "Run Simulation".</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

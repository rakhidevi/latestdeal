<div class="h-[calc(100vh-100px)] flex flex-col">
    <!-- Header -->
    <div class="mb-4 border-b border-gray-200 pb-4 flex justify-between items-center px-4 pt-4">
        <div>
            <h3 class="text-2xl font-bold leading-6 text-gray-900">Discovery Playground</h3>
            <p class="mt-2 text-sm text-gray-500">Test Discovery Profiles against Provider Query Builders before deploying workers.</p>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 flex overflow-hidden mx-4 mb-4 space-x-4">
        
        <!-- Left Pane: Configuration -->
        <div class="w-1/3 flex flex-col space-y-4">
            
            <div class="bg-white shadow rounded-lg border border-gray-200 flex flex-col h-full">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Search Target Constraints</span>
                    <button wire:click="clearConstraints" class="text-xs text-red-600 hover:underline">Clear All</button>
                </div>
                
                <div class="p-4 flex-1 space-y-4 overflow-y-auto">
                    <!-- Session Errors -->
                    @if (session()->has('error'))
                        <div class="p-2 bg-red-100 border border-red-400 text-red-700 text-xs rounded relative font-bold">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Provider Selection -->
                    <div>
                        <label class="block text-sm font-medium leading-6 text-gray-900">Provider</label>
                        <select wire:model="provider" class="mt-1 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="Amazon">Amazon India</option>
                            <option value="Flipkart">Flipkart</option>
                        </select>
                    </div>

                    <!-- Constraints -->
                    <div class="border-t border-gray-200 pt-4">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Target Brand</label>
                        <input type="text" wire:model.defer="constraints.brand" placeholder="e.g. Nike" class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>

                    <div>
                        <label class="block text-sm font-medium leading-6 text-gray-900">Category Node ID</label>
                        <input type="text" wire:model.defer="constraints.node_id" placeholder="e.g. 1983518031 (Electronics)" class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <p class="mt-1 text-xs text-gray-500">Provider specific category identifier.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium leading-6 text-gray-900">Minimum Discount (%)</label>
                        <input type="number" wire:model.defer="constraints.min_discount" placeholder="e.g. 30" min="0" max="100" class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="p-4 border-t border-gray-200 bg-gray-50">
                    <button wire:click="runDiscovery" class="w-full inline-flex justify-center items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <svg wire:loading wire:target="runDiscovery" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Test Discovery Constraints
                    </button>
                </div>
            </div>

        </div>

        <!-- Right Pane: Execution Results -->
        <div class="w-2/3 bg-white shadow rounded-lg border border-gray-200 flex flex-col overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Discovery Engine Output</span>
            </div>
            
            <div class="flex-1 p-6 overflow-y-auto bg-gray-50">
                @if($simulationResult)
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg border border-indigo-100 shadow-sm">
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Provider Target</dt>
                            <dd class="mt-1 text-2xl font-bold text-indigo-600">{{ $simulationResult['provider'] }}</dd>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-green-100 shadow-sm">
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Estimated Reachable Targets</dt>
                            <dd class="mt-1 text-2xl font-bold text-green-600">~{{ number_format($simulationResult['estimated_targets']) }}</dd>
                        </div>
                    </div>

                    <!-- Parameter Breakdown -->
                    <div class="bg-white rounded-lg border shadow-sm mb-6 overflow-hidden">
                        <div class="px-4 py-2 border-b border-gray-200 bg-gray-100">
                            <h4 class="font-bold text-gray-800 text-xs uppercase tracking-wider">URL Query Parameters Generated</h4>
                        </div>
                        <div class="p-4">
                            @if(!empty($simulationResult['parameters']))
                                <ul class="divide-y divide-gray-100">
                                    @foreach($simulationResult['parameters'] as $key => $val)
                                    <li class="py-2 flex justify-between font-mono text-sm">
                                        <span class="font-bold text-gray-600">{{ $key }}</span>
                                        <span class="text-indigo-600 truncate max-w-lg">{{ $val }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500 italic">No parameters generated.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Generated URLs -->
                    <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
                        <div class="px-4 py-2 border-b border-gray-200 bg-gray-100">
                            <h4 class="font-bold text-gray-800 text-xs uppercase tracking-wider">Sample Seed URLs (Page 1 & 2)</h4>
                        </div>
                        <div class="p-4 space-y-3">
                            @foreach($simulationResult['generated_urls'] as $url)
                                <div class="bg-gray-900 rounded p-3 overflow-x-auto">
                                    <pre class="text-xs text-green-400 font-mono">{{ $url }}</pre>
                                </div>
                            @endforeach
                        </div>
                    </div>

                @else
                    <div class="h-full flex flex-col items-center justify-center text-gray-500">
                        <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <p class="text-sm font-medium text-gray-900">Playground Ready</p>
                        <p class="text-xs mt-1 text-center max-w-sm">Enter Target Constraints on the left to see exactly how the Discovery Engine translates them into Provider Queries.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

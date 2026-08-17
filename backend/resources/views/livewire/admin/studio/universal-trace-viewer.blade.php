<div>
    <!-- Header & Search -->
    <div class="mb-4 border-b border-gray-200 pb-4 sm:flex sm:items-center sm:justify-between">
        <div>
            <h3 class="text-2xl font-bold leading-6 text-gray-900">Universal Trace Viewer</h3>
            <p class="mt-2 max-w-4xl text-sm text-gray-500">Reconstruct the entire lifecycle of any entity across the Universal Commerce Data Platform.</p>
        </div>
        <div class="mt-3 sm:ml-4 sm:mt-0 flex w-full max-w-md">
            <input type="text" wire:model.defer="searchQuery" wire:keydown.enter="performSearch" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Trace ID, Target UUID, ASIN...">
            <button wire:click="performSearch" class="ml-2 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Search</button>
        </div>
    </div>

    <!-- Breadcrumbs & Status -->
    @if($activeTrace)
    <nav class="flex mb-6 text-sm text-gray-500 font-medium space-x-2" aria-label="Breadcrumb">
        <a href="#" class="hover:text-gray-900">Platform</a>
        <span>&gt;</span>
        <a href="#" class="hover:text-gray-900">Trace</a>
        <span>&gt;</span>
        <span class="text-indigo-600">{{ $activeTrace['trace_id'] }}</span>
    </nav>
    @endif

    @if($activeTrace)
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        
        <!-- Left Column: Timeline -->
        <div class="lg:col-span-1">
            <h4 class="text-sm font-semibold text-gray-900 mb-4">Lifecycle Timeline</h4>
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($activeTrace['nodes'] as $index => $node)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 {{ isset($node['status']) && $node['status'] == 'warning' ? 'bg-yellow-300 border-dashed border-2' : 'bg-gray-200' }}" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3 cursor-pointer p-2 rounded-lg {{ $expandedStage === $node['id'] ? 'bg-indigo-50 ring-1 ring-indigo-200' : 'hover:bg-gray-50' }}" wire:click="toggleStage('{{ $node['id'] }}')">
                                <div>
                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white {{ isset($node['status']) && $node['status'] == 'warning' ? 'bg-yellow-500' : 'bg-green-500' }}">
                                        @if(isset($node['status']) && $node['status'] == 'warning')
                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        @else
                                        <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                        </svg>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $node['name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $node['type'] }} | <span class="font-medium text-gray-700">{{ $node['duration_ms'] }}ms</span></p>
                                    </div>
                                    <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                        <time datetime="{{ $node['timestamp'] }}">{{ explode('T', $node['timestamp'])[1] }}</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Center Column: Deep Inspection -->
        <div class="lg:col-span-2 relative h-[600px]">
            @if($expandedStage)
                @php
                    $stageData = collect($activeTrace['nodes'])->firstWhere('id', $expandedStage);
                @endphp
                <livewire:admin.studio.universal-inspector :nodeData="$stageData" :key="$expandedStage" />
            @else
                <div class="flex h-full items-center justify-center text-gray-500 bg-white shadow rounded-lg border border-gray-200">
                    <p>Select a timeline stage on the left to inspect its payloads and decision mathematics.</p>
                </div>
            @endif
        </div>

        <!-- Right Column: Metadata & Actions -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Actions -->
            <div class="bg-white shadow rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Trace Actions</h4>
                <button wire:click="triggerReplay" class="w-full mb-2 inline-flex justify-center items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Replay Branch
                </button>
                <button wire:click="triggerComparison" class="w-full mb-2 inline-flex justify-center items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Compare Execution
                </button>
                <button wire:click="triggerFork" class="w-full inline-flex justify-center items-center rounded-md bg-yellow-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-400">
                    Fork & Experiment
                </button>
            </div>

            <!-- Metadata -->
            <div class="bg-white shadow rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Trace Relationships</h4>
                <dl class="divide-y divide-gray-100 text-sm">
                    <div class="px-2 py-2 sm:grid sm:grid-cols-3 sm:gap-2">
                        <dt class="font-medium text-gray-900 text-xs">Origin Trace</dt>
                        <dd class="text-gray-700 sm:col-span-2 text-right text-xs">
                            <a href="#" class="text-indigo-600 hover:underline">trace-base-001</a>
                        </dd>
                    </div>
                </dl>
            </div>


        </div>
    </div>

    <!-- Bottom: UCDP Event Stream -->
    <div class="mt-8 bg-gray-900 rounded-lg shadow-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-800 flex justify-between items-center bg-black">
            <h4 class="text-sm font-mono text-green-400">UCDP Immutable Event Stream</h4>
            <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
        </div>
        <div class="p-4 font-mono text-xs text-gray-300 h-64 overflow-y-auto space-y-1">
            @foreach($traceEvents as $event)
            <div class="flex space-x-4 hover:bg-gray-800 px-2 py-1 rounded cursor-pointer">
                <span class="text-gray-500">[{{ $event['timestamp'] }}]</span>
                <span class="text-blue-400 font-bold w-48">{{ $event['event'] }}</span>
                <span class="text-yellow-300">uuid: <a href="#" class="hover:underline">{{ $event['uuid'] }}</a></span>
            </div>
            @endforeach
            <div class="animate-pulse flex space-x-4 px-2 py-1">
                <span class="text-gray-600">_</span>
            </div>
        </div>
    </div>
    @elseif(count($searchResults) > 1)
    <div class="bg-white shadow overflow-hidden sm:rounded-md mt-6">
        <ul role="list" class="divide-y divide-gray-200">
            @foreach($searchResults as $result)
            <li>
                <a href="#" wire:click.prevent="selectTrace('{{ $result['trace_id'] }}')" class="block hover:bg-gray-50">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-indigo-600 truncate">{{ $result['trace_id'] }}</p>
                            <div class="ml-2 flex-shrink-0 flex">
                                <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $result['match_type'] }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    Entity Matched: {{ $result['entity'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    @else
    <div class="text-center mt-20">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
        <h3 class="mt-2 text-sm font-semibold text-gray-900">No Trace Selected</h3>
        <p class="mt-1 text-sm text-gray-500">Enter a Trace ID, Target UUID, or ASIN in the search bar to reconstruct its lifecycle.</p>
    </div>
    @endif
</div>

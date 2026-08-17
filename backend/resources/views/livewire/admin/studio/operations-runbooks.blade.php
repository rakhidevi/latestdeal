<div class="h-[calc(100vh-100px)] flex flex-col p-4 bg-gray-50">
    <!-- Header -->
    <div class="mb-4 border-b border-gray-200 pb-4">
        <h3 class="text-2xl font-bold leading-6 text-gray-900">Operations Runbooks</h3>
        <p class="mt-2 text-sm text-gray-500">Interactive operational knowledge base for identifying and resolving platform failure modes.</p>
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 flex overflow-hidden bg-white shadow rounded-lg border border-gray-200">
        
        <!-- Left Pane: Runbook Catalog -->
        <div class="w-1/3 border-r border-gray-200 bg-gray-50 flex flex-col overflow-y-auto">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-100">
                <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Runbook Catalog</span>
            </div>
            <div class="p-2 space-y-2">
                @foreach(collect($catalog)->groupBy('category') as $category => $runbooks)
                    <div class="mb-4">
                        <h4 class="px-2 text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $category }}</h4>
                        <ul class="space-y-1">
                            @foreach($runbooks as $runbook)
                                <li>
                                    <button wire:click="selectRunbook('{{ $runbook['id'] }}')" 
                                            class="w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors flex justify-between items-center
                                            {{ optional($activeRunbook)['id'] === $runbook['id'] ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-200' }}">
                                        <span class="truncate">{{ $runbook['title'] }}</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold {{ $runbook['severity'] === 'CRITICAL' ? 'bg-red-200 text-red-800' : ($runbook['severity'] === 'HIGH' ? 'bg-orange-200 text-orange-800' : 'bg-gray-200 text-gray-800') }}">
                                            {{ $runbook['severity'] }}
                                        </span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right Pane: Interactive Runbook -->
        <div class="w-2/3 flex flex-col overflow-y-auto bg-white">
            @if($activeRunbook)
                
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-xl font-bold text-gray-900">{{ $activeRunbook['title'] }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $activeRunbook['description'] }}</p>
                </div>

                <div class="p-6 space-y-8">
                    
                    <!-- Identification -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider border-b pb-1 mb-3">Trigger Conditions</h4>
                            <ul class="list-disc list-inside text-sm text-gray-800 space-y-1">
                                @foreach($activeRunbook['trigger_conditions'] as $condition)
                                    <li>{!! preg_replace('/\*\*(.*?)\*\*/', '<span class="font-bold">$1</span>', $condition) !!}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider border-b pb-1 mb-3">Symptoms</h4>
                            <ul class="list-disc list-inside text-sm text-gray-800 space-y-1">
                                @foreach($activeRunbook['symptoms'] as $symptom)
                                    <li>{{ $symptom }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Automated Diagnostics -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
                            <h4 class="text-sm font-bold text-gray-900">Automated Diagnostics</h4>
                            <button wire:click="runDiagnostics" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded shadow inline-flex items-center">
                                <svg wire:loading wire:target="runDiagnostics" class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Run Checks
                            </button>
                        </div>

                        @if($diagnosticResults)
                            <ul class="space-y-2">
                                @foreach($diagnosticResults as $result)
                                    <li class="flex justify-between items-center bg-white p-2 border rounded text-sm">
                                        <span class="font-medium text-gray-700">{{ $result['check'] }}</span>
                                        <div class="flex items-center space-x-3">
                                            <span class="text-gray-500 text-xs">{{ $result['message'] }}</span>
                                            <span class="px-2 py-0.5 rounded text-xs font-bold {{ $result['status'] === 'PASS' ? 'bg-green-100 text-green-800' : ($result['status'] === 'WARN' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ $result['status'] }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500 italic text-center py-4">Click "Run Checks" to automatically evaluate system health against this runbook's criteria.</p>
                        @endif
                    </div>

                    <!-- Recommended Recovery -->
                    <div>
                        <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider border-b border-indigo-200 pb-1 mb-3">Recommended Recovery Steps</h4>
                        <ol class="list-decimal list-inside text-sm text-gray-800 space-y-2 bg-indigo-50/50 p-4 rounded border border-indigo-100">
                            @foreach($activeRunbook['recovery_steps'] as $step)
                                <li>{!! preg_replace('/\*\*(.*?)\*\*/', '<span class="font-bold text-indigo-900">$1</span>', $step) !!}</li>
                            @endforeach
                        </ol>
                    </div>

                    <!-- Deep Links & Escalation -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider border-b pb-1 mb-3">Related Workspaces</h4>
                            <ul class="space-y-2">
                                @foreach($activeRunbook['deep_links'] as $link)
                                    <li>
                                        <a href="{{ $link['url'] }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            {{ $link['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-red-500 uppercase tracking-wider border-b border-red-200 pb-1 mb-3">Escalation Path</h4>
                            <p class="text-sm text-gray-800 bg-red-50 p-3 rounded border border-red-100">{{ $activeRunbook['escalation_path'] }}</p>
                        </div>
                    </div>

                </div>

            @else
                <!-- Empty State -->
                <div class="flex-1 flex items-center justify-center text-gray-500 flex-col h-full">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <p class="text-sm font-medium">Select an operational runbook from the catalog.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="h-[calc(100vh-100px)] flex flex-col">
    <!-- Header -->
    <div class="mb-4 border-b border-gray-200 pb-4 flex justify-between items-center px-4 pt-4">
        <div>
            <h3 class="text-2xl font-bold leading-6 text-gray-900">Knowledge Management System (KMS)</h3>
            <p class="mt-2 text-sm text-gray-500">Manage and compile Brands, Nodes, Policies, and Profiles. Direct production edits are prohibited.</p>
        </div>
        @if(session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded relative text-sm">
                {{ session('message') }}
            </div>
        @endif
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 flex overflow-hidden bg-white shadow rounded-lg border border-gray-200 mx-4 mb-4">
        
        <!-- Left Pane: File Explorer -->
        <div class="w-1/4 border-r border-gray-200 bg-gray-50 flex flex-col overflow-y-auto">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-100">
                <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Knowledge Entities</span>
            </div>
            <div class="p-2 space-y-4">
                @foreach($entityTree as $group => $items)
                    <div>
                        <h4 class="px-2 text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">{{ $group }}</h4>
                        <ul class="space-y-1">
                            @foreach($items as $item)
                                <li>
                                    <button wire:click="selectEntity('{{ $item['id'] }}')" 
                                            class="w-full text-left px-3 py-1.5 rounded-md text-sm font-medium transition-colors 
                                            {{ $selectedEntityId === $item['id'] ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-200' }}">
                                        <span class="truncate block w-full">{{ $item['name'] }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Middle & Right Panes: Editor and Workflow -->
        <div class="w-3/4 flex flex-col bg-white">
            @if($activeFile)
                
                <!-- Editor Header & Toolbar -->
                <div class="px-4 py-3 border-b border-gray-200 bg-white flex justify-between items-center">
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">{{ $activeFile['name'] }}</h4>
                        <p class="text-xs text-gray-500">Version: <span class="font-mono">{{ $activeFile['version'] }}</span> | Status: <span class="uppercase font-bold text-indigo-600">{{ $activeFile['status'] }}</span></p>
                    </div>
                    <!-- KMS Workflow Pipeline -->
                    <div class="flex space-x-2 text-xs">
                        <button wire:click="handleWorkflowAction('validate')" class="px-3 py-1.5 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 font-medium">1. Validate</button>
                        <button wire:click="handleWorkflowAction('diff')" class="px-3 py-1.5 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 font-medium">2. Diff</button>
                        <button wire:click="handleWorkflowAction('compile')" class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-500 font-bold shadow">3. Compile & Publish</button>
                        <button wire:click="handleWorkflowAction('rollback')" class="px-3 py-1.5 border border-red-300 text-red-600 rounded hover:bg-red-50 font-medium ml-4">Rollback</button>
                    </div>
                </div>

                <!-- YAML Editor Area -->
                <div class="flex-1 bg-gray-900 flex flex-col relative">
                    <div class="px-4 py-1 bg-gray-800 border-b border-gray-700 text-xs text-gray-400 font-mono flex justify-between">
                        <span>source_draft.yaml</span>
                        <span>[YAML]</span>
                    </div>
                    <textarea wire:model.defer="editorContent" class="flex-1 w-full bg-gray-900 text-gray-300 p-4 font-mono text-sm border-0 focus:ring-0 resize-none outline-none" spellcheck="false"></textarea>
                </div>

            @else
                <!-- Empty State -->
                <div class="flex-1 flex items-center justify-center text-gray-500 flex-col">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p class="text-sm">Select a knowledge entity from the sidebar to manage its lifecycle.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Impact Analysis Modal -->
    @if($showImpactModal)
    <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Pre-Flight Impact Analysis</h3>
                                <div class="mt-4 border border-gray-200 rounded p-4 bg-gray-50 space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-xs font-semibold text-gray-500 uppercase">Affected Providers</dt>
                                            <dd class="text-sm font-bold text-gray-900">{{ implode(', ', $impactAnalysis['affected_providers']) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold text-gray-500 uppercase">Estimated Targets Changed</dt>
                                            <dd class="text-sm font-bold text-red-600">{{ $impactAnalysis['estimated_targets_changed'] }}</dd>
                                        </div>
                                        <div class="col-span-2">
                                            <dt class="text-xs font-semibold text-gray-500 uppercase">Affected Discovery Profiles</dt>
                                            <dd class="text-sm text-gray-900">{{ implode(', ', $impactAnalysis['affected_discovery_profiles']) }}</dd>
                                        </div>
                                        <div class="col-span-2">
                                            <dt class="text-xs font-semibold text-gray-500 uppercase">Policies Impacted</dt>
                                            <dd class="text-sm text-gray-900">{{ implode(', ', $impactAnalysis['policies_impacted']) }}</dd>
                                        </div>
                                    </div>
                                    <div class="border-t pt-4">
                                        <h5 class="text-xs font-semibold text-gray-500 uppercase mb-2">Entities Changed</h5>
                                        <ul class="text-sm space-y-1">
                                            @foreach($impactAnalysis['entities_changed'] as $change)
                                                <li><span class="font-bold {{ $change['type'] === 'Added' ? 'text-green-600' : 'text-blue-600' }}">[{{ $change['type'] }}]</span> {{ $change['entity'] }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="confirmPublish" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Approve & Publish to UCDP</button>
                        <button type="button" wire:click="$set('showImpactModal', false)" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

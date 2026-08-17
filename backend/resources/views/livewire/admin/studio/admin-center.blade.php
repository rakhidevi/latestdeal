<div class="h-[calc(100vh-100px)] flex flex-col p-4 bg-gray-50 overflow-y-auto">
    <!-- Header -->
    <div class="mb-4 border-b border-gray-200 pb-4">
        <h3 class="text-2xl font-bold leading-6 text-gray-900">Platform Control Plane</h3>
        <p class="mt-2 text-sm text-gray-500">Master administrative controls for the Discovery Engine. All actions are strictly audited and logged.</p>
        
        <!-- Alerts -->
        @if (session()->has('message'))
            <div class="mt-4 p-2 bg-green-100 border border-green-400 text-green-700 text-sm rounded font-medium">
                {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mt-4 p-2 bg-red-600 text-white text-sm rounded font-bold animate-pulse">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Left Column -->
        <div class="space-y-6">
            
            <!-- Provider Controls -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <h4 class="font-bold text-gray-800 text-sm">Provider Subsystems</h4>
                </div>
                <div class="p-4 space-y-4">
                    @foreach($platformState['providers'] as $key => $provider)
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-bold text-gray-900">{{ $provider['name'] }}</span>
                                <p class="text-xs text-gray-500">Toggle ingestion and processing for this provider.</p>
                            </div>
                            <button wire:click="toggleControl('providers', '{{ $key }}', {{ $provider['enabled'] ? 'true' : 'false' }})" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $provider['enabled'] ? 'bg-indigo-600' : 'bg-gray-200' }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $provider['enabled'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Feature Flags & Modes -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <h4 class="font-bold text-gray-800 text-sm">Operational Modes</h4>
                </div>
                <div class="p-4 space-y-4">
                    @foreach($platformState['features'] as $key => $feature)
                        @if($key !== 'kill_switch')
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-bold text-gray-900">{{ $feature['name'] }}</span>
                            </div>
                            <button wire:click="toggleControl('features', '{{ $key }}', {{ $feature['enabled'] ? 'true' : 'false' }})" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $feature['enabled'] ? 'bg-indigo-600' : 'bg-gray-200' }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $feature['enabled'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                        @endif
                    @endforeach
                    
                    <div class="border-t pt-4 mt-4">
                        <label class="block text-sm font-bold text-gray-900">Canary Rollout Percentage</label>
                        <div class="flex items-center mt-2">
                            <input type="range" wire:model.defer="platformState.rollout.canary_percentage" min="0" max="100" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                            <span class="ml-4 font-mono font-bold text-indigo-600 w-12">{{ $platformState['rollout']['canary_percentage'] }}%</span>
                        </div>
                        <button wire:click="updateRollout($wire.platformState.rollout.canary_percentage)" class="mt-2 text-xs bg-gray-100 hover:bg-gray-200 border rounded px-2 py-1">Apply Rollout</button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="space-y-6">

            <!-- Governance Status -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <h4 class="font-bold text-gray-800 text-sm">Active Governance References</h4>
                </div>
                <div class="p-4">
                    <ul class="divide-y divide-gray-100 text-sm">
                        <li class="py-2 flex justify-between">
                            <span class="text-gray-500">Knowledge Graph Version</span>
                            <span class="font-mono font-bold text-indigo-600">{{ $platformState['governance']['knowledge_version'] }}</span>
                        </li>
                        <li class="py-2 flex justify-between">
                            <span class="text-gray-500">Policy Ruleset</span>
                            <span class="font-mono font-bold text-indigo-600">{{ $platformState['governance']['policy_version'] }}</span>
                        </li>
                        <li class="py-2 flex justify-between">
                            <span class="text-gray-500">Discovery Profiles</span>
                            <span class="font-mono font-bold text-indigo-600">{{ $platformState['governance']['discovery_profile_set'] }}</span>
                        </li>
                    </ul>
                    <div class="mt-4 text-right">
                        <a href="{{ route('admin.studio.knowledge-center') }}" class="text-xs text-indigo-600 hover:underline">Manage Governance &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Diagnostics -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <h4 class="font-bold text-gray-800 text-sm">Platform Diagnostics</h4>
                </div>
                <div class="p-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="block text-xs text-gray-500">Worker Fleet</span>
                        <span class="font-bold {{ $platformState['diagnostics']['worker_status'] === 'HEALTHY' ? 'text-green-600' : 'text-red-600' }}">{{ $platformState['diagnostics']['worker_status'] }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500">Event Store</span>
                        <span class="font-bold {{ $platformState['diagnostics']['event_store'] === 'HEALTHY' ? 'text-green-600' : 'text-red-600' }}">{{ $platformState['diagnostics']['event_store'] }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500">Queue Broker</span>
                        <span class="font-bold text-green-600">{{ $platformState['diagnostics']['queue_connectivity'] }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500">Replay Engine</span>
                        <span class="font-bold {{ $platformState['diagnostics']['replay_engine'] === 'HEALTHY' ? 'text-green-600' : 'text-yellow-600' }}">{{ $platformState['diagnostics']['replay_engine'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-50 shadow rounded-lg border border-red-300">
                <div class="px-4 py-3 border-b border-red-300 bg-red-100">
                    <h4 class="font-bold text-red-900 text-sm">Danger Zone</h4>
                </div>
                <div class="p-4">
                    <p class="text-xs text-red-700 mb-4">Activating the emergency kill switch will immediately halt all workers, flush active queues, and severe provider connections. This action requires authorization.</p>
                    <button wire:click="$set('showKillSwitchModal', true)" class="w-full flex justify-center items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-bold uppercase tracking-wider text-sm shadow">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Initiate Kill Switch
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Kill Switch Confirmation Modal -->
    @if($showKillSwitchModal)
    <div class="relative z-50">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-red-600 px-4 py-4 sm:px-6">
                        <h3 class="text-lg font-bold leading-6 text-white text-center uppercase">Critical Action Required</h3>
                    </div>
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 text-center">
                        <svg class="mx-auto h-16 w-16 text-red-600 mb-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-sm text-gray-700 font-bold mb-2">You are about to sever all active connections and halt the discovery platform.</p>
                        <p class="text-xs text-gray-500 mb-4">Type <span class="font-mono bg-gray-100 text-red-600 px-1">TERMINATE</span> below to confirm.</p>
                        
                        <input type="text" wire:model.defer="killSwitchConfirmCode" class="block w-full rounded-md border-0 py-2 text-center font-mono font-bold text-red-600 shadow-sm ring-1 ring-inset ring-red-300 focus:ring-2 focus:ring-inset focus:ring-red-600 sm:text-sm sm:leading-6">
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="triggerKillSwitch" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 sm:ml-3 sm:w-auto">EXECUTE</button>
                        <button type="button" wire:click="$set('showKillSwitchModal', false)" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Abort</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

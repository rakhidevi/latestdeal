<div>
    @section('title', 'Segments')
    
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Segments</h2>
            <p class="text-slate-500 mt-1">Group subscribers dynamically based on rules and behaviors.</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="createSegment" class="px-4 py-2 bg-blue-600 text-white rounded-xl shadow-sm text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Create Segment
            </button>
        </div>
    </div>

    @if($isEditing)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-800">Create Dynamic Segment</h3>
                <button wire:click="cancelEdit" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Segment Name</label>
                        <input type="text" class="w-full border-slate-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. Engaged VIPs">
                    </div>
                    
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <div class="text-sm font-bold text-slate-700 mb-3">Segment Rules</div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-slate-500 w-16">Include</span>
                                <select class="border-slate-200 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option>Subscribers with Tag</option>
                                    <option>Active Status</option>
                                    <option>Opened Email</option>
                                </select>
                                <select class="border-slate-200 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option>equals</option>
                                </select>
                                <input type="text" class="border-slate-200 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="VIP">
                                <button class="text-slate-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <select class="border-slate-200 rounded-md text-sm font-bold text-slate-700 bg-white focus:border-blue-500 focus:ring-blue-500 w-16">
                                    <option>AND</option>
                                    <option>OR</option>
                                </select>
                                <select class="border-slate-200 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option>Activity status</option>
                                </select>
                                <select class="border-slate-200 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option>is</option>
                                </select>
                                <select class="border-slate-200 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500 w-32">
                                    <option>Active</option>
                                    <option>Unengaged</option>
                                </select>
                                <button class="text-slate-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </div>
                            
                            <button class="mt-2 text-sm text-blue-600 font-medium hover:text-blue-700 flex items-center gap-1">
                                <i data-lucide="plus" class="w-3 h-3"></i> Add Rule
                            </button>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                        <div class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-2">Audience Estimate</div>
                        <div class="text-3xl font-bold text-blue-900">0</div>
                        <p class="text-xs text-blue-700 mt-1">subscribers match these rules.</p>
                    </div>
                    
                    <button class="w-full mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg shadow-sm text-sm font-medium hover:bg-blue-700 transition-colors">
                        Save Segment
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search segments..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Segment Name</th>
                        <th class="px-6 py-3 font-semibold">Type</th>
                        <th class="px-6 py-3 font-semibold">Rules</th>
                        <th class="px-6 py-3 font-semibold">Created</th>
                        <th class="px-6 py-3 font-semibold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($segments as $segment)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $segment->name }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 capitalize">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $segment->type === 'dynamic' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $segment->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                <!-- Dummy rule display -->
                                Tag = "VIP" AND Status = "Active"
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ $segment->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="mx-auto w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                    <i data-lucide="filter" class="w-6 h-6 text-slate-300"></i>
                                </div>
                                <p class="font-medium text-slate-700">No segments found</p>
                                <p class="text-sm mt-1">Create dynamic segments to group your subscribers.</p>
                                <button wire:click="createSegment" class="mt-4 px-4 py-2 bg-white border border-slate-200 rounded-lg shadow-sm text-sm font-medium text-blue-600 hover:bg-slate-50">
                                    Create First Segment
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($segments->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $segments->links() }}
            </div>
        @endif
    </div>
</div>

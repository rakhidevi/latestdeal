<div>
    @section('title', 'Subscribers')
    
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Subscribers</h2>
            <p class="text-slate-500 mt-1">Manage your audiences, view subscriber statistics, and organize by tags.</p>
        </div>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                <i data-lucide="download" class="w-4 h-4 inline mr-1"></i> Export
            </button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-xl shadow-sm text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-2">
                <i data-lucide="upload" class="w-4 h-4"></i> Import CSV
            </button>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <!-- Sidebar -->
        <div class="w-full md:w-64 flex-shrink-0">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 px-2">Audiences</div>
                <button wire:click="setFilter('all')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeFilter === 'all' && !$activeTag ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="users" class="w-4 h-4 {{ $activeFilter === 'all' && !$activeTag ? 'text-blue-500' : 'text-slate-400' }}"></i>
                        Master List
                    </div>
                </button>
                <button wire:click="setFilter('subscribed')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeFilter === 'subscribed' ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="user-check" class="w-4 h-4 {{ $activeFilter === 'subscribed' ? 'text-blue-500' : 'text-slate-400' }}"></i>
                        Active Shoppers
                    </div>
                </button>
                <button wire:click="setFilter('unengaged')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeFilter === 'unengaged' ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="user-minus" class="w-4 h-4 {{ $activeFilter === 'unengaged' ? 'text-blue-500' : 'text-slate-400' }}"></i>
                        Unengaged
                    </div>
                </button>
                <button wire:click="setFilter('suppressed')" class="mt-1 w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeFilter === 'suppressed' ? 'bg-red-50 text-red-700' : 'text-slate-600 hover:bg-slate-50' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="user-x" class="w-4 h-4 {{ $activeFilter === 'suppressed' ? 'text-red-500' : 'text-slate-400' }}"></i>
                        Suppression List
                    </div>
                </button>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 px-2 flex justify-between items-center">
                    Tags
                    <button class="text-blue-600 hover:text-blue-700"><i data-lucide="plus" class="w-3 h-3"></i></button>
                </div>
                @foreach($this->tags as $tag)
                    <button wire:click="setTagFilter('{{ $tag->slug }}')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeTag === $tag->slug ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full" style="background-color: {{ $tag->color ?? '#cbd5e1' }}"></span>
                            {{ $tag->name }}
                        </div>
                    </button>
                @endforeach
                @if(count($this->tags) === 0)
                    <div class="px-2 text-xs text-slate-400 italic">No tags created yet.</div>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search subscribers..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Subscriber</th>
                                <th class="px-6 py-3 font-semibold">Status</th>
                                <th class="px-6 py-3 font-semibold">Tags</th>
                                <th class="px-6 py-3 font-semibold">Added</th>
                                <th class="px-6 py-3 font-semibold"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($subscribers as $subscriber)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-800">{{ $subscriber->first_name }} {{ $subscriber->last_name }}</div>
                                        <div class="text-slate-500 text-xs">{{ $subscriber->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($subscriber->status === 'subscribed' || $subscriber->status === 'active')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Subscribed</span>
                                        @elseif($subscriber->status === 'unengaged')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Unengaged</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 capitalize">{{ $subscriber->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($subscriber->tags as $tag)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                            <button class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-slate-50 text-slate-400 hover:bg-slate-200 hover:text-slate-600 border border-dashed border-slate-300">
                                                <i data-lucide="plus" class="w-3 h-3"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ $subscriber->created_at->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button class="text-slate-400 hover:text-blue-600"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                        <div class="mx-auto w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                            <i data-lucide="users" class="w-6 h-6 text-slate-300"></i>
                                        </div>
                                        <p class="font-medium text-slate-700">No subscribers found</p>
                                        <p class="text-sm mt-1">Try adjusting your filters or search query.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($subscribers->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $subscribers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

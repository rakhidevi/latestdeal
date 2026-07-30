<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Template Library</h2>
            <p class="text-sm text-slate-500 mt-1">Choose from 20+ predesigned responsive templates or build your own.</p>
        </div>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors shadow-sm flex items-center gap-2">
                <i data-lucide="code" class="w-4 h-4"></i> Custom HTML
            </button>
            <button class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700 transition-colors shadow-sm flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> New Template
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
            {{ session('message') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex space-x-1 overflow-x-auto w-full sm:w-auto">
            @foreach($categories as $key => $label)
                <button wire:click="$set('category', '{{ $key }}')" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ $category === $key ? 'bg-red-50 text-red-700 border border-red-200' : 'text-slate-600 hover:bg-slate-50 border border-transparent' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        
        <div class="relative w-full sm:w-72 flex-shrink-0">
            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search templates..." class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
        </div>
    </div>

    <!-- Template Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($templates as $template)
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-lg transition-all group flex flex-col h-full">
                <!-- Thumbnail -->
                <div class="relative h-48 w-full bg-slate-100 overflow-hidden">
                    <img src="{{ $template['thumbnail'] }}" alt="{{ $template['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    
                    <!-- Overlay Actions -->
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-3">
                        <button wire:click="selectTemplate({{ $template['id'] }})" class="px-5 py-2 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                            Use Template
                        </button>
                        <button class="px-5 py-2 bg-white text-slate-800 text-sm font-bold rounded-lg hover:bg-slate-50 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300 delay-75">
                            Preview
                        </button>
                    </div>

                    <!-- Badges -->
                    <div class="absolute top-3 left-3">
                        <span class="px-2 py-1 bg-white/90 backdrop-blur text-slate-700 text-[10px] font-bold rounded shadow-sm uppercase tracking-wider">
                            {{ $template['type'] }}
                        </span>
                    </div>
                </div>

                <!-- Info -->
                <div class="p-4 flex-1 flex flex-col">
                    <h3 class="font-bold text-slate-800 text-base line-clamp-1" title="{{ $template['name'] }}">{{ $template['name'] }}</h3>
                    <p class="text-xs text-slate-500 mt-1 uppercase tracking-wide">{{ $categories[$template['category']] ?? 'Unknown' }}</p>
                    
                    <div class="mt-auto pt-4 flex flex-wrap gap-1.5">
                        @foreach($template['tags'] as $tag)
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-medium border border-slate-200">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                    <i data-lucide="layout-template" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">No templates found</h3>
                <p class="text-sm text-slate-500">We couldn't find any templates matching your filters.</p>
                <button wire:click="$set('search', '')" class="mt-4 text-red-600 font-medium hover:text-red-700 text-sm">
                    Clear search
                </button>
            </div>
        @endforelse
    </div>
</div>

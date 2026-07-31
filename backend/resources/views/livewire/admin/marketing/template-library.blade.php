<div>
    @section('title', 'Template Library')

    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Template Library</h2>
            <p class="text-slate-500 mt-1">Manage and organize your predesigned email and message templates.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.marketing.dashboard') }}" class="px-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 inline mr-1"></i> Back
            </a>
            <a href="{{ route('admin.marketing.templates.create') ?? '#' }}" class="px-4 py-2 bg-blue-600 text-white rounded-xl shadow-sm text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> New Template
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3 shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            {{ session('message') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row gap-6 mb-8">
        <!-- Categories Sidebar -->
        <div class="w-full md:w-64 flex-shrink-0">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 px-2">Categories</div>
                @foreach($this->categories as $key => $name)
                    <button wire:click="$set('category', '{{ $key }}')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors mb-1 {{ $category === $key ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        {{ $name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Search & Filter Bar -->
            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex gap-3 mb-6">
                <div class="flex-1 relative">
                    <i data-lucide="search" class="w-5 h-5 absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search templates..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border-transparent focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-lg text-sm transition-all">
                </div>
            </div>

            <!-- Templates Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($this->templates as $template)
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow group flex flex-col">
                        <div class="aspect-[4/3] bg-slate-100 relative overflow-hidden">
                            @if($template->thumbnail_url)
                                <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <i data-lucide="layout-template" class="w-12 h-12"></i>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                <a href="{{ route('admin.marketing.templates.edit', $template->id ?? 0) }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    Edit
                                </a>
                                <button wire:click="deleteTemplate({{ $template->id }})" wire:confirm="Delete this template?" class="px-4 py-2 bg-white text-slate-700 font-medium rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors">
                                    Delete
                                </button>
                            </div>
                            <div class="absolute top-3 left-3 flex flex-wrap gap-1">
                                <span class="px-2 py-1 bg-white/90 backdrop-blur text-slate-700 text-xs font-bold rounded shadow-sm flex items-center gap-1">
                                    <i data-lucide="mail" class="w-3 h-3"></i> {{ $template->type }}
                                </span>
                                @if($template->is_system)
                                    <span class="px-2 py-1 bg-blue-600/90 backdrop-blur text-white text-xs font-bold rounded shadow-sm">
                                        System
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="p-4 flex-1 flex flex-col">
                            <h3 class="font-bold text-slate-800 text-lg mb-1">{{ $template->name }}</h3>
                            <p class="text-sm text-slate-500 mb-3">{{ $this->categories[$template->category] ?? 'General' }}</p>
                            
                            <div class="mt-auto pt-3 border-t border-slate-100 flex gap-2">
                                @if(is_array($template->tags))
                                    @foreach($template->tags as $tag)
                                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs">{{ $tag }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $this->templates->links() }}
            </div>
        </div>
    </div>
</div>

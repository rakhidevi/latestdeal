<div>
    @section('title', 'Asset Manager')
    
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Asset Manager</h2>
            <p class="text-slate-500 mt-1">Manage brand imagery, template assets, and media files for your campaigns.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.marketing.dashboard') }}" class="px-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 inline mr-1"></i> Back
            </a>
            <!-- Uploader Trigger -->
            <label class="px-4 py-2 bg-blue-600 text-white rounded-xl shadow-sm text-sm font-medium hover:bg-blue-700 transition-colors cursor-pointer flex items-center gap-2">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i> Upload Asset
                <input type="file" wire:model="upload" class="hidden" accept="image/*">
            </label>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3 shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            {{ session('message') }}
        </div>
    @endif
    
    @error('upload')
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3 shadow-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
            {{ $message }}
        </div>
    @enderror

    <!-- Upload Progress (Livewire native) -->
    <div x-data="{ isUploading: false, progress: 0 }"
         x-on:livewire-upload-start="isUploading = true"
         x-on:livewire-upload-finish="isUploading = false; $wire.save()"
         x-on:livewire-upload-error="isUploading = false"
         x-on:livewire-upload-progress="progress = $event.detail.progress">
        
        <div x-show="isUploading" class="mb-6 bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-8 h-8 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></div>
            <div class="flex-1">
                <div class="flex justify-between text-sm mb-1 text-slate-600 font-medium">
                    <span>Uploading...</span>
                    <span x-text="progress + '%'"></span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="`width: ${progress}%`"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <!-- Sidebar Folders -->
        <div class="w-full md:w-64 flex-shrink-0">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 px-2">Folders</div>
                <button wire:click="setFolder('all')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeFolder === 'all' ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="folder-open" class="w-4 h-4 {{ $activeFolder === 'all' ? 'text-blue-500' : 'text-slate-400' }}"></i>
                        All Assets
                    </div>
                </button>
                @foreach($this->folders as $folder)
                    <button wire:click="setFolder('{{ strtolower($folder) }}')" class="mt-1 w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeFolder === strtolower($folder) ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="folder" class="w-4 h-4 {{ $activeFolder === strtolower($folder) ? 'text-blue-500' : 'text-slate-400' }}"></i>
                            {{ $folder }}
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Asset Grid -->
        <div class="flex-1">
            @if(count($this->assets) > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($this->assets as $asset)
                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm group">
                            <!-- Image Preview -->
                            <div class="aspect-video bg-slate-100 flex items-center justify-center relative overflow-hidden">
                                @if(str_starts_with($asset->mime_type, 'image/'))
                                    <img src="{{ Storage::disk($asset->storage_disk)->url($asset->path) }}" class="w-full h-full object-cover" alt="{{ $asset->name }}">
                                @else
                                    <i data-lucide="file" class="w-10 h-10 text-slate-300"></i>
                                @endif
                                
                                <!-- Hover Overlay actions -->
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    <button onclick="navigator.clipboard.writeText('{{ Storage::disk($asset->storage_disk)->url($asset->path) }}'); alert('URL Copied!')" class="p-2 bg-white text-slate-700 rounded-lg hover:bg-blue-50 hover:text-blue-600" title="Copy URL">
                                        <i data-lucide="link" class="w-4 h-4"></i>
                                    </button>
                                    <button wire:click="deleteAsset({{ $asset->id }})" wire:confirm="Are you sure you want to delete this asset?" class="p-2 bg-white text-slate-700 rounded-lg hover:bg-red-50 hover:text-red-600" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Meta Data -->
                            <div class="p-3">
                                <div class="font-medium text-sm text-slate-800 truncate" title="{{ $asset->original_filename }}">{{ $asset->name }}</div>
                                <div class="flex justify-between items-center mt-2 text-xs text-slate-500">
                                    <span class="uppercase">{{ explode('/', $asset->mime_type)[1] ?? 'FILE' }}</span>
                                    <span>{{ number_format($asset->file_size / 1024, 0) }} KB</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl border border-slate-200 border-dashed p-12 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="image" class="w-8 h-8 text-slate-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700 mb-1">No Assets Found</h3>
                    <p class="text-slate-500 text-sm mb-6 max-w-md">You haven't uploaded any assets in this folder yet. Upload brand logos, template banners, and product imagery.</p>
                    <label class="px-5 py-2.5 bg-white border-2 border-slate-200 text-slate-700 rounded-xl shadow-sm text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-colors cursor-pointer inline-flex items-center gap-2">
                        <i data-lucide="upload" class="w-4 h-4"></i> Browse Files
                        <input type="file" wire:model="upload" class="hidden" accept="image/*">
                    </label>
                </div>
            @endif
        </div>
    </div>
</div>

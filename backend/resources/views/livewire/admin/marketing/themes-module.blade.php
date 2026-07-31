<div>
    @section('title', 'Theme Manager')
    
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Theme Manager</h2>
            <p class="text-slate-500 mt-1">Define global styling defaults (colors, fonts, spacing) for your email templates.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.marketing.dashboard') }}" class="px-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 inline mr-1"></i> Back
            </a>
            @if(!$isEditing)
                <button wire:click="createTheme" class="px-4 py-2 bg-blue-600 text-white rounded-xl shadow-sm text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create Theme
                </button>
            @endif
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3 shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3 shadow-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
            {{ session('error') }}
        </div>
    @endif

    @if($isEditing)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">{{ $themeId ? 'Edit Theme' : 'New Theme' }}</h3>
            </div>
            
            <div class="p-6">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Theme Name</label>
                    <input type="text" wire:model.defer="name" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., Black Friday 2026">
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Theme Manifest (JSON)</label>
                    <p class="text-xs text-slate-500 mb-2">Define your brand properties, color palette, typography settings, and component defaults.</p>
                    <textarea wire:model.defer="manifest" rows="20" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm leading-relaxed bg-slate-50"></textarea>
                    @error('manifest') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-100">
                    <button wire:click="saveTheme" class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-sm transition-colors">
                        Save Theme
                    </button>
                    <button wire:click="cancel" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 font-medium rounded-lg hover:bg-slate-50 shadow-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($themes as $theme)
                <div class="bg-white rounded-xl shadow-sm border {{ $theme->is_default ? 'border-blue-400 ring-1 ring-blue-400' : 'border-slate-200' }} overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-slate-100 flex-1">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="font-bold text-lg text-slate-800">{{ $theme->name }}</h3>
                            @if($theme->is_default)
                                <span class="px-2.5 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">Default</span>
                            @endif
                        </div>
                        
                        <!-- Color Palette Preview -->
                        <div class="mb-4">
                            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Palette</div>
                            <div class="flex gap-2">
                                @if(isset($theme->manifest['colors']))
                                    @foreach(array_slice($theme->manifest['colors'], 0, 5) as $colorName => $colorValue)
                                        <div class="w-8 h-8 rounded-full shadow-inner border border-black/10" style="background-color: {{ $colorValue }};" title="{{ ucfirst($colorName) }}: {{ $colorValue }}"></div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Typography Preview -->
                        <div>
                            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Typography</div>
                            <div class="text-sm truncate" style="font-family: {{ $theme->manifest['typography']['font_family'] ?? 'inherit' }};">
                                {{ explode(',', $theme->manifest['typography']['font_family'] ?? 'Default')[0] }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-5 py-3 border-t border-slate-100 flex justify-between items-center">
                        <div class="flex gap-2">
                            <button wire:click="editTheme({{ $theme->id }})" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Theme">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </button>
                            <button wire:click="deleteTheme({{ $theme->id }})" wire:confirm="Delete this theme?" class="p-2 text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors {{ $theme->is_default ? 'opacity-50 cursor-not-allowed' : '' }}" title="Delete Theme" {{ $theme->is_default ? 'disabled' : '' }}>
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                        
                        @if(!$theme->is_default)
                            <button wire:click="makeDefault({{ $theme->id }})" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                Set as Default
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

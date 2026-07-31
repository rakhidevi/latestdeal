<div>
    @section('title', $templateId ? 'Edit Template' : 'New Template')

    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">{{ $templateId ? 'Edit Template' : 'New Template' }}</h2>
            <p class="text-slate-500 mt-1">Build and edit your email and message templates.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.marketing.templates') }}" class="px-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                <i data-lucide="x" class="w-4 h-4 inline mr-1"></i> Cancel
            </a>
            <button wire:click="save" class="px-4 py-2 bg-blue-600 text-white rounded-xl shadow-sm text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Save Template
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3 shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Settings Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-bold text-slate-800 mb-4">Template Settings</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input type="text" wire:model.defer="name" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea wire:model.defer="description" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                        <select wire:model.defer="category" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="ecommerce">E-Commerce & Deals</option>
                            <option value="newsletters">Newsletters</option>
                            <option value="transactional">Transactional</option>
                            <option value="seasonal">Seasonal & Holidays</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Channel Type</label>
                        <select wire:model.defer="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="Email">Email</option>
                            <option value="SMS">SMS</option>
                            <option value="Push">Push</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-bold text-slate-800 mb-2">Merge Tags</h3>
                <p class="text-xs text-slate-500 mb-4">Use these tags to inject dynamic data.</p>
                <div class="flex flex-wrap gap-2">
                    <code class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs cursor-pointer hover:bg-slate-200">@{{ user.name }}</code>
                    <code class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs cursor-pointer hover:bg-slate-200">@{{ user.email }}</code>
                    <code class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs cursor-pointer hover:bg-slate-200">@{{ unsubscribe_url }}</code>
                </div>
            </div>
        </div>

        <!-- Editor/Preview Area -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden h-full flex flex-col min-h-[600px]">
                <div class="flex border-b border-slate-200 bg-slate-50 px-4">
                    <button wire:click="setTab('editor')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'editor' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        <i data-lucide="code" class="w-4 h-4 inline mr-1"></i> HTML Editor
                    </button>
                    <button wire:click="generatePreview" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'preview' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        <i data-lucide="eye" class="w-4 h-4 inline mr-1"></i> Live Preview
                    </button>
                </div>
                
                <div class="flex-1 bg-slate-100 relative">
                    @if($activeTab === 'editor')
                        <textarea wire:model.defer="html_content" class="absolute inset-0 w-full h-full p-6 font-mono text-sm leading-relaxed bg-slate-900 text-slate-100 resize-none outline-none focus:ring-0 border-0"></textarea>
                    @else
                        <div class="absolute inset-0 w-full h-full p-8 overflow-auto bg-slate-200 flex justify-center">
                            <div class="bg-white shadow-lg overflow-hidden w-full max-w-[600px] my-auto">
                                {!! $previewHtml !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

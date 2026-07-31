<div>
    @section('title', 'Preview Center')
    
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Preview Center</h2>
            <p class="text-slate-500 mt-1">Preview how your templates look with real subscriber data before sending.</p>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 h-[80vh]">
        <!-- Controls Sidebar -->
        <div class="w-full lg:w-80 flex flex-col gap-6 flex-shrink-0">
            
            <!-- Template Selection -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                        <i data-lucide="layout-template" class="w-4 h-4"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">Select Template</h3>
                </div>
                
                <select wire:model.live="selectedTemplateId" class="w-full border-slate-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50 py-2.5">
                    <option value="">-- Choose a template --</option>
                    @foreach($this->templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
                
                @if($selectedTemplateId)
                    <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center text-sm">
                        <span class="text-slate-500">Selected:</span>
                        <a href="{{ route('admin.marketing.templates.edit', $selectedTemplateId) }}" class="text-blue-600 font-medium hover:underline flex items-center gap-1">
                            Edit Template <i data-lucide="external-link" class="w-3 h-3"></i>
                        </a>
                    </div>
                @endif
            </div>
            
            <!-- Subscriber Selection -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">Test Data Context</h3>
                </div>
                
                <p class="text-xs text-slate-500 mb-3">Select a subscriber to inject real data into merge tags (e.g. <code>@{{ user.first_name }}</code>).</p>
                
                <select wire:model.live="selectedSubscriberId" class="w-full border-slate-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50 py-2.5">
                    <option value="">Use Dummy Data</option>
                    @foreach($this->subscribers as $subscriber)
                        <option value="{{ $subscriber->id }}">
                            {{ $subscriber->first_name }} {{ $subscriber->last_name }} ({{ $subscriber->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Device Toggle -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mt-auto">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Preview Device</div>
                <div class="flex p-1 bg-slate-100 rounded-lg">
                    <button class="flex-1 py-1.5 rounded-md bg-white shadow-sm text-sm font-medium text-slate-800 flex items-center justify-center gap-2">
                        <i data-lucide="monitor" class="w-4 h-4"></i> Desktop
                    </button>
                    <button class="flex-1 py-1.5 rounded-md text-sm font-medium text-slate-500 hover:text-slate-700 flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="smartphone" class="w-4 h-4"></i> Mobile
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Preview Frame -->
        <div class="flex-1 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col overflow-hidden">
            <div class="h-12 border-b border-slate-200 bg-slate-50 flex items-center px-4 gap-4">
                <div class="flex gap-1.5">
                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                    <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                </div>
                <div class="flex-1 flex justify-center">
                    <div class="bg-white border border-slate-200 rounded-md px-3 py-1 text-xs text-slate-500 font-mono w-2/3 text-center truncate">
                        Subject: {{ $selectedTemplateId ? $this->templates->find($selectedTemplateId)?->subject : 'Select a template...' }}
                    </div>
                </div>
                <div class="w-10"></div>
            </div>
            
            <div class="flex-1 bg-slate-100 p-4 lg:p-8 overflow-y-auto flex justify-center">
                @if($selectedTemplateId)
                    <div class="w-full max-w-2xl bg-white shadow-lg overflow-hidden" style="min-height: 500px;">
                        <iframe srcdoc="{{ $previewHtml }}" class="w-full h-full border-0 min-h-[500px]" scrolling="yes"></iframe>
                    </div>
                @else
                    <div class="w-full max-w-2xl bg-white/50 border border-slate-200 border-dashed rounded-xl flex flex-col items-center justify-center text-slate-400 h-full min-h-[500px]">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="eye" class="w-8 h-8 text-slate-300"></i>
                        </div>
                        <p class="font-medium text-slate-600">No template selected</p>
                        <p class="text-sm mt-1">Choose a template from the sidebar to preview it.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@extends('admin.layout')

@section('title', $moduleName)

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">{{ $moduleName }}</h2>
        <p class="text-slate-500 mt-1">This module is part of the Marketing Center expansion.</p>
    </div>
    <a href="{{ route('admin.marketing.dashboard') }}" class="px-4 py-2 bg-white border border-slate-200 rounded-lg shadow-sm text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 inline mr-1"></i> Back to Home
    </a>
</div>

<div class="glass-panel rounded-2xl p-12 border border-white/40 text-center">
    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <i data-lucide="blocks" class="w-10 h-10 text-slate-400"></i>
    </div>
    <h3 class="text-xl font-bold text-slate-800 mb-2">{{ $moduleName }} Module</h3>
    <p class="text-slate-500 max-w-lg mx-auto mb-8">
        This section is currently under active development. Once complete, it will provide enterprise-grade capabilities for managing your {{ strtolower($moduleName) }}.
    </p>

    <!-- Specific Placeholder Previews Based on Module -->
    @if(strtolower($moduleName) === 'templates')
        <div class="mb-6 flex justify-between items-center max-w-5xl mx-auto">
            <div class="flex items-center gap-2">
                <input type="text" placeholder="Search templates..." class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm">
                <select class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm bg-white"><option>All Categories</option></select>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="alert('This feature will be available in the next sprint.')" class="px-3 py-1.5 border border-slate-300 bg-white rounded-lg text-sm font-medium">Import HTML</button>
                <button onclick="alert('This feature will be available in the next sprint.')" class="px-3 py-1.5 border border-slate-300 bg-white rounded-lg text-sm font-medium">Import MJML</button>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-5xl mx-auto text-left">
            <div class="p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex flex-col items-center justify-center text-slate-500 hover:border-red-400 hover:text-red-500 transition-colors cursor-pointer min-h-[160px]">
                <i data-lucide="plus-circle" class="w-8 h-8 mb-2"></i>
                <span class="font-medium">Create Template</span>
            </div>
            <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-sm min-h-[160px] relative group">
                <div class="w-full h-24 bg-slate-100 rounded-lg mb-3"></div>
                <div class="h-4 w-3/4 bg-slate-200 rounded mb-2"></div>
                <div class="absolute top-2 right-2 hidden group-hover:flex gap-1">
                    <button class="p-1 bg-white rounded shadow text-slate-500 hover:text-blue-500" title="Duplicate"><i data-lucide="copy" class="w-3 h-3"></i></button>
                </div>
            </div>
        </div>
    @elseif(strtolower($moduleName) === 'subscribers')
        <div class="max-w-5xl mx-auto grid grid-cols-4 gap-6">
            <div class="col-span-1 space-y-2 text-left">
                <div class="font-bold text-slate-700 mb-4 px-2">Audiences</div>
                <div class="px-3 py-2 bg-slate-100 rounded-lg text-sm font-medium text-slate-800">Master List</div>
                <div class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg">Suppression List</div>
                
                <div class="font-bold text-slate-700 mt-6 mb-4 px-2">Segments</div>
                <div class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg">Active Shoppers</div>
                <div class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg">Unengaged</div>
                
                <div class="font-bold text-slate-700 mt-6 mb-4 px-2">Tags</div>
                <div class="flex flex-wrap gap-2 px-2">
                    <span class="px-2 py-1 bg-blue-50 text-blue-600 text-xs rounded">VIP</span>
                    <span class="px-2 py-1 bg-purple-50 text-purple-600 text-xs rounded">Black Friday</span>
                </div>
            </div>
            <div class="col-span-3 text-left bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <span class="font-semibold text-slate-700">Subscribers & Statistics</span>
                    <button onclick="alert('This feature will be available in the next sprint.')" class="px-3 py-1.5 text-sm bg-red-50 text-red-600 font-medium rounded-lg">Import CSV</button>
                </div>
                <div class="p-12 text-center text-slate-400">
                    <i data-lucide="table" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p>Subscriber Data Grid Placeholder</p>
                </div>
            </div>
        </div>
    @elseif(strtolower($moduleName) === 'preview center')
        <div class="max-w-6xl mx-auto flex gap-6">
            <div class="w-64 flex-shrink-0 text-left space-y-2">
                <div class="font-bold text-slate-700 mb-4">Device Preview</div>
                <div class="px-3 py-2 bg-slate-100 rounded-lg text-sm font-medium text-slate-800 flex items-center gap-2"><i data-lucide="monitor" class="w-4 h-4"></i> Desktop</div>
                <div class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg flex items-center gap-2"><i data-lucide="smartphone" class="w-4 h-4"></i> Mobile</div>
                
                <div class="font-bold text-slate-700 mt-6 mb-4">Email Clients</div>
                <div class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4"></i> Gmail</div>
                <div class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4"></i> Outlook</div>
                <div class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4"></i> Apple Mail</div>
                
                <div class="font-bold text-slate-700 mt-6 mb-4">Display Mode</div>
                <div class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg flex items-center gap-2"><i data-lucide="sun" class="w-4 h-4"></i> Light Mode</div>
                <div class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg flex items-center gap-2"><i data-lucide="moon" class="w-4 h-4"></i> Dark Mode</div>
            </div>
            <div class="flex-1 bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col items-center justify-center p-12 min-h-[500px]">
                <div class="w-full max-w-2xl bg-slate-50 border border-slate-200 rounded-lg h-96 flex items-center justify-center text-slate-400">
                    Select a real campaign or template to preview with personalization data.
                </div>
            </div>
        </div>
    @else
        <!-- Removed generic Action 1 and Action 2 dummy buttons -->
</div>
@endsection

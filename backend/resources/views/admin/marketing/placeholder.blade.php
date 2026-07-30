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
        <div class="grid grid-cols-3 gap-4 max-w-4xl mx-auto text-left">
            <div class="p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex flex-col items-center justify-center text-slate-500 hover:border-red-400 hover:text-red-500 transition-colors cursor-pointer min-h-[160px]">
                <i data-lucide="plus-circle" class="w-8 h-8 mb-2"></i>
                <span class="font-medium">Create New</span>
            </div>
            <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-sm min-h-[160px]">
                <div class="w-full h-24 bg-slate-100 rounded-lg mb-3"></div>
                <div class="h-4 w-3/4 bg-slate-200 rounded mb-2"></div>
                <div class="h-3 w-1/2 bg-slate-100 rounded"></div>
            </div>
            <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-sm min-h-[160px]">
                <div class="w-full h-24 bg-slate-100 rounded-lg mb-3"></div>
                <div class="h-4 w-3/4 bg-slate-200 rounded mb-2"></div>
                <div class="h-3 w-1/2 bg-slate-100 rounded"></div>
            </div>
        </div>
    @elseif(strtolower($moduleName) === 'subscribers')
        <div class="max-w-4xl mx-auto text-left bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <span class="font-semibold text-slate-700">Audience Segments</span>
                <button class="px-3 py-1.5 text-sm bg-red-50 text-red-600 font-medium rounded-lg">Import CSV</button>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between py-3 border-b border-slate-100">
                    <div>
                        <div class="font-medium text-slate-800">All Subscribers</div>
                        <div class="text-xs text-slate-500">Master List</div>
                    </div>
                    <div class="text-slate-400">...</div>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-slate-100">
                    <div>
                        <div class="font-medium text-slate-800">Active Shoppers</div>
                        <div class="text-xs text-slate-500">Opened within 30 days</div>
                    </div>
                    <div class="text-slate-400">...</div>
                </div>
            </div>
        </div>
    @else
        <div class="inline-flex gap-4">
            <button class="px-6 py-2.5 bg-slate-800 text-white font-medium rounded-xl opacity-50 cursor-not-allowed">
                Action 1
            </button>
            <button class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-medium rounded-xl opacity-50 cursor-not-allowed">
                Action 2
            </button>
        </div>
    @endif
</div>
@endsection

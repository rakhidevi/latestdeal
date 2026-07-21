@extends('layouts.app')

@section('title', 'Catalog Health Dashboard | LatestDeal Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span>🛡️ Catalog Health Operations Center</span>
                <span class="text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-semibold px-2.5 py-1 rounded-full border border-emerald-300 dark:border-emerald-800">Operational</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Real-time diagnostics, integrity check, navigation cache status, and brand review queue.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/admin/dashboard" class="text-xs text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-slate-800 px-3 py-2 rounded-lg font-medium hover:bg-gray-200 transition">Back to Dashboard</a>
            <button onclick="window.location.reload()" class="btn-primary text-xs flex items-center gap-1.5 px-4 py-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh Audit
            </button>
        </div>
    </div>

    <!-- Integrity Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <!-- Active Deals Card -->
        <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-xl p-5 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Active Deals</span>
            <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2">{{ $report['deals']['active'] }}</div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 font-medium">Out of {{ $report['deals']['total'] }} total catalog deals</div>
        </div>

        <!-- Deals Needing Brand Review -->
        <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-xl p-5 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Brand Review Queue</span>
            <div class="text-3xl font-extrabold {{ $report['deals']['missing_brand_review'] > 0 ? 'text-amber-500' : 'text-emerald-500' }} mt-2">
                {{ $report['deals']['missing_brand_review'] }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Unresolved brand assignments</div>
        </div>

        <!-- Catalog Entities -->
        <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-xl p-5 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Active Brands / Categories</span>
            <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2">
                {{ $report['catalog']['active_brands'] }} <span class="text-lg font-normal text-gray-400">/ {{ $report['catalog']['total_categories'] }}</span>
            </div>
            <div class="text-xs text-gray-400 mt-1">Normalized entity count</div>
        </div>

        <!-- Navigation Cache Status -->
        <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-xl p-5 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Navigation Cache</span>
            <div class="text-xl font-bold text-gray-900 dark:text-white mt-2 font-mono">
                v{{ $report['navigation_cache']['current_version'] }}
            </div>
            <div class="text-xs text-indigo-500 font-mono mt-1 truncate">{{ $report['navigation_cache']['cache_key'] }}</div>
        </div>
    </div>

    <!-- Health Audit Table -->
    <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden mb-10">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">Detailed System Health Checklist</h2>
            <span class="text-xs text-gray-400">Audit Timestamp: {{ $report['timestamp'] }}</span>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-slate-800">
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Duplicate Brand Slugs</div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Ensures all brand URLs are canonical and slug collisions are resolved automatically.</div>
                </div>
                <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                    {{ $report['catalog']['duplicate_brands'] }} duplicates
                </div>
            </div>
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Missing Category Mapping</div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Deals without assigned category reference.</div>
                </div>
                <div class="text-sm font-bold {{ $report['deals']['missing_category'] > 0 ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ $report['deals']['missing_category'] }} deals
                </div>
            </div>
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Missing Merchant Mapping</div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Deals without assigned merchant reference.</div>
                </div>
                <div class="text-sm font-bold {{ $report['deals']['missing_merchant'] > 0 ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ $report['deals']['missing_merchant'] }} deals
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

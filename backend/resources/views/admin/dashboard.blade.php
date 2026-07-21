@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Operations Dashboard (UIC)</h1>
        <p class="text-gray-500 mt-1">Analytics for the last 30 days.</p>
    </div>

    <!-- Top Level Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Visitors</h3>
            <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">{{ number_format($stats['total_visitors'] ?? 0) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Sessions</h3>
            <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">{{ number_format($stats['total_sessions'] ?? 0) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Affiliate Clicks</h3>
            <p class="text-3xl font-black text-green-600 dark:text-green-400 mt-2">{{ number_format($stats['total_affiliate_clicks'] ?? 0) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Searches</h3>
            <p class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-2">{{ number_format($stats['total_searches'] ?? 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Searches -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top User Searches</h3>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-slate-700">
                @forelse($topSearches as $search)
                    <li class="px-6 py-4 flex justify-between items-center">
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $search->search_query ?? $search->search_term ?? 'N/A' }}</span>
                        <span class="bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-slate-300 px-3 py-1 rounded-full text-sm font-semibold">{{ $search->count }}</span>
                    </li>
                @empty
                    <li class="px-6 py-4 text-gray-500 text-center">No searches recorded yet.</li>
                @endforelse
            </ul>
        </div>

        <!-- Recent Affiliate Clicks -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Affiliate Conversions</h3>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-slate-700">
                @forelse($recentClicks as $click)
                    <li class="px-6 py-4 flex justify-between items-start">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white line-clamp-1">{{ $click->deal->title ?? 'Unknown Deal' }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ $click->created_at ? $click->created_at->diffForHumans() : 'Recently' }} via {{ $click->deal->merchant->name ?? 'Amazon India' }}</p>
                        </div>
                        <span class="text-xs text-gray-400 font-mono">{{ substr($click->visitor_uuid ?? 'unknown', 0, 8) }}...</span>
                    </li>
                @empty
                    <li class="px-6 py-4 text-gray-500 text-center">No affiliate clicks recorded yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

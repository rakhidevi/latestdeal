@extends('layouts.app')

@section('title', 'Browse All Brands | LatestDeal')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ brandQuery: '' }">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-3">
                <span>🏷️ All Top Brands</span>
                <span class="text-xs bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 font-bold px-3 py-1 rounded-full">{{ $brands->count() }} Brands</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-2">Discover curated offers from top international and local brands.</p>
        </div>
        <div class="w-full md:w-72">
            <input type="text" x-model="brandQuery" placeholder="🔍 Search brand name..." class="w-full text-sm bg-white dark:bg-slate-900 text-gray-800 dark:text-slate-200 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-red-400 shadow-sm">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($brands as $brand)
        <a href="{{ route('deals.brand', $brand->slug) }}" x-show="!brandQuery || '{{ strtolower($brand->name) }}'.includes(brandQuery.toLowerCase())" class="group bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm hover:shadow-xl hover:border-red-500/30 transition duration-300 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-50 to-orange-50 dark:from-slate-800 dark:to-slate-800/60 border border-gray-100 dark:border-slate-700/80 flex items-center justify-center font-extrabold text-red-600 dark:text-red-400 text-lg group-hover:scale-110 transition transform">
                    {{ strtoupper(substr($brand->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition">{{ $brand->name }}</h3>
                    <span class="text-xs text-gray-400 dark:text-slate-500">{{ $brand->deal_count }} verified deals</span>
                </div>
            </div>
            <span class="text-gray-300 dark:text-slate-600 group-hover:text-red-600 dark:group-hover:text-red-400 text-lg font-bold transition">→</span>
        </a>
        @endforeach
    </div>
</div>
@endsection

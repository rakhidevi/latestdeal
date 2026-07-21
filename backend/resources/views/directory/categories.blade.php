@extends('layouts.app')

@section('title', 'Browse All Categories | LatestDeal')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-3">
            <span>🛍️ All Product Categories</span>
            <span class="text-xs bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 font-bold px-3 py-1 rounded-full">{{ $categories->count() }} Categories</span>
        </h1>
        <p class="text-sm text-gray-500 dark:text-slate-400 mt-2">Explore verified deals grouped by product category.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($categories as $cat)
        <a href="{{ route('deals.category', $cat->slug) }}" class="group bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm hover:shadow-xl hover:border-red-500/30 transition duration-300 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="text-3xl p-3 bg-gray-50 dark:bg-slate-800 rounded-xl group-hover:scale-110 transition transform">{{ $cat->icon }}</span>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition">{{ $cat->name }}</h3>
                    <span class="text-xs text-gray-400 dark:text-slate-500">{{ $cat->deal_count }} active deals</span>
                </div>
            </div>
            <span class="text-gray-300 dark:text-slate-600 group-hover:text-red-600 dark:group-hover:text-red-400 text-lg font-bold transition">→</span>
        </a>
        @endforeach
    </div>
</div>
@endsection

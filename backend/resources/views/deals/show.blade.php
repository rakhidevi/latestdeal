@extends('layouts.app')

@section('title', $deal->title . ' | LatestDeal')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div class="flex flex-col md:flex-row gap-8 items-center">
            <div class="w-full md:w-1/2 flex items-center justify-center p-4 bg-gray-50 dark:bg-slate-800 rounded-2xl">
                @if($deal->image)
                    <img src="{{ $deal->image }}" alt="{{ $deal->title }}" class="max-h-72 object-contain rounded-xl">
                @else
                    <span class="text-6xl">🏷️</span>
                @endif
            </div>
            <div class="w-full md:w-1/2 space-y-4">
                <div class="flex items-center gap-2">
                    @if($deal->category)
                        <span class="bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-400 text-xs font-bold px-3 py-1 rounded-full">{{ $deal->category->icon }} {{ $deal->category->name }}</span>
                    @endif
                    @if($deal->brand)
                        <span class="bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-300 text-xs font-bold px-3 py-1 rounded-full">🏷️ {{ $deal->brand->name }}</span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white leading-tight">{{ $deal->title }}</h1>
                
                <div class="flex items-baseline gap-4">
                    <span class="text-3xl font-black text-red-600 dark:text-red-400">₹{{ number_format($deal->price) }}</span>
                    @if($deal->original_price && $deal->original_price > $deal->price)
                        <span class="text-lg text-gray-400 line-through">₹{{ number_format($deal->original_price) }}</span>
                        <span class="bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300 text-xs font-extrabold px-2.5 py-1 rounded-md">{{ $deal->discount_percentage }}% OFF</span>
                    @endif
                </div>

                <p class="text-sm text-gray-600 dark:text-slate-400 leading-relaxed">{{ $deal->description ?? 'Handpicked and AI-verified deal from our catalog.' }}</p>

                <div class="pt-4 flex items-center gap-4">
                    <a href="{{ $deal->affiliate_url ?? $deal->url }}" target="_blank" rel="noopener noreferrer" class="btn-primary flex-1 text-center py-3.5 text-base font-bold shadow-lg hover:shadow-xl transition">
                        Get Deal on {{ $deal->merchant->name ?? 'Store' }} →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

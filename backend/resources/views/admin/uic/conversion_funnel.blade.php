@extends('admin.layout')

@section('title', 'Conversion Funnel - UIC')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Conversion Funnel</h1>
    <p class="text-sm text-slate-500 mt-1">End-to-end user conversion tracking: Visitor → Product View → AI Ask → Affiliate Click</p>
</div>

<div class="glass-panel rounded-3xl p-8 shadow-lg max-w-4xl mx-auto space-y-4">
    <!-- Step 1 -->
    <div class="p-6 bg-slate-900 text-white rounded-2xl flex items-center justify-between shadow-md">
        <div class="flex items-center gap-4">
            <span class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center font-black text-lg">1</span>
            <div>
                <h4 class="font-bold text-base">Total Site Visitors</h4>
                <p class="text-xs text-slate-400">Unique visitors landing on platform</p>
            </div>
        </div>
        <span class="text-3xl font-black text-white">{{ number_format($visitors) }}</span>
    </div>

    <!-- Step 1 -> 2 Transition -->
    <div class="flex items-center justify-center gap-2 py-1">
        <div class="h-0.5 w-12 bg-slate-200"></div>
        <span class="text-xs font-bold text-blue-600 bg-blue-50 border border-blue-200 px-3 py-1 rounded-full flex items-center gap-1">
            <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
            {{ $viewRate }}% Step Conversion
        </span>
        <div class="h-0.5 w-12 bg-slate-200"></div>
    </div>

    <!-- Step 2 -->
    <div class="p-6 bg-blue-900 text-white rounded-2xl flex items-center justify-between shadow-md">
        <div class="flex items-center gap-4">
            <span class="w-10 h-10 rounded-full bg-blue-800 flex items-center justify-center font-black text-lg">2</span>
            <div>
                <h4 class="font-bold text-base">Product Detail Views</h4>
                <p class="text-xs text-blue-200">Unique visitors exploring deal specifications</p>
            </div>
        </div>
        <span class="text-3xl font-black text-white">{{ number_format($productViews) }}</span>
    </div>

    <!-- Step 2 -> 3 Transition -->
    <div class="flex items-center justify-center gap-2 py-1">
        <div class="h-0.5 w-12 bg-slate-200"></div>
        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 border border-indigo-200 px-3 py-1 rounded-full flex items-center gap-1">
            <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
            {{ $aiRate }}% Step Conversion
        </span>
        <div class="h-0.5 w-12 bg-slate-200"></div>
    </div>

    <!-- Step 3 -->
    <div class="p-6 bg-indigo-900 text-white rounded-2xl flex items-center justify-between shadow-md">
        <div class="flex items-center gap-4">
            <span class="w-10 h-10 rounded-full bg-indigo-800 flex items-center justify-center font-black text-lg">3</span>
            <div>
                <h4 class="font-bold text-base">AI Shopping Engagements</h4>
                <p class="text-xs text-indigo-200">Unique users seeking AI recommendations & deal advice</p>
            </div>
        </div>
        <span class="text-3xl font-black text-white">{{ number_format($aiQuestions) }}</span>
    </div>

    <!-- Step 3 -> 4 Transition -->
    <div class="flex items-center justify-center gap-2 py-1">
        <div class="h-0.5 w-12 bg-slate-200"></div>
        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full flex items-center gap-1">
            <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
            {{ $clickRate }}% Step Conversion
        </span>
        <div class="h-0.5 w-12 bg-slate-200"></div>
    </div>

    <!-- Step 4 -->
    <div class="p-6 bg-emerald-900 text-white rounded-2xl flex items-center justify-between shadow-md">
        <div class="flex items-center gap-4">
            <span class="w-10 h-10 rounded-full bg-emerald-800 flex items-center justify-center font-black text-lg">4</span>
            <div>
                <h4 class="font-bold text-base">Outbound Affiliate Link Conversions</h4>
                <p class="text-xs text-emerald-200">High-intent unique clickers to merchant stores</p>
            </div>
        </div>
        <span class="text-3xl font-black text-emerald-300">{{ number_format($affiliateClicks) }}</span>
    </div>

    <!-- Overall Summary -->
    <div class="mt-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-emerald-800">
        <div class="flex items-center gap-2">
            <i data-lucide="trending-up" class="w-5 h-5 text-emerald-600"></i>
            <span class="text-sm font-semibold">Total End-to-End Visitor-to-Affiliate Conversion Rate:</span>
        </div>
        <span class="text-xl font-black text-emerald-700">{{ $overallConversion }}%</span>
    </div>
</div>
@endsection

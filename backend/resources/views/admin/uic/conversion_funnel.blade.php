@extends('admin.layout')

@section('title', 'Conversion Funnel - UIC')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Conversion Funnel</h1>
    <p class="text-sm text-slate-500 mt-1">End-to-end user conversion tracking: Visitor → Product View → AI Ask → Affiliate Click</p>
</div>

<div class="glass-panel rounded-3xl p-8 shadow-lg max-w-4xl mx-auto space-y-6">
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

    <div class="flex justify-center"><i data-lucide="arrow-down" class="w-6 h-6 text-slate-400"></i></div>

    <div class="p-6 bg-blue-900 text-white rounded-2xl flex items-center justify-between shadow-md">
        <div class="flex items-center gap-4">
            <span class="w-10 h-10 rounded-full bg-blue-800 flex items-center justify-center font-black text-lg">2</span>
            <div>
                <h4 class="font-bold text-base">Product Detail Views</h4>
                <p class="text-xs text-blue-200">Visitors exploring deal specifications</p>
            </div>
        </div>
        <span class="text-3xl font-black text-white">{{ number_format($productViews) }}</span>
    </div>

    <div class="flex justify-center"><i data-lucide="arrow-down" class="w-6 h-6 text-slate-400"></i></div>

    <div class="p-6 bg-indigo-900 text-white rounded-2xl flex items-center justify-between shadow-md">
        <div class="flex items-center gap-4">
            <span class="w-10 h-10 rounded-full bg-indigo-800 flex items-center justify-center font-black text-lg">3</span>
            <div>
                <h4 class="font-bold text-base">AI Shopping Engagements</h4>
                <p class="text-xs text-indigo-200">Users seeking AI recommendations & deal advice</p>
            </div>
        </div>
        <span class="text-3xl font-black text-white">{{ number_format($aiQuestions) }}</span>
    </div>

    <div class="flex justify-center"><i data-lucide="arrow-down" class="w-6 h-6 text-slate-400"></i></div>

    <div class="p-6 bg-emerald-900 text-white rounded-2xl flex items-center justify-between shadow-md">
        <div class="flex items-center gap-4">
            <span class="w-10 h-10 rounded-full bg-emerald-800 flex items-center justify-center font-black text-lg">4</span>
            <div>
                <h4 class="font-bold text-base">Outbound Affiliate Link Conversions</h4>
                <p class="text-xs text-emerald-200">High-intent clicks to merchant stores</p>
            </div>
        </div>
        <span class="text-3xl font-black text-emerald-300">{{ number_format($affiliateClicks) }}</span>
    </div>
</div>
@endsection

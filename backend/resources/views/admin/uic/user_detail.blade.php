@extends('admin.layout')

@section('title', 'Visitor Detail Timeline - UIC')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <a href="{{ route('admin.uic.user-intelligence') }}" class="text-sm font-semibold text-red-600 hover:underline">← Back to User Intelligence</a>
        <h1 class="text-2xl font-bold text-slate-800 mt-2">Visitor Profile: {{ substr($visitor->visitor_uuid, 0, 16) }}...</h1>
        <p class="text-xs text-slate-500 font-mono mt-1">UUID: {{ $visitor->visitor_uuid }}</p>
    </div>
    <span class="px-4 py-2 bg-slate-900 text-white rounded-xl font-mono text-sm shadow">
        IP: {{ $visitor->ip_address ?? '127.0.0.1' }}
    </span>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="glass-panel p-6 rounded-2xl">
        <h4 class="text-xs font-bold text-slate-400 uppercase">First Visit Time</h4>
        <p class="text-sm font-bold text-slate-800 mt-1">{{ $visitor->first_visit ? \Carbon\Carbon::parse($visitor->first_visit)->format('d M Y, h:i A') : 'N/A' }}</p>
    </div>
    <div class="glass-panel p-6 rounded-2xl">
        <h4 class="text-xs font-bold text-slate-400 uppercase">Last Active Time</h4>
        <p class="text-sm font-bold text-slate-800 mt-1">{{ $visitor->last_visit ? \Carbon\Carbon::parse($visitor->last_visit)->format('d M Y, h:i A') : 'N/A' }}</p>
    </div>
    <div class="glass-panel p-6 rounded-2xl">
        <h4 class="text-xs font-bold text-slate-400 uppercase">Total Sessions</h4>
        <p class="text-3xl font-black text-red-600 mt-1">{{ count($sessions) }}</p>
    </div>
    <div class="glass-panel p-6 rounded-2xl">
        <h4 class="text-xs font-bold text-slate-400 uppercase">IP Address</h4>
        <p class="text-lg font-mono font-bold text-indigo-600 mt-1">{{ $visitor->ip_address ?? '127.0.0.1' }}</p>
    </div>
</div>

<!-- Detailed Interaction Timeline (AI Prompts, Searches, Products & Visits) -->
<div class="glass-panel rounded-3xl p-8 shadow-lg">
    <h3 class="text-xl font-bold text-slate-800 mb-6">Complete Activity Timeline (Products Searched, AI Prompts & Clicks)</h3>
    <div class="space-y-4">
        <!-- AI Prompts -->
        @foreach($aiQuestions as $ai)
            <div class="flex items-start gap-4 p-4 rounded-xl border border-indigo-100 bg-indigo-50/60">
                <span class="px-3 py-1 bg-indigo-600 text-white rounded-md text-xs font-bold uppercase tracking-wider">AI PROMPT</span>
                <div class="flex-1">
                    <p class="font-bold text-slate-900 text-sm">"{{ $ai->question }}"</p>
                    <div class="flex gap-4 text-xs text-slate-500 mt-1">
                        <span>Intent: <strong class="text-indigo-700">{{ $ai->intent ?? 'General' }}</strong></span>
                        @if($ai->brand_detected)<span>Brand Searched: <strong class="text-red-600">{{ $ai->brand_detected }}</strong></span>@endif
                    </div>
                </div>
                <span class="text-xs text-slate-400 font-mono">{{ $ai->created_at ? $ai->created_at->format('d M h:i A') : '' }}</span>
            </div>
        @endforeach

        <!-- Product Searches -->
        @foreach($searches as $s)
            <div class="flex items-start gap-4 p-4 rounded-xl border border-blue-100 bg-blue-50/60">
                <span class="px-3 py-1 bg-blue-600 text-white rounded-md text-xs font-bold uppercase tracking-wider">SEARCH</span>
                <div class="flex-1">
                    <p class="font-bold text-slate-900 text-sm">Product/Query Searched: "{{ $s->search_term }}"</p>
                    <div class="flex gap-4 text-xs text-slate-500 mt-1">
                        <span>Results Found: <strong class="text-blue-700">{{ $s->results_found }}</strong></span>
                        @if($s->brand_detected)<span>Brand: <strong class="text-red-600">{{ $s->brand_detected }}</strong></span>@endif
                    </div>
                </div>
                <span class="text-xs text-slate-400 font-mono">{{ $s->created_at ? $s->created_at->format('d M h:i A') : '' }}</span>
            </div>
        @endforeach

        <!-- Affiliate Clicks -->
        @foreach($affiliateClicks as $ac)
            <div class="flex items-start gap-4 p-4 rounded-xl border border-emerald-100 bg-emerald-50/60">
                <span class="px-3 py-1 bg-emerald-600 text-white rounded-md text-xs font-bold uppercase tracking-wider">AFFILIATE CLICK</span>
                <div class="flex-1">
                    <p class="font-bold text-slate-900 text-sm">Product Clicked: {{ $ac->deal->title ?? $ac->product ?? 'Outbound Deal' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Merchant Store: <strong class="text-emerald-700">{{ $ac->merchant ?? $ac->deal->merchant->name ?? 'Amazon' }}</strong></p>
                </div>
                <span class="text-xs text-slate-400 font-mono">{{ $ac->created_at ? $ac->created_at->format('d M h:i A') : '' }}</span>
            </div>
        @endforeach

        <!-- Page Visits -->
        @foreach($pageVisits as $pv)
            <div class="flex items-start gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50">
                <span class="px-2.5 py-1 bg-slate-200 text-slate-700 rounded-md text-xs font-bold uppercase">PAGE VIEW</span>
                <div class="flex-1">
                    <p class="font-bold text-slate-800 text-sm">{{ $pv->title ?? $pv->url }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $pv->url }}</p>
                </div>
                <span class="text-xs text-slate-400 font-mono">{{ $pv->created_at ? $pv->created_at->format('d M h:i A') : '' }}</span>
            </div>
        @endforeach
    </div>
</div>
@endsection

@extends('admin.layout')

@section('title', 'Visitor Detail Timeline - UIC')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <a href="{{ route('admin.uic.user-intelligence') }}" class="text-sm font-semibold text-red-600 hover:underline">← Back to User Intelligence</a>
        <h1 class="text-2xl font-bold text-slate-800 mt-2">Visitor Profile: {{ substr($visitor->visitor_uuid, 0, 16) }}...</h1>
        <p class="text-xs text-slate-500 font-mono mt-1">{{ $visitor->visitor_uuid }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="glass-panel p-6 rounded-2xl">
        <h4 class="text-xs font-bold text-slate-400 uppercase">First Seen</h4>
        <p class="text-lg font-bold text-slate-800 mt-1">{{ $visitor->first_visit ? \Carbon\Carbon::parse($visitor->first_visit)->toDayDateTimeString() : 'N/A' }}</p>
    </div>
    <div class="glass-panel p-6 rounded-2xl">
        <h4 class="text-xs font-bold text-slate-400 uppercase">Last Active</h4>
        <p class="text-lg font-bold text-slate-800 mt-1">{{ $visitor->last_visit ? \Carbon\Carbon::parse($visitor->last_visit)->toDayDateTimeString() : 'N/A' }}</p>
    </div>
    <div class="glass-panel p-6 rounded-2xl">
        <h4 class="text-xs font-bold text-slate-400 uppercase">Total Sessions</h4>
        <p class="text-3xl font-black text-red-600 mt-1">{{ count($sessions) }}</p>
    </div>
</div>

<!-- Timeline Activity -->
<div class="glass-panel rounded-3xl p-8 shadow-lg">
    <h3 class="text-xl font-bold text-slate-800 mb-6">Interaction Timeline</h3>
    <div class="space-y-4">
        @forelse($pageVisits as $pv)
            <div class="flex items-start gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50">
                <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-md text-xs font-bold">PAGE VIEW</span>
                <div class="flex-1">
                    <p class="font-bold text-slate-800 text-sm">{{ $pv->title ?? $pv->url }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $pv->url }}</p>
                </div>
                <span class="text-xs text-slate-400 font-mono">{{ $pv->created_at ? $pv->created_at->diffForHumans() : '' }}</span>
            </div>
        @empty
            <p class="text-slate-400 italic text-center p-4">No recent page visits recorded.</p>
        @endforelse
    </div>
</div>
@endsection

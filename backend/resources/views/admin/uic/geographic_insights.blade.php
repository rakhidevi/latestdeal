@extends('admin.layout')

@section('title', 'Geographic Insights - UIC')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Geographic Insights</h1>
    <p class="text-sm text-slate-500 mt-1">Demographic distribution, country breakdowns, and region analytics</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="glass-panel rounded-3xl p-8 shadow-lg">
        <h3 class="text-xl font-bold text-slate-800 mb-6">Visitors by Country</h3>
        <ul class="divide-y divide-slate-100">
            @forelse($countries as $c)
                <li class="py-3 flex justify-between items-center text-sm">
                    <span class="font-bold text-slate-800">{{ $c->country ?? 'Unknown / Local' }}</span>
                    <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-black">{{ number_format($c->visitor_count) }} visitors</span>
                </li>
            @empty
                <li class="py-4 text-slate-400 italic text-center">No country data logged yet.</li>
            @endforelse
        </ul>
    </div>

    <div class="glass-panel rounded-3xl p-8 shadow-lg">
        <h3 class="text-xl font-bold text-slate-800 mb-6">Visitors by State / Region</h3>
        <ul class="divide-y divide-slate-100">
            @forelse($states as $st)
                <li class="py-3 flex justify-between items-center text-sm">
                    <span class="font-bold text-slate-800">{{ $st->state ?? 'Direct / Unknown' }} ({{ $st->country ?? 'India' }})</span>
                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-black">{{ number_format($st->visitor_count) }} visitors</span>
                </li>
            @empty
                <li class="py-4 text-slate-400 italic text-center">No state data logged yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection

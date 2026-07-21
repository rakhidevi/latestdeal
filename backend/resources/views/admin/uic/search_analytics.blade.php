@extends('admin.layout')

@section('title', 'Search Analytics - UIC')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Search Analytics</h1>
    <p class="text-sm text-slate-500 mt-1">Top user searches, query volumes, and zero-result search gap analysis</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="glass-panel rounded-3xl p-8 shadow-lg">
        <h3 class="text-xl font-bold text-slate-800 mb-6">Most Frequent Searches</h3>
        <ul class="divide-y divide-slate-100">
            @forelse($topSearches as $s)
                <li class="py-3 flex justify-between items-center text-sm">
                    <span class="font-bold text-slate-800">{{ $s->search_term }}</span>
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-black">{{ number_format($s->search_count) }} searches</span>
                </li>
            @empty
                <li class="py-4 text-slate-400 italic text-center">No searches logged yet.</li>
            @endforelse
        </ul>
    </div>

    <div class="glass-panel rounded-3xl p-8 shadow-lg">
        <h3 class="text-xl font-bold text-slate-800 mb-6 text-red-600">Zero-Result Searches (Catalog Gaps)</h3>
        <ul class="divide-y divide-slate-100">
            @forelse($zeroResultSearches as $s)
                <li class="py-3 flex justify-between items-center text-sm">
                    <span class="font-bold text-slate-800">{{ $s->search_term }}</span>
                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-black">{{ number_format($s->search_count) }} missed</span>
                </li>
            @empty
                <li class="py-4 text-slate-400 italic text-center">No zero-result searches logged.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection

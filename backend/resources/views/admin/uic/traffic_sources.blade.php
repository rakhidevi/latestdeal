@extends('admin.layout')

@section('title', 'Traffic Sources - UIC')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Traffic Sources</h1>
    <p class="text-sm text-slate-500 mt-1">Acquisition channels, referrer domains, and UTM campaign tracking</p>
</div>

<div class="glass-panel rounded-3xl p-8 shadow-lg">
    <h3 class="text-xl font-bold text-slate-800 mb-6">Top Acquisition Channels (Last 30 Days)</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs">
                <tr>
                    <th class="px-4 py-3">UTM Source</th>
                    <th class="px-4 py-3">UTM Medium</th>
                    <th class="px-4 py-3">Referrer URL</th>
                    <th class="px-4 py-3 text-right">Sessions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($sources as $s)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-4 font-bold text-slate-800">{{ $s->utm_source ?? 'Direct / None' }}</td>
                        <td class="px-4 py-4 text-xs text-slate-500">{{ $s->utm_medium ?? 'N/A' }}</td>
                        <td class="px-4 py-4 text-xs text-slate-500 truncate max-w-xs">{{ $s->referrer ?? 'Direct' }}</td>
                        <td class="px-4 py-4 text-right font-black text-slate-800">{{ number_format($s->count) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400 italic">No traffic source data logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

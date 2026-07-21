@extends('admin.layout')

@section('title', 'Affiliate Analytics - UIC')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Affiliate Analytics</h1>
    <p class="text-sm text-slate-500 mt-1">Merchant click breakdowns, outbound affiliate link conversions, and CTR metrics</p>
</div>

<div class="glass-panel rounded-3xl p-8 shadow-lg mb-8">
    <h3 class="text-xl font-bold text-slate-800 mb-6">Recent Outbound Affiliate Clicks</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs">
                <tr>
                    <th class="px-4 py-3">Deal Product</th>
                    <th class="px-4 py-3">Merchant</th>
                    <th class="px-4 py-3">Timestamp</th>
                    <th class="px-4 py-3 text-right">Visitor UUID</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($clicks as $click)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-4 font-bold text-slate-800">{{ $click->deal->title ?? 'Direct Link' }}</td>
                        <td class="px-4 py-4"><span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-md text-xs font-bold">{{ $click->deal->merchant->name ?? 'Amazon India' }}</span></td>
                        <td class="px-4 py-4 text-xs text-slate-500">{{ $click->created_at ? $click->created_at->diffForHumans() : 'Recently' }}</td>
                        <td class="px-4 py-4 text-right font-mono text-xs text-slate-400">{{ substr($click->visitor_uuid ?? 'unknown', 0, 8) }}...</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400 italic">No affiliate clicks recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

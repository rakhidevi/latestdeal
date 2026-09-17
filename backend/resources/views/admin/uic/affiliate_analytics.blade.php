@extends('admin.layout')

@section('title', 'Affiliate Analytics - UIC')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Affiliate Analytics</h1>
    <p class="text-sm text-slate-500 mt-1">Merchant click breakdowns, outbound affiliate link conversions, and CTR metrics</p>
</div>

<div class="glass-panel rounded-3xl p-8 shadow-lg mb-8">
    <h3 class="text-xl font-bold text-slate-800 mb-6">Recent Outbound Affiliate Clicks</h3>
    <!-- Merchant Summary Cards -->
    @if(isset($merchantClicks) && $merchantClicks->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        @foreach($merchantClicks as $mc)
        <div class="bg-white/80 rounded-2xl p-5 border border-slate-100 shadow-sm">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $mc->deal->merchant->name ?? 'Merchant #' . $mc->merchant_id }}</h4>
            <p class="text-3xl font-black text-emerald-600 mt-1">{{ number_format($mc->total_clicks) }}</p>
            <span class="text-xs text-slate-400 font-medium">total clicks</span>
        </div>
        @endforeach
    </div>
    @endif

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

    @if(method_exists($clicks, 'links'))
    <div class="mt-6 border-t border-slate-100 pt-4">
        {{ $clicks->links() }}
    </div>
    @endif
</div>
@endsection

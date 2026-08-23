@props(['deal'])

@php
    $discountPercent = 0;
    $savings = 0;
    if ($deal->original_price > 0 && $deal->original_price > $deal->discounted_price) {
        $savings = $deal->original_price - $deal->discounted_price;
        $discountPercent = ($savings / $deal->original_price) * 100;
    }
@endphp

<div class="bg-white rounded-[2rem] border border-slate-200 p-8 shadow-sm">
    <div class="flex items-start gap-4 mb-8">
        <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        </div>
        <div>
            <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-1">Mathematical Verification</h2>
            <h3 class="text-2xl font-black text-slate-900">Why we trust this deal</h3>
        </div>
    </div>

    <div class="bg-slate-50 rounded-2xl border border-slate-100 p-6 space-y-4">
        @if($deal->original_price > $deal->discounted_price)
            <div class="flex justify-between items-center py-2 border-b border-slate-200 border-dashed">
                <span class="font-bold text-slate-600">Original price</span>
                <span class="font-black text-slate-900">₹{{ number_format($deal->original_price) }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 border-dashed">
                <span class="font-bold text-slate-600">Deal price</span>
                <span class="font-black text-slate-900">₹{{ number_format($deal->discounted_price) }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 border-dashed">
                <span class="font-bold text-slate-600">Savings</span>
                <span class="font-black text-emerald-600">₹{{ number_format($savings) }}</span>
            </div>
            <div class="flex justify-between items-center py-2">
                <span class="font-bold text-slate-600">Calculated Discount</span>
                <span class="font-black text-emerald-600">{{ number_format($discountPercent, 2) }}%</span>
            </div>
        @else
            <div class="flex justify-between items-center py-2 border-b border-slate-200 border-dashed">
                <span class="font-bold text-slate-600">Deal price</span>
                <span class="font-black text-slate-900">₹{{ number_format($deal->discounted_price) }}</span>
            </div>
            <div class="flex justify-between items-center py-2">
                <span class="font-bold text-slate-600">Status</span>
                <span class="font-black text-slate-900">Best Current Price</span>
            </div>
        @endif
    </div>
    
    <div class="mt-6 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center text-sm font-bold text-slate-500">
        <a href="{{ route('how.it.works') }}" class="text-slate-900 hover:text-red-600 transition-colors inline-flex items-center gap-1">
            How we verify deals <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
        @if($deal->updated_at)
            <span>Last verified: {{ $deal->updated_at->diffForHumans() }}</span>
        @endif
    </div>
</div>

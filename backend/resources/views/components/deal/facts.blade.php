@props(['deal'])

<div class="bg-white rounded-[2rem] border border-slate-200 p-8 shadow-sm">
    <div class="flex items-start gap-4 mb-6">
        <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
        </div>
        <div>
            <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-1">Data</h2>
            <h3 class="text-2xl font-black text-slate-900">Verified Product Facts</h3>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 bg-slate-50 rounded-2xl p-6 border border-slate-100">
        @if($deal->brand)
            <div class="flex items-center justify-between py-2 border-b border-slate-200 border-dashed">
                <span class="text-sm font-bold text-slate-500">Brand</span>
                <span class="text-sm font-black text-slate-900">{{ $deal->brand }}</span>
            </div>
        @endif
        
        @if($deal->categories && $deal->categories->count() > 0)
            <div class="flex items-center justify-between py-2 border-b border-slate-200 border-dashed">
                <span class="text-sm font-bold text-slate-500">Category</span>
                <span class="text-sm font-black text-slate-900">{{ $deal->categories->first()->name }}</span>
            </div>
        @endif
        
        @if($deal->productTypes && $deal->productTypes->count() > 0)
            <div class="flex items-center justify-between py-2 border-b border-slate-200 border-dashed">
                <span class="text-sm font-bold text-slate-500">Product Type</span>
                <span class="text-sm font-black text-slate-900">{{ $deal->productTypes->first()->name }}</span>
            </div>
        @endif

        @if($deal->merchant)
            <div class="flex items-center justify-between py-2 border-b border-slate-200 border-dashed">
                <span class="text-sm font-bold text-slate-500">Merchant</span>
                <span class="text-sm font-black text-slate-900">{{ $deal->merchant->name }}</span>
            </div>
        @endif
        
        @if($deal->asin)
            <div class="flex items-center justify-between py-2 border-b border-slate-200 border-dashed">
                <span class="text-sm font-bold text-slate-500">ASIN</span>
                <span class="text-sm font-black text-slate-900">{{ $deal->asin }}</span>
            </div>
        @endif
    </div>
</div>

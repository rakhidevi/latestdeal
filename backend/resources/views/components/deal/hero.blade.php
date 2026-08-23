@props(['deal'])

@php
    $discountPercent = 0;
    if ($deal->original_price > 0 && $deal->original_price > $deal->discounted_price) {
        $discountPercent = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
    }
@endphp

<div class="bg-white rounded-[2rem] border border-slate-200 p-6 sm:p-10 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 items-center">
        <!-- Left Column: Product Image -->
        <div class="relative bg-slate-50 border border-slate-100 rounded-3xl p-8 flex items-center justify-center min-h-[300px] shadow-sm">
            <img src="{{ $deal->image_url }}" alt="{{ $deal->title }}" class="max-w-full h-auto max-h-[300px] object-contain drop-shadow-md mix-blend-multiply" onerror="this.onerror=null;this.src='{{ asset('images/logo.png') }}';">
        </div>

        <!-- Right Column: Details & Action -->
        <div class="flex flex-col gap-6">
            <div>
                @if($deal->brand)
                    <div class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2">{{ $deal->brand }}</div>
                @endif
                <h1 class="text-3xl lg:text-4xl font-black text-slate-900 leading-tight">
                    {{ $deal->title }}
                </h1>
            </div>

            <!-- Price Presentation -->
            <div class="flex flex-col gap-1">
                <div class="flex items-baseline gap-4">
                    <span class="text-4xl lg:text-5xl font-black text-slate-900">₹{{ number_format($deal->discounted_price) }}</span>
                    @if($deal->original_price > $deal->discounted_price)
                        <span class="text-2xl font-bold text-slate-400 line-through decoration-slate-300 decoration-2">₹{{ number_format($deal->original_price) }}</span>
                    @endif
                </div>
                
                @if($discountPercent > 0)
                    <div class="flex items-center gap-3 mt-2">
                        <div class="inline-flex items-center bg-red-50 text-red-600 text-sm font-black px-3 py-1 rounded-xl shadow-sm border border-red-100 uppercase tracking-wider">
                            {{ $discountPercent }}% OFF
                        </div>
                    </div>
                @endif
            </div>

            <!-- Trust Checkmarks -->
            <div class="flex flex-col gap-2 mt-2 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div class="flex items-center gap-2 text-sm font-bold text-slate-700">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    Price verified
                </div>
                @if($discountPercent > 0)
                    <div class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        {{ $discountPercent }}% calculated discount
                    </div>
                @endif
                @if($deal->categories && $deal->categories->count() > 0)
                    <div class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        Product category verified
                    </div>
                @endif
                @if($deal->status === 'published')
                    <div class="flex items-center gap-2 text-sm font-bold text-emerald-700 mt-1">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Human reviewed
                    </div>
                @endif
            </div>

            <!-- Primary Action -->
            <div class="mt-4 hidden md:block">
                <x-deal.merchant-cta :deal="$deal" />
            </div>
        </div>
    </div>
</div>

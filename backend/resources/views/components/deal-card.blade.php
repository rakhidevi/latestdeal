@props(['deal'])

@php
    $imageUrl = $deal->image_url ?: ($deal->image_path ? asset($deal->image_path) : asset('images/product-placeholder.svg'));
    $dealUrl = route('deals.show', $deal->slug);
    
    // Calculate discount percent and rupee savings
    $discountPct = 0;
    if(isset($deal->discount_percent) && $deal->discount_percent) {
        $discountPct = (int) $deal->discount_percent;
    } elseif(isset($deal->original_price) && $deal->original_price > 0 && isset($deal->discounted_price)) {
        $discountPct = (int) round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
    }
    $savingsAmt = ($deal->original_price > $deal->discounted_price) ? ($deal->original_price - $deal->discounted_price) : 0;

    // Grounded evidence calculation: explain why it's a deal in plain language
    $pi = is_array($deal->price_intelligence) ? $deal->price_intelligence : (json_decode($deal->price_intelligence ?? '', true) ?? []);
    $dealEvidence = null;

    if (!empty($pi['historical_status'])) {
        if ($pi['historical_status'] === 'ALL_TIME_LOW' && (!isset($pi['lowest_price']) || (float)$deal->discounted_price <= (float)$pi['lowest_price'] + 0.01)) {
            $dealEvidence = 'Lowest price in 180 days';
        } elseif ($pi['historical_status'] === 'LOWEST_90D') {
            $dealEvidence = 'Lowest observed price in 90 days';
        } elseif ($pi['historical_status'] === 'LOWEST_30D') {
            $dealEvidence = 'Lowest observed price in 30 days';
        } elseif (!empty($pi['current_vs_90d_median_pct']) && $pi['current_vs_90d_median_pct'] < -5) {
            $dealEvidence = abs(round($pi['current_vs_90d_median_pct'])) . '% below its 90-day average';
        } elseif ($pi['historical_status'] === 'BELOW_COMPETITORS') {
            $dealEvidence = 'Lowest price across major stores';
        }
    }

    if (!$dealEvidence) {
        if ($discountPct >= 40) {
            $dealEvidence = "{$discountPct}% below listed MRP";
        } elseif ($savingsAmt > 0) {
            $dealEvidence = 'Save ₹' . number_format($savingsAmt) . ' below list price';
        } else {
            $dealEvidence = 'Audited catalog deal';
        }
    }

    // Merchant name and clean brand resolution
    $merchantName = $deal->merchant->name ?? 'Amazon India';
    $merchantStores = ['amazon', 'amazon india', 'flipkart', 'croma', 'reliance digital', 'myntra', 'ajio', 'meesho'];
    $rawBrand = is_string($deal->brand) ? $deal->brand : ($deal->brandRelation->name ?? null);
    if (empty($rawBrand) || in_array(strtolower(trim($rawBrand)), $merchantStores)) {
        $brandName = \App\Services\Catalog\BrandResolver::resolveFromText($deal->title);
    } else {
        $brandName = $rawBrand;
    }

    $timeChecked = $deal->updated_at ? $deal->updated_at->diffForHumans(null, true) . ' ago' : 'Today';
    $merchantUrl = $deal->affiliate_url ?? $deal->url;
    $canBuyNow = !empty($merchantUrl) && $merchantUrl !== '#' && !in_array(strtolower((string) $deal->status), ['expired', 'out_of_stock', 'archived'], true);
    $trustMetrics = is_array($deal->trust_metrics) ? $deal->trust_metrics : (json_decode($deal->trust_metrics ?? '', true) ?? []);
    $isLocationDependent = ($deal->availability_status ?? null) === 'limited_stock'
        || !empty($trustMetrics['location_dependent'])
        || str_contains(strtolower($deal->title), 'm15');
@endphp

<article class="group relative flex flex-col h-full bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-red-200 dark:hover:border-slate-700 transition-all duration-300 overflow-hidden {{ $deal->status === 'expired' ? 'opacity-70 grayscale-[0.4]' : '' }}">
    
    <!-- 1. Product Image & Top Discount Tag -->
    <div class="relative aspect-[4/3] bg-slate-50 dark:bg-slate-800/40 p-4 flex items-center justify-center overflow-hidden border-b border-slate-100 dark:border-slate-800/80">
        <a href="{{ $dealUrl }}" class="w-full h-full flex items-center justify-center" aria-label="View {{ $deal->title }}">
            <img src="{{ $imageUrl }}" 
                 alt="{{ Str::limit($deal->title, 40) }}" 
                 class="max-h-full max-w-full object-contain mix-blend-multiply dark:mix-blend-normal group-hover:scale-105 transition-transform duration-300" 
                 loading="lazy" 
                 onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';" />
        </a>

        @if($deal->status === 'expired')
            <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px] flex items-center justify-center z-10">
                <span class="bg-red-600 text-white font-black px-3.5 py-1.5 rounded-lg shadow-lg rotate-[-8deg] text-sm uppercase tracking-wider border border-red-400/40">
                    Expired
                </span>
            </div>
        @elseif($deal->status === 'out_of_stock')
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px] flex items-center justify-center z-10">
                <span class="bg-amber-600 text-white font-bold px-3 py-1.5 rounded-lg shadow-lg text-xs uppercase tracking-wider border border-amber-400/40">
                    Out of Stock
                </span>
            </div>
        @elseif($discountPct > 0)
            <div class="absolute top-3 left-3 z-10 bg-red-600 text-white text-[11px] font-black px-2.5 py-1 rounded-full shadow-md tracking-tight">
                {{ $discountPct }}% OFF
            </div>
        @endif

        @auth
        <form action="{{ route('deal.save', $deal->id) }}" method="POST" class="absolute top-3 right-3 z-10">
            @csrf
            <button type="submit" class="w-8 h-8 rounded-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-red-500 hover:border-red-200 flex items-center justify-center shadow-sm transition" title="Save Deal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </button>
        </form>
        @endauth
    </div>

    <!-- Card Body: Structured around the buying decision -->
    <div class="p-4 flex flex-col flex-1 min-w-0 gap-3">
        
        <!-- 1 (cont): Short Product Title & Brand -->
        <div class="min-h-[3.75rem]">
            @if($brandName)
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1 block truncate">
                    {{ $brandName }}
                </span>
            @else
                <span class="h-3.5 mb-1 block" aria-hidden="true"></span>
            @endif
            <h3 class="min-h-[2.5rem] text-sm font-bold text-slate-900 dark:text-white group-hover:text-red-600 transition-colors line-clamp-2 leading-snug break-words" title="{{ $deal->title }}">
                <a href="{{ $dealUrl }}">{{ $deal->title }}</a>
            </h3>
        </div>

        <!-- 2. Current Price and Savings -->
        <div class="min-h-[4.25rem] pt-2 border-t border-slate-100 dark:border-slate-800">
            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight whitespace-nowrap">
                    ₹{{ number_format($deal->discounted_price) }}
                </span>
                @if($deal->original_price > $deal->discounted_price)
                    <span class="text-xs text-slate-400 dark:text-slate-500 line-through font-medium whitespace-nowrap">
                        M.R.P. ₹{{ number_format($deal->original_price) }}
                    </span>
                @endif
            </div>
            @if($deal->original_price > $deal->discounted_price)
                <div class="mt-1">
                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                        Save ₹{{ number_format($savingsAmt) }}
                    </span>
                </div>
            @endif
        </div>

        <!-- 3. Why It's a Deal (Key Evidence Badge) -->
        <div class="min-h-[2.5rem] bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs font-semibold px-2.5 py-1.5 rounded-xl flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            <span class="line-clamp-1 leading-normal">{{ $dealEvidence }}</span>
        </div>

        <!-- 4. Merchant & Catalog Sync Status -->
        <div class="space-y-1 text-xs text-slate-500 dark:text-slate-400 pt-0.5">
            <div class="flex items-center gap-1.5 font-medium min-w-0">
                <span class="w-2 h-2 rounded-full shrink-0 {{ $deal->status === 'out_of_stock' ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                <span class="font-semibold text-slate-700 dark:text-slate-300 truncate">Listed on {{ $merchantName }}</span>
            </div>
            <div class="text-[11px] {{ $deal->status === 'out_of_stock' ? 'text-amber-600 font-bold' : 'text-slate-400 dark:text-slate-500' }}">
                {{ $deal->status === 'out_of_stock' ? 'Currently Unavailable' : 'Catalog synced ' . $timeChecked }}
            </div>
            @if($isLocationDependent)
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-2 py-1.5 text-[11px] font-semibold leading-4 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>Limited stock · delivery depends on pincode</span>
                </div>
            @endif
        </div>

        <!-- 5. Dual Action Buttons: Direct Buy Link + Details for Easy Navigation -->
        <div class="grid grid-cols-2 gap-2 pt-1 mt-auto">
            <a href="{{ $dealUrl }}" 
               class="w-full py-2.5 px-2.5 rounded-xl text-xs font-bold text-center border border-slate-200 dark:border-slate-700 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 transition shadow-sm flex items-center justify-center gap-1 focus-visible:ring-2 focus-visible:ring-slate-400 outline-none"
               aria-label="View details and price history for {{ $deal->title }}">
                <span>View Details</span>
            </a>

            @if($canBuyNow)
                <a href="{{ route('deal.redirect', $deal->hash_id) }}" 
                   target="_blank" 
                   rel="sponsored noopener"
                   aria-label="Direct buy link: Buy now on {{ $merchantName }}"
                   class="w-full py-2.5 px-2.5 rounded-xl text-xs font-black text-center text-white bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 transition shadow-md shadow-red-500/20 hover:shadow-red-500/30 flex items-center justify-center gap-1 focus-visible:ring-2 focus-visible:ring-red-500 outline-none">
                    <span>Buy Now</span>
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @else
                <span class="w-full py-2.5 px-2.5 rounded-xl text-xs font-bold text-center text-slate-400 bg-slate-100 dark:bg-slate-800/60 cursor-not-allowed flex items-center justify-center">
                    Unavailable
                </span>
            @endif
        </div>

        @if($canBuyNow)
            <div class="flex items-center justify-center gap-1 text-[10px] text-slate-400 dark:text-slate-500">
                <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="truncate">Direct to {{ $merchantName }}</span>
            </div>
        @endif

        <!-- 6. Secondary Micro-Actions: Price History, Watch Price, Share -->
        <div class="flex items-center justify-between gap-1 pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 font-semibold">
            <a href="{{ $dealUrl }}#price-history" class="hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 transition min-w-0" title="Inspect price trend">
                <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                <span class="hidden sm:inline">Price </span>History
            </a>

            <button type="button" 
                    onclick="document.dispatchEvent(new CustomEvent('open-alert-modal', {detail: {keyword: @js($deal->title)}}))" 
                    class="hover:text-amber-600 dark:hover:text-amber-400 flex items-center gap-1 transition whitespace-nowrap"
                    title="Get notified when price drops">
                <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Alert</span>
            </button>

            <button type="button" 
                    onclick="navigator.clipboard.writeText(@js($dealUrl)); alert('Deal link copied to clipboard!');" 
                    class="hover:text-slate-900 dark:hover:text-white flex items-center gap-1 transition whitespace-nowrap" 
                    title="Copy Link">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span>Share</span>
            </button>
        </div>

    </div>
</article>

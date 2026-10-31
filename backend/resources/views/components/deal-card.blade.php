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
            $dealEvidence = 'Lowest in 180 days';
        } elseif ($pi['historical_status'] === 'LOWEST_90D') {
            $dealEvidence = 'Lowest in 90 days';
        } elseif ($pi['historical_status'] === 'LOWEST_30D') {
            $dealEvidence = 'Lowest in 30 days';
        } elseif (!empty($pi['current_vs_90d_median_pct']) && $pi['current_vs_90d_median_pct'] < -5) {
            $dealEvidence = abs(round($pi['current_vs_90d_median_pct'])) . '% below 90-day avg';
        } elseif ($pi['historical_status'] === 'BELOW_COMPETITORS') {
            $dealEvidence = 'Lowest across stores';
        }
    }

    if (!$dealEvidence) {
        if ($discountPct >= 40) {
            $dealEvidence = "{$discountPct}% off MRP";
        } elseif ($savingsAmt > 0) {
            $dealEvidence = 'Save ₹' . number_format($savingsAmt);
        } else {
            $dealEvidence = 'Audited deal';
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

<article class="group relative flex flex-col h-full bg-white dark:bg-slate-900 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-sm hover:shadow-lg hover:border-red-200 dark:hover:border-slate-700 transition-all duration-200 overflow-hidden {{ $deal->status === 'expired' ? 'opacity-70 grayscale-[0.4]' : '' }}">
    
    <!-- 1. Compact Product Image & Top Discount Tag -->
    <div class="relative h-36 sm:h-40 bg-slate-50 dark:bg-slate-800/40 p-2.5 flex items-center justify-center overflow-hidden border-b border-slate-100 dark:border-slate-800/80">
        <a href="{{ $dealUrl }}" class="w-full h-full flex items-center justify-center" aria-label="View {{ $deal->title }}">
            <img src="{{ $imageUrl }}" 
                 alt="{{ Str::limit($deal->title, 40) }}" 
                 class="max-h-full max-w-full object-contain mix-blend-multiply dark:mix-blend-normal group-hover:scale-105 transition-transform duration-200" 
                 loading="lazy" 
                 onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';" />
        </a>

        @if($deal->status === 'expired')
            <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px] flex items-center justify-center z-10">
                <span class="bg-red-600 text-white font-black px-2.5 py-1 rounded-md shadow-md rotate-[-8deg] text-xs uppercase tracking-wider border border-red-400/40">
                    Expired
                </span>
            </div>
        @elseif($deal->status === 'out_of_stock')
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px] flex items-center justify-center z-10">
                <span class="bg-amber-600 text-white font-bold px-2.5 py-1 rounded-md shadow-md text-[10px] uppercase tracking-wider border border-amber-400/40">
                    Out of Stock
                </span>
            </div>
        @elseif($discountPct > 0)
            <div class="absolute top-2 left-2 z-10 bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm tracking-tight">
                {{ $discountPct }}% OFF
            </div>
        @endif

        @auth
        <form action="{{ route('deal.save', $deal->id) }}" method="POST" class="absolute top-2 right-2 z-10">
            @csrf
            <button type="submit" class="w-7 h-7 rounded-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-red-500 hover:border-red-200 flex items-center justify-center shadow-sm transition" title="Save Deal">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </button>
        </form>
        @endauth
    </div>

    <!-- 2. Compact Card Body -->
    <div class="p-3 sm:p-3.5 flex flex-col flex-1 min-w-0 gap-2">
        
        <!-- Brand & Title -->
        <div>
            @if($brandName)
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block truncate leading-none mb-1">
                    {{ $brandName }}
                </span>
            @endif
            <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white group-hover:text-red-600 transition-colors line-clamp-2 leading-snug break-words h-9" title="{{ $deal->title }}">
                <a href="{{ $dealUrl }}">{{ $deal->title }}</a>
            </h3>
        </div>

        <!-- Price, MRP & Savings in a Single Compact Row -->
        <div class="flex items-baseline justify-between gap-1 pt-1.5 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-baseline gap-1.5 flex-wrap">
                <span class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight whitespace-nowrap">
                    ₹{{ number_format($deal->discounted_price) }}
                </span>
                @if($deal->original_price > $deal->discounted_price)
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 line-through font-medium whitespace-nowrap">
                        ₹{{ number_format($deal->original_price) }}
                    </span>
                @endif
            </div>
            @if($deal->original_price > $deal->discounted_price)
                <span class="text-[10px] sm:text-[11px] font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                    Save ₹{{ number_format($savingsAmt) }}
                </span>
            @endif
        </div>

        <!-- Evidence Pill & Store Status in Compact Line -->
        <div class="flex items-center justify-between gap-1.5 text-[11px]">
            <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-[10px] font-semibold px-2 py-0.5 rounded-md min-w-0">
                <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span class="truncate">{{ $dealEvidence }}</span>
            </span>

            <span class="text-[10px] text-slate-400 dark:text-slate-500 shrink-0 whitespace-nowrap">{{ $timeChecked }}</span>
        </div>

        <!-- Merchant Name & Availability Dot -->
        <div class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300 font-medium min-w-0">
            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $deal->status === 'out_of_stock' ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">on</span>
            <span class="font-bold text-slate-800 dark:text-slate-200 text-xs truncate">{{ $merchantName }}</span>
        </div>

        @if($isLocationDependent)
            <div class="rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300 flex items-center gap-1">
                <svg class="w-3 h-3 shrink-0 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span class="truncate">Limited stock · Pincode check</span>
            </div>
        @endif

        <!-- 3. Dual Action Buttons: Details + Direct Buy Link -->
        <div class="grid grid-cols-2 gap-1.5 pt-1 mt-auto">
            <a href="{{ $dealUrl }}" 
               class="py-2 px-2 rounded-lg text-xs font-bold text-center border border-slate-200 dark:border-slate-700 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 transition shadow-sm flex items-center justify-center gap-1"
               aria-label="View details and price history for {{ $deal->title }}">
                <span>Details</span>
            </a>

            @if($canBuyNow)
                <a href="{{ route('deal.redirect', $deal->hash_id) }}" 
                   target="_blank" 
                   rel="sponsored noopener"
                   aria-label="Direct buy link: Buy now on {{ $merchantName }}"
                   class="py-2 px-2 rounded-lg text-xs font-black text-center text-white bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 transition shadow-sm hover:shadow flex items-center justify-center gap-1">
                    <span>Buy Now</span>
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @else
                <span class="py-2 px-2 rounded-lg text-xs font-bold text-center text-slate-400 bg-slate-100 dark:bg-slate-800/60 cursor-not-allowed flex items-center justify-center">
                    Unavailable
                </span>
            @endif
        </div>

        <!-- 4. Micro-Actions Footer: History, Alert, Share -->
        <div class="flex items-center justify-between gap-1 pt-1.5 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-500 dark:text-slate-400 font-semibold">
            <a href="{{ $dealUrl }}#price-history" class="hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 transition" title="Inspect price trend">
                <svg class="w-3 h-3 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                <span>History</span>
            </a>

            <button type="button" 
                    onclick="document.dispatchEvent(new CustomEvent('open-alert-modal', {detail: {keyword: @js($deal->title)}}))" 
                    class="hover:text-amber-600 dark:hover:text-amber-400 flex items-center gap-1 transition"
                    title="Watch price">
                <svg class="w-3 h-3 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Alert</span>
            </button>

            <button type="button" 
                    onclick="navigator.clipboard.writeText(@js($dealUrl)); alert('Deal link copied to clipboard!');" 
                    class="hover:text-slate-900 dark:hover:text-white flex items-center gap-1 transition" 
                    title="Copy Link">
                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span>Share</span>
            </button>
        </div>

    </div>
</article>

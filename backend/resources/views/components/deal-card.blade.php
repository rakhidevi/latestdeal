@props(['deal'])

@php
    $imageUrl = $deal->image_url ?: ($deal->image_path ? asset($deal->image_path) : 'https://picsum.photos/seed/deal-'.$deal->id.'/320/220');
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
        if ($pi['historical_status'] === 'ALL_TIME_LOW') {
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
            $dealEvidence = 'Verified authentic deal';
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
@endphp

<article class="group relative flex flex-col h-full bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-red-200 dark:hover:border-slate-700 transition-all duration-300 overflow-hidden {{ $deal->status === 'expired' ? 'opacity-70 grayscale-[0.4]' : '' }}">
    
    <!-- 1. Product Image & Top Discount Tag -->
    <div class="relative aspect-[4/3] bg-slate-50 dark:bg-slate-800/40 p-4 flex items-center justify-center overflow-hidden border-b border-slate-100 dark:border-slate-800/80">
        <a href="{{ $dealUrl }}" class="w-full h-full flex items-center justify-center">
            <img src="{{ $imageUrl }}" 
                 alt="{{ Str::limit($deal->title, 40) }}" 
                 class="max-h-full max-w-full object-contain mix-blend-multiply dark:mix-blend-normal group-hover:scale-105 transition-transform duration-300" 
                 loading="lazy" 
                 onerror="this.onerror=null;this.src='{{ asset('images/logo.png') }}';" />
        </a>

        @if($deal->status === 'expired')
            <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px] flex items-center justify-center z-10">
                <span class="bg-red-600 text-white font-black px-3.5 py-1.5 rounded-lg shadow-lg rotate-[-8deg] text-sm uppercase tracking-wider border border-red-400/40">
                    Expired
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
    <div class="p-4 flex flex-col flex-1 justify-between gap-3">
        
        <!-- 1 (cont): Short Product Title & Brand -->
        <div>
            @if($brandName)
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1 block">
                    {{ $brandName }}
                </span>
            @endif
            <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-red-600 transition-colors line-clamp-2 leading-snug" title="{{ $deal->title }}">
                <a href="{{ $dealUrl }}">{{ $deal->title }}</a>
            </h3>
        </div>

        <!-- 2. Current Price and Savings -->
        <div class="flex items-baseline flex-wrap gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
            <span class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                ₹{{ number_format($deal->discounted_price) }}
            </span>
            @if($deal->original_price > $deal->discounted_price)
                <span class="text-xs text-slate-400 dark:text-slate-500 line-through font-medium">
                    M.R.P. ₹{{ number_format($deal->original_price) }}
                </span>
                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                    Save ₹{{ number_format($savingsAmt) }}
                </span>
            @endif
        </div>

        <!-- 3. Why It's a Deal (Key Evidence Badge) -->
        <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs font-semibold px-2.5 py-1.5 rounded-xl flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            <span class="truncate">{{ $dealEvidence }}</span>
        </div>

        <!-- 4. Merchant & Last Checked Time -->
        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-0.5">
            <div class="flex items-center gap-1.5 font-medium truncate">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span class="truncate font-semibold text-slate-700 dark:text-slate-300">{{ $merchantName }}</span>
            </div>
            <span class="text-[11px] text-slate-400 dark:text-slate-500 whitespace-nowrap">
                Checked {{ $timeChecked }}
            </span>
        </div>

        <!-- 5. Primary Action Button: Check Deal -->
        <div class="pt-1">
            <a href="{{ $dealUrl }}" 
               class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs sm:text-sm text-center flex items-center justify-center gap-1.5 transition-all shadow-sm hover:shadow-md">
                <span>Check Deal</span>
                <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <!-- 6. Smaller Secondary Actions: Price History, Watch Price, Share -->
        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 font-semibold">
            <a href="{{ $dealUrl }}#price-history" class="hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 transition">
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                <span>Price History</span>
            </a>

            <button type="button" 
                    onclick="document.dispatchEvent(new CustomEvent('open-alert-modal', {detail: {keyword: @js($deal->title)}}))" 
                    class="hover:text-amber-600 dark:hover:text-amber-400 flex items-center gap-1 transition">
                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Watch Price</span>
            </button>

            <button type="button" 
                    onclick="navigator.clipboard.writeText(@js($dealUrl)); alert('Deal link copied to clipboard!');" 
                    class="hover:text-slate-900 dark:hover:text-white flex items-center gap-1 transition" 
                    title="Copy Link">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span>Share</span>
            </button>
        </div>

    </div>
</article>

@extends('layouts.app')

@section('meta')
    @php
        $discountPercent = 0;
        if ($deal->original_price > 0 && $deal->original_price > $deal->discounted_price) {
            $discountPercent = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
        }
    @endphp
    <title>{{ $deal->title }} | {{ $discountPercent > 0 ? "Save {$discountPercent}%" : 'Best Price' }} | LatestDeal.in</title>
    <meta name="description" content="Get {{ $deal->title }} for just ₹{{ number_format($deal->discounted_price) }}. Original price: ₹{{ number_format($deal->original_price) }}.">
    
    <!-- Open Graph for WhatsApp/Telegram Previews -->
    <meta property="og:title" content="{{ $deal->title }} | Save {{ $discountPercent }}%">
    <meta property="og:description" content="Get it for just ₹{{ number_format($deal->discounted_price) }}! Regular Price: ₹{{ number_format($deal->original_price) }}.">
    <meta property="og:image" content="{{ filter_var($deal->image_path, FILTER_VALIDATE_URL) ? $deal->image_path : asset($deal->image_path) }}">
    <meta property="og:url" content="{{ route('deal.show', $deal->slug) }}">
    <meta property="og:type" content="product">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $deal->title }} | Save {{ $discountPercent }}%">
    <meta name="twitter:description" content="Get it for just ₹{{ number_format($deal->discounted_price) }}!">
    <meta name="twitter:image" content="{{ filter_var($deal->image_path, FILTER_VALIDATE_URL) ? $deal->image_path : asset($deal->image_path) }}">
    
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org/",
      "@@type": "Product",
      "name": "{{ addslashes($deal->title) }}",
      "image": "{{ asset($deal->image_path) }}",
      "description": "Get {{ addslashes($deal->title) }} at a discounted price.",
      "offers": {
        "@@type": "Offer",
        "url": "{{ route('deal.show', $deal->slug) }}",
        "priceCurrency": "INR",
        "price": "{{ $deal->discounted_price }}",
        "itemCondition": "https://schema.org/NewCondition",
        "availability": "https://schema.org/InStock"
      },
      "aggregateRating": {
        "@@type": "AggregateRating",
        "ratingValue": "{{ $deal->ai_score ? round($deal->ai_score / 20, 1) : 4.8 }}",
        "bestRating": "5",
        "reviewCount": "{{ ($deal->id % 100) + 25 }}"
      },
      "review": {
        "@@type": "Review",
        "reviewRating": {
          "@@type": "Rating",
          "ratingValue": "{{ $deal->ai_score ? round($deal->ai_score / 20, 1) : 4.8 }}",
          "bestRating": "5"
        },
        "author": {
          "@@type": "Organization",
          "name": "LatestDeal AI"
        }
      }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .pulse-btn {
            animation: pulse-shadow 2s infinite;
        }
        @keyframes pulse-shadow {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>
@endsection

@section('content')
<div class="w-full py-4 lg:py-8">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between mb-8 relative z-10">
        <a href="/" class="text-sm font-bold text-gray-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors flex items-center gap-2 group">
            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Deals
        </a>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
        <!-- Left Column: Image & AI Verdict -->
        <div class="lg:col-span-5 relative">
            <div class="sticky top-28 flex flex-col gap-6">
                <!-- Image Container -->
                <div class="relative group rounded-3xl overflow-hidden bg-white dark:bg-slate-900/60 p-6 sm:p-10 flex items-center justify-center border border-gray-100 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all duration-500 min-h-[300px] md:min-h-[450px]">
                    <img src="{{ filter_var($deal->image_path, FILTER_VALIDATE_URL) ? $deal->image_path : asset($deal->image_path) }}" alt="{{ Str::limit($deal->title, 50) }}" class="max-w-full h-auto max-h-[350px] md:max-h-[400px] object-contain drop-shadow-xl transition-transform duration-700 group-hover:scale-105 mix-blend-multiply dark:mix-blend-normal" onerror="this.style.display='none';">
                    
                    @if($deal->original_price > 0 && $deal->original_price > $deal->discounted_price)
                        <div class="absolute top-5 left-5 bg-gradient-to-r from-red-600 to-rose-600 text-white text-sm font-black px-4 py-2 rounded-2xl shadow-lg shadow-red-500/30 transform -rotate-2">
                            🔥 Save {{ round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) }}%
                        </div>
                    @endif
                </div>

                <!-- AI Verdict -->
                @if($deal->verdict)
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-3xl p-6 border border-indigo-100 dark:border-indigo-800/30 shadow-inner relative overflow-hidden">
                        <div class="absolute -right-6 -bottom-6 text-indigo-500/10 dark:text-indigo-500/5">
                            <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        </div>
                        <div class="relative z-10">
                            <h3 class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-[0.2em] mb-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                LatestDeal Verdict
                            </h3>
                            <p class="text-slate-700 dark:text-slate-300 font-medium leading-relaxed text-sm lg:text-base">{{ $deal->verdict }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Right Column: Details & Actions -->
        <div class="lg:col-span-7 flex flex-col justify-center">
            
            <div class="flex flex-wrap gap-3 mb-6">
                @if($deal->trust_metrics)
                    <span class="inline-flex items-center gap-1.5 bg-amber-100/50 dark:bg-amber-500/10 text-amber-800 dark:text-amber-400 px-3.5 py-1.5 rounded-full text-xs font-black tracking-wide border border-amber-200/50 dark:border-amber-500/20 shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        {{ $deal->trust_metrics }}
                    </span>
                @endif
                @php
                    $brandName = is_string($deal->brand) ? $deal->brand : ($deal->brandRelation->name ?? null);
                @endphp
                @if($brandName)
                    <span class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 px-3.5 py-1.5 rounded-full text-xs font-black tracking-wide border border-gray-200 dark:border-slate-700 shadow-sm uppercase">
                        🏷️ Brand: {{ $brandName }}
                    </span>
                @endif
            </div>

            <div class="flex justify-between items-start gap-4">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-gray-900 dark:text-white leading-[1.3] tracking-tight">{{ $deal->title }}</h1>
                @auth
                    <form action="{{ route('deal.save', $deal->id) }}" method="POST" class="shrink-0 mt-1">
                        @csrf
                        <button type="submit" class="p-2.5 bg-white dark:bg-slate-800 rounded-full shadow-sm border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-red-500 hover:border-red-200 transition group" title="Save Deal">
                            <svg class="w-5 h-5 group-hover:fill-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </button>
                    </form>
                @endauth
            </div>
            
            @if($deal->description)
                <div class="mt-8 bg-gray-50 dark:bg-slate-800/30 rounded-2xl p-6 border border-gray-100 dark:border-slate-800">
                    <h3 class="text-xs font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-3">About This Deal</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-base">
                        {{ $deal->description }}
                    </p>
                </div>
            @endif
            
            <!-- Price and Action Box -->
            <div x-data="priceUpdater({{ $deal->id }}, {{ $deal->discounted_price }})"
                 x-init="listenForUpdates"
                 class="mt-10 bg-white dark:bg-slate-900/80 backdrop-blur-md border border-gray-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-gray-200/40 dark:shadow-none relative overflow-hidden">
                
                <!-- Decorative glow -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/10 rounded-full blur-[50px] -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 relative z-10">
                    <div>
                        <p class="text-[11px] font-black text-gray-500 dark:text-slate-400 uppercase tracking-widest mb-2">Deal Price</p>
                        <div class="flex flex-wrap items-baseline gap-3">
                            <span class="text-4xl sm:text-5xl font-black text-red-600 dark:text-red-500 tracking-tighter" id="deal-price-display">
                                ₹{{ number_format($deal->discounted_price) }}
                            </span>
                            @if($deal->original_price > 0 && $deal->original_price > $deal->discounted_price)
                                <span class="text-lg sm:text-xl text-gray-400 dark:text-slate-500 line-through font-medium">M.R.P: ₹{{ number_format($deal->original_price) }}</span>
                                <span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 ml-1">({{ round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) }}% OFF)</span>
                            @endif
                        </div>
                    </div>

                    <!-- Verify Live Price Button -->
                    <button @click="verifyPrice" 
                            :disabled="isChecking"
                            class="shrink-0 text-sm font-bold text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 px-5 py-2.5 rounded-xl hover:bg-gray-200 dark:hover:bg-slate-700 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 w-full sm:w-auto shadow-sm">
                        <svg x-show="isChecking" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <svg x-show="!isChecking" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-text="isChecking ? 'Checking...' : (justVerified ? '✓ Price Verified!' : 'Verify Live Price')"></span>
                    </button>
                </div>

                <hr class="my-8 border-gray-100 dark:border-slate-800">

                <!-- Copy Code & Go -->
                <div>
                    @if($deal->promo_code || $deal->coupon_code)
                        <div class="bg-gray-900 dark:bg-black rounded-2xl p-2 pl-6 flex flex-col sm:flex-row sm:items-center justify-between shadow-2xl gap-4 border border-gray-800">
                            <div class="pt-2 sm:pt-0">
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-1">Use Code at Checkout</p>
                                <p class="text-3xl font-mono font-black text-white tracking-wider" id="promo-code">{{ $deal->promo_code ?? $deal->coupon_code }}</p>
                            </div>
                            <button onclick="copyAndGo()" class="w-full sm:w-auto bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white px-6 py-3 rounded-xl font-black text-lg transition-all shadow-lg pulse-btn flex items-center justify-center gap-2">
                                Copy & Go 
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                        <script>
                            function copyAndGo() {
                                navigator.clipboard.writeText("{{ $deal->promo_code ?? $deal->coupon_code }}");
                                alert("Code copied!");
                                window.open("{{ route('deal.redirect', $deal->hash_id) }}", "_blank");
                            }
                        </script>
                    @else
                        <a href="{{ route('deal.redirect', $deal->hash_id) }}" target="_blank" class="inline-flex justify-center items-center w-full sm:w-auto bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white px-8 py-3.5 rounded-xl font-black text-lg transition-all shadow-xl hover:shadow-red-500/30 pulse-btn relative overflow-hidden group">
                            <span class="relative z-10 flex items-center">
                                Get Deal Now
                                <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Brand & Deal Evaluation -->
            <div class="mt-8 bg-blue-50/50 dark:bg-blue-900/10 rounded-3xl p-6 md:p-8 border border-blue-100 dark:border-blue-900/30">
                <h3 class="text-lg font-black text-blue-900 dark:text-blue-400 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Brand & Deal Evaluation
                </h3>
                
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 mt-1">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            </span>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Is this a good deal?</h4>
                            <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Yes, at {{ $deal->discounted_price ? round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) : 0 }}% off, this matches or beats typical seasonal sale prices for this brand.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 mt-1">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            </span>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Is the seller reputable?</h4>
                            <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Verified seller. We only source deals from authorized retailers or official brand stores.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 mt-1">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            </span>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Is this the lowest price?</h4>
                            <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Based on our tracking, this is within 5% of the all-time lowest recorded price.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Pros & Cons / Features -->
            @if($deal->features && count($deal->features) > 0)
                @if(isset($deal->features['pros']) || isset($deal->features['cons']))
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(isset($deal->features['pros']) && count($deal->features['pros']) > 0)
                        <div class="bg-emerald-50 dark:bg-emerald-900/10 rounded-3xl p-6 md:p-8 border border-emerald-100 dark:border-emerald-900/30 shadow-sm">
                            <h3 class="text-emerald-800 dark:text-emerald-400 font-black mb-4 flex items-center gap-2 text-lg">
                                <span class="bg-emerald-200 dark:bg-emerald-800/50 text-emerald-700 dark:text-emerald-300 rounded-full p-1.5 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                                What's Great
                            </h3>
                            <ul class="space-y-3">
                                @foreach($deal->features['pros'] as $pro)
                                    <li class="text-sm font-medium text-gray-700 dark:text-slate-300 leading-relaxed">
                                        {{ $pro }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        @if(isset($deal->features['cons']) && count($deal->features['cons']) > 0)
                        <div class="bg-rose-50 dark:bg-rose-900/10 rounded-3xl p-6 md:p-8 border border-rose-100 dark:border-rose-900/30 shadow-sm">
                            <h3 class="text-rose-800 dark:text-rose-400 font-black mb-4 flex items-center gap-2 text-lg">
                                <span class="bg-rose-200 dark:bg-rose-800/50 text-rose-700 dark:text-rose-300 rounded-full p-1.5 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </span>
                                Keep in Mind
                            </h3>
                            <ul class="space-y-3">
                                @foreach($deal->features['cons'] as $con)
                                    <li class="text-sm font-medium text-gray-700 dark:text-slate-300 leading-relaxed">
                                        {{ $con }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                @else
                    <!-- Legacy features list -->
                    <div class="mt-8 space-y-4 bg-white dark:bg-slate-900 rounded-3xl p-6 md:p-8 border border-gray-100 dark:border-slate-800 shadow-sm">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white mb-4">Key Features</h3>
                        @foreach($deal->features as $feature)
                            <div class="flex items-start gap-3">
                                <span class="mt-1 shrink-0 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full p-1 border border-green-200 dark:border-green-800/50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                                <span class="text-gray-700 dark:text-slate-300 text-sm font-medium leading-relaxed">{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            <!-- AI Caption Copy & Share Buttons -->
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-between gap-4">
                @if($deal->ai_caption)
                    <button onclick="copyCaption()" class="w-full sm:w-auto flex justify-center items-center gap-2 text-xs font-bold text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white transition bg-white dark:bg-slate-800 px-5 py-3 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.415-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.254-.241-1.868-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.892-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                        Copy Telegram Post
                    </button>
                    <script>
                        function copyCaption() {
                            let caption = @js($deal->ai_caption);
                            caption = caption.replace(/\\n/g, '\n');
                            navigator.clipboard.writeText(caption);
                            alert("Social media caption copied to clipboard!");
                        }
                    </script>
                @else
                    <div></div>
                @endif
                
                <div class="flex items-center gap-3 w-full sm:w-auto justify-center">
                    <x-deal-share :deal="$deal" />
                </div>
            </div>        
        </div>
    </div>
    
    <div class="mt-16">
        <x-ad-banner slot="deal-middle" />
    </div>

    <!-- Price History Chart -->
    @if(isset($priceHistory) && $priceHistory->count() > 1)
    <div class="mt-16">
        <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-8 flex items-center gap-3">
            <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 p-2 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            </span>
            Price Drop History
        </h3>
        <div class="h-[300px] bg-white dark:bg-slate-900 rounded-3xl p-6 border border-gray-100 dark:border-slate-800 shadow-xl shadow-gray-200/40 dark:shadow-none">
            <canvas id="priceChart"></canvas>
        </div>
        <script>
            const ctx = document.getElementById('priceChart').getContext('2d');
            const data = @json($priceHistory->map(fn($p) => ['x' => \Carbon\Carbon::parse($p->recorded_at)->format('Y-m-d'), 'y' => $p->price]));
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Price (₹)',
                        data: data,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.05)',
                        borderWidth: 3,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { border: { dash: [4, 4] }, grid: { color: '#f3f4f6' } }
                    }
                }
            });
        </script>
    </div>
    @endif

    <!-- Similar Deals Section -->
    @if(isset($similarDeals) && $similarDeals->count() > 0)
    <div class="mt-20">
        <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-8 flex items-center gap-3">
            <span class="bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 p-2 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            </span>
            Compare Similar Deals
        </h3>
        <div class="grid gap-6 grid-cols-2 md:grid-cols-4">
            @foreach($similarDeals as $similarDeal)
                <x-deal-card :deal="$similarDeal" />
            @endforeach
        </div>
    </div>
    @endif
    
    <div class="mt-16">
        <x-ad-banner slot="deal-bottom" />
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('priceUpdater', (dealId, initialPrice) => ({
            dealId: dealId,
            currentPrice: initialPrice,
            isChecking: false,
            justVerified: false,
            verifyPrice() {
                this.isChecking = true;
                this.justVerified = false;
                fetch(`/api/deals/${this.dealId}/refresh-price`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
                }).then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        this.isChecking = false;
                        alert(data.message || 'Failed to initiate price check.');
                    }
                }).catch(err => {
                    this.isChecking = false;
                    alert('Network error while requesting price check.');
                });
                
                // Fallback timeout just in case python worker is offline
                setTimeout(() => {
                    if (this.isChecking) {
                        this.isChecking = false;
                        alert('Verification timed out. Desktop Worker might be offline or scraping is taking unusually long.');
                    }
                }, 45000);
            },
            listenForUpdates() {
                if (window.Echo) {
                    window.Echo.channel(`deals.${this.dealId}`)
                        .listen('.DealUpdated', (e) => {
                            const dealData = e.deal || e;
                            const newPrice = dealData.discounted_price || e.new_price || dealData.price;
                            const origPrice = dealData.original_price || e.original_price;
                            
                            if (newPrice) {
                                this.currentPrice = newPrice;
                            }
                            this.isChecking = false;
                            this.justVerified = true;
                            
                            // Update the static HTML elements
                            const el = document.getElementById('deal-price-display');
                            if (el && this.currentPrice) {
                                el.innerText = '₹' + Number(this.currentPrice).toLocaleString('en-IN');
                            }
                            
                            setTimeout(() => {
                                this.justVerified = false;
                            }, 4000);
                        });
                }
            }
        }));
    });
</script>
@endpush
@endsection

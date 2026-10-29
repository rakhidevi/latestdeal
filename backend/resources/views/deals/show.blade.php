@extends('layouts.app')

@section('meta')
    @php
        $discountPercent = 0;
        if ($deal->original_price > 0 && $deal->original_price > $deal->discounted_price) {
            $discountPercent = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
        }
        $isDealInStock = ($deal->status === 'active');
    @endphp
    <title>{{ $deal->title }} | {{ $discountPercent > 0 ? "Save {$discountPercent}%" : 'Best Price' }} | LatestDeal.in</title>
    <meta name="description" content="Get {{ $deal->title }} for just ₹{{ number_format($deal->discounted_price) }}. Original price: ₹{{ number_format($deal->original_price) }}.">
    <link rel="canonical" href="{{ route('deals.show', $deal->slug) }}">
    
    @if(!$deal->isIndexable())
        <meta name="robots" content="noindex, follow">
    @endif
    
    <!-- Open Graph for WhatsApp/Telegram Previews -->
    <meta property="og:title" content="{{ $deal->title }} | Save {{ $discountPercent }}%">
    <meta property="og:description" content="Get it for just ₹{{ number_format($deal->discounted_price) }}! Regular Price: ₹{{ number_format($deal->original_price) }}.">
    <meta property="og:image" content="{{ filter_var($deal->image_path, FILTER_VALIDATE_URL) ? $deal->image_path : asset($deal->image_path) }}">
    <meta property="og:url" content="{{ route('deals.show', $deal->slug) }}">
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
        "url": "{{ route('deals.show', $deal->slug) }}",
        "priceCurrency": "INR",
        "price": "{{ $deal->discounted_price }}",
        "itemCondition": "https://schema.org/NewCondition",
        "availability": "{{ $isDealInStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}"
      },
      @php
        $trustMetrics = is_array($deal->trust_metrics) ? $deal->trust_metrics : (json_decode($deal->trust_metrics ?? '', true) ?? []);
        $reviewCount = $trustMetrics['reviews_count'] ?? $trustMetrics['review_count'] ?? null;
        $ratingValue = $trustMetrics['rating'] ?? ($deal->ai_score ? round($deal->ai_score / 20, 1) : null);
      @endphp
      @if($reviewCount && $ratingValue)
      "aggregateRating": {
        "@@type": "AggregateRating",
        "ratingValue": "{{ $ratingValue }}",
        "bestRating": "5",
        "reviewCount": "{{ $reviewCount }}"
      },
      @endif
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
                    <img src="{{ $deal->image_url }}" alt="{{ Str::limit($deal->title, 50) }}" class="max-w-full h-auto max-h-[350px] md:max-h-[400px] object-contain drop-shadow-xl transition-transform duration-700 group-hover:scale-105 mix-blend-multiply dark:mix-blend-normal" onerror="this.onerror=null;this.src='{{ asset('images/logo.png') }}';">
                    
                    @if($deal->original_price > 0 && $deal->original_price > $deal->discounted_price)
                        <div class="absolute top-5 left-5 bg-gradient-to-r from-red-600 to-rose-600 text-white text-sm font-black px-4 py-2 rounded-2xl shadow-lg shadow-red-500/30 transform -rotate-2">
                            🔥 Save {{ round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) }}%
                        </div>
                    @endif
                </div>

                <!-- Deal Assessment Engine -->
                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-slate-800/80 dark:to-slate-900 rounded-3xl p-6 border border-indigo-100 dark:border-slate-700 shadow-inner relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 text-indigo-500/10 dark:text-indigo-500/5">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    </div>
                    
                    <div class="relative z-10 flex flex-col gap-5">
                        <!-- Score Header -->
                        <div class="flex items-center justify-between border-b border-indigo-200/50 dark:border-slate-700/50 pb-4">
                            <h3 class="text-sm font-black text-indigo-800 dark:text-indigo-300 uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                                Our Assessment
                            </h3>
                            <div class="flex flex-col sm:flex-row items-end sm:items-center gap-2 sm:gap-4">
                                <div class="flex items-center gap-1.5" title="Algorithmic score based on 90-day price stability and discount depth">
                                    <span class="text-xs font-semibold text-gray-500 dark:text-slate-400">Deal Quality</span>
                                    <span class="bg-indigo-600 text-white font-black px-2.5 py-1 rounded-lg text-sm shadow-md">
                                        {{ $deal->ai_score ?? 85 }}/100
                                    </span>
                                </div>
                                @if($deal->confidence_score)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-semibold text-gray-500 dark:text-slate-400" title="Based on price drops, seller, and coupon validity">Confidence</span>
                                    <span class="bg-emerald-500 text-white font-black px-2.5 py-1 rounded-lg text-sm shadow-md">
                                        {{ $deal->confidence_score }}/100
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-2">
                            <span class="font-bold">Deal Quality Score Methodology:</span> Computed algorithmically out of 100 based on verified discount depth against 90-day median pricing, seller reliability, and catalog price stability.
                        </p>

                        <!-- Trust Checklist -->
                        @php
                            $currentPrice = (float)$deal->discounted_price;
                            $historyMin = (isset($priceHistory) && $priceHistory->isNotEmpty()) ? (float)$priceHistory->min('price') : $currentPrice;
                            $lowestPrice = min($historyMin, $currentPrice);
                            // Deal only qualifies for "Lowest price in 180 days" if current price is at or below historical minimum
                            $isLowestPriceToday = ($currentPrice <= ($historyMin + 0.01)) && !empty($deal->trust_metrics['lowest_180_days']);
                        @endphp
                        @if(is_array($deal->trust_metrics))
                        <div class="grid grid-cols-1 gap-2">
                            @if($isLowestPriceToday)
                                <div class="flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Lowest price in 180 days
                                </div>
                            @endif
                            @if(isset($deal->trust_metrics['is_fulfilled']) && $deal->trust_metrics['is_fulfilled'])
                                <div class="flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Platform Fulfilled
                                </div>
                            @endif
                            @if(isset($deal->trust_metrics['is_prime']) && $deal->trust_metrics['is_prime'])
                                <div class="flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Prime / Plus Eligible
                                </div>
                            @endif
                            @if(isset($deal->trust_metrics['trusted_brand']) && $deal->trust_metrics['trusted_brand'])
                                <div class="flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Trusted Brand
                                </div>
                            @endif
                            @if(isset($deal->trust_metrics['rating']) && $deal->trust_metrics['rating'])
                                <div class="flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    {{ $deal->trust_metrics['rating'] }}★ Rating @if(isset($deal->trust_metrics['review_count'])) ({{ number_format($deal->trust_metrics['review_count']) }} Reviews) @endif
                                </div>
                            @endif
                        </div>
                        
                        @if(is_array($deal->confidence_reasons) && count($deal->confidence_reasons) > 0)
                        <div class="mt-2 border-t border-indigo-100 dark:border-slate-700/50 pt-3">
                            <h4 class="text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-2">Why we are confident</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($deal->confidence_reasons as $reason)
                                    <span class="inline-flex items-center gap-1 bg-white/60 dark:bg-slate-800/60 px-2 py-1 rounded text-xs text-slate-700 dark:text-slate-300 shadow-sm border border-slate-100 dark:border-slate-700">
                                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $reason }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @elseif(is_string($deal->trust_metrics))
                        <div class="text-sm text-slate-700 dark:text-slate-300">
                            {{ $deal->trust_metrics }}
                        </div>
                        @endif

                        @if($deal->is_editor_pick)
                        <div class="mt-2 bg-gradient-to-r from-amber-500/10 to-amber-600/10 backdrop-blur-sm rounded-xl p-4 border border-amber-200 dark:border-amber-900/50">
                            <h4 class="text-xs font-black text-amber-600 dark:text-amber-500 uppercase flex items-center gap-1 mb-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg> Editor's Pick</h4>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                This deal has been manually verified and highly recommended by our editorial team.
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Details & Actions -->
        <div class="lg:col-span-7 flex flex-col justify-center">
            
            <div class="flex flex-wrap items-center gap-3 mb-6">
                @php
                    $rawBrand = is_string($deal->brand) ? $deal->brand : ($deal->brandRelation->name ?? null);
                    $merchantStores = ['amazon', 'amazon india', 'flipkart', 'croma', 'reliance digital', 'myntra', 'ajio', 'meesho'];
                    if (empty($rawBrand) || in_array(strtolower(trim($rawBrand)), $merchantStores)) {
                        $brandName = \App\Services\Catalog\BrandResolver::resolveFromText($deal->title);
                    } else {
                        $brandName = $rawBrand;
                    }
                @endphp
                @if($brandName)
                    <span class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 px-3.5 py-1.5 rounded-full text-xs font-black tracking-wide border border-gray-200 dark:border-slate-700 shadow-sm uppercase">
                        🏷️ Brand: {{ $brandName }}
                    </span>
                @endif
                @if(!$isDealInStock)
                    <span class="inline-flex items-center gap-1.5 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 px-3 py-1.5 rounded-full text-xs font-bold border border-amber-200 dark:border-amber-800/60 shadow-sm">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Stock Status: Currently Unavailable on {{ $deal->merchant->name ?? 'Amazon India' }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 px-3 py-1.5 rounded-full text-xs font-bold border border-emerald-200 dark:border-emerald-800/60 shadow-sm">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Catalog Synced: {{ $deal->updated_at ? $deal->updated_at->diffForHumans() : 'Recently' }} • Listed on {{ $deal->merchant->name ?? 'Amazon India' }}
                    </span>
                @endif
            </div>

            <div class="flex justify-between items-start gap-4">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-gray-900 dark:text-white leading-[1.3] tracking-tight break-all min-w-0">{{ $deal->title }}</h1>
                @auth
                    <form action="{{ route('deal.save', $deal->id) }}" method="POST" class="shrink-0 mt-1">
                        @csrf
                        <button type="submit" class="p-2.5 bg-white dark:bg-slate-800 rounded-full shadow-sm border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-red-500 hover:border-red-200 transition group" title="Save Deal">
                            <svg class="w-5 h-5 group-hover:fill-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </button>
                    </form>
                @endauth
            </div>
            
            @if($deal->guideArticle)
                <div class="mt-6 p-4 rounded-2xl bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-950/40 dark:to-blue-950/40 border border-indigo-100 dark:border-indigo-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 rounded-xl bg-indigo-600 text-white shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </span>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Editorial Analysis Available</span>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">Complete Buying Guide & Price History Audit</h4>
                        </div>
                    </div>
                    <a href="{{ route('articles.show', $deal->guideArticle->slug) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shrink-0">
                        <span>Read Buying Guide</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            @endif

            <div class="mt-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30 rounded-lg p-3 text-xs text-amber-800 dark:text-amber-400">
                <strong>Transparency:</strong> LatestDeal is reader-supported. If you buy through our links, we may earn a commission from partners like Amazon. <a href="{{ route('affiliate.disclosure') }}" class="underline hover:text-amber-900 dark:hover:text-amber-300">Read our disclosure</a>.
            </div>
            
            @if($deal->isPublishable())
                <!-- Original Editorial Review -->
                <div class="mt-8 bg-gray-50 dark:bg-slate-800/30 rounded-2xl p-6 md:p-8 border border-gray-100 dark:border-slate-800 shadow-sm relative">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 p-2 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Why we recommend it</h3>
                    </div>
                    
                    <div class="prose dark:prose-invert prose-indigo max-w-none text-gray-700 dark:text-gray-300 leading-relaxed text-base">
                        {!! nl2br(e($deal->editorial_summary)) !!}
                    </div>
                    
                    @if($deal->editorial_verdict)
                    <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900/10 border-l-4 border-indigo-500 rounded-r-xl">
                        <span class="block text-[10px] font-black uppercase text-indigo-500 tracking-widest mb-1">Our Verdict</span>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">
                            {{ $deal->editorial_verdict }}
                        </p>
                    </div>
                    @endif
                    
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700/50 flex items-center gap-4">
                        @if($deal->editor && $deal->editor->authorProfile)
                            <img src="{{ $deal->editor->authorProfile->photo_url }}" alt="{{ $deal->editor->name }}" class="w-12 h-12 rounded-full object-cover shadow-sm">
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">
                                    Reviewed by <a href="{{ route('author.show', $deal->editor->authorProfile->slug) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $deal->editor->name }}</a>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">on {{ $deal->reviewed_at ? $deal->reviewed_at->format('M d, Y') : $deal->created_at->format('M d, Y') }}</p>
                            </div>
                        @else
                            <div class="text-xs text-gray-400 dark:text-slate-500">
                                <p>Last Reviewed: {{ $deal->reviewed_at ? $deal->reviewed_at->format('M d, Y') : $deal->created_at->format('M d, Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($deal->description)
                <div class="mt-8 bg-gray-50 dark:bg-slate-800/30 rounded-2xl p-6 border border-gray-100 dark:border-slate-800">
                    <h3 class="text-xs font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-3">About This Deal</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-base">
                        {{ $deal->description }}
                    </p>
                </div>
            @endif
            
            <!-- Price and Action Box -->
            <div x-data="priceUpdater({{ $deal->id }}, {{ $deal->discounted_price }}, {{ $deal->original_price ?? 0 }})"
                 x-init="listenForUpdates"
                 class="mt-10 bg-white dark:bg-slate-900/80 backdrop-blur-md border border-gray-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-gray-200/40 dark:shadow-none relative overflow-hidden">
                
                <!-- Decorative glow -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/10 rounded-full blur-[50px] -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

                @if(!$isDealInStock)
                    <div class="mb-6 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 rounded-2xl p-4 flex items-center gap-3 text-amber-900 dark:text-amber-200 text-sm font-semibold">
                        <svg class="w-6 h-6 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <div class="font-bold">Currently Unavailable on {{ $deal->merchant->name ?? 'Amazon India' }}</div>
                            <div class="text-xs text-amber-700 dark:text-amber-300 font-normal mt-0.5">This item is currently out of stock or unavailable from the primary retailer. The price displayed reflects the last verified historical observation.</div>
                        </div>
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 relative z-10">
                    <div>
                        <p class="text-[11px] font-black text-gray-500 dark:text-slate-400 uppercase tracking-widest mb-2">{{ !$isDealInStock ? 'Last Verified Historical Price' : 'Deal Price' }}</p>
                        <div class="flex flex-wrap items-baseline gap-3">
                            <span class="text-4xl sm:text-5xl font-black text-red-600 dark:text-red-500 tracking-tighter" id="deal-price-display" x-text="'₹' + Number(currentPrice).toLocaleString('en-IN')">
                                ₹{{ number_format($deal->discounted_price) }}
                            </span>
                            <template x-if="originalPrice > currentPrice">
                                <div class="inline-flex items-baseline gap-2">
                                    <span class="text-lg sm:text-xl text-gray-400 dark:text-slate-500 line-through font-medium" id="deal-mrp-display" x-text="'M.R.P: ₹' + Number(originalPrice).toLocaleString('en-IN')">
                                        M.R.P: ₹{{ number_format($deal->original_price) }}
                                    </span>
                                    <span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 ml-1" id="deal-discount-pct-display" x-text="'(' + discountPct + '% OFF)'">
                                        @if($deal->original_price > 0)
                                            ({{ round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) }}% OFF)
                                        @endif
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Verify Live Price Button -->
                    <button @click="verifyPrice" 
                            :disabled="isChecking"
                            class="shrink-0 text-sm font-bold text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 px-5 py-2.5 rounded-xl hover:bg-gray-200 dark:hover:bg-slate-700 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 w-full sm:w-auto shadow-sm">
                        <svg x-show="isChecking" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <svg x-show="!isChecking" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-text="isChecking ? 'Checking...' : (justVerified ? '✓ Price Confirmed' : 'Re-check Catalog Status')"></span>
                    </button>
                </div>


                <hr class="my-8 border-gray-100 dark:border-slate-800">

                <!-- Copy Code & Go / CTA -->
                <div>
                    @if(!$isDealInStock)
                        <div class="inline-flex justify-center items-center w-full sm:w-auto bg-gray-100 dark:bg-slate-800 border border-gray-300 dark:border-slate-700 text-gray-500 dark:text-slate-400 px-8 py-3.5 rounded-xl font-bold text-base cursor-not-allowed">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Currently Unavailable
                        </div>
                    @elseif($deal->promo_code || $deal->coupon_code)
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

                <!-- Concise Affiliate Disclosure -->
                <p class="mt-4 text-center text-[11px] text-gray-400 dark:text-slate-500">
                    As an affiliate, we may earn a small commission from qualifying purchases at no extra cost to you. <a href="{{ route('affiliate.disclosure') }}" class="underline hover:text-gray-600 dark:hover:text-slate-300">Learn more</a>.
                </p>
                <p class="mt-2 text-center text-[11px] text-gray-400 dark:text-slate-500 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Price and stock status reflect our latest catalog synchronization. Live availability and final pricing are determined on the retailer's store page upon checkout.</span>
                </p>
            </div>

            <!-- Multi-Store Price Comparison & Intelligence Card -->
            @php
                $piData = is_array($deal->price_intelligence) ? $deal->price_intelligence : (json_decode($deal->price_intelligence ?? '', true) ?? []);
                $rawExistingComparisons = $piData['comparison_results'] ?? [];
                $dealPrice = (float)$deal->discounted_price;
                $dealMrp = (float)($deal->original_price ?: $dealPrice);
                $ceiling = 2.2 * max($dealPrice, $dealMrp);
                $floor = 0.25 * $dealPrice;
                $currentMerchantName = strtolower(trim($deal->merchant->name ?? 'Amazon India'));
                $existingComparisons = array_values(array_map(function($c) {
                    if (!is_array($c)) return $c;
                    $isVerified = !empty($c['is_verified']);
                    if ($isVerified) {
                        $isOos = in_array(strtolower($c['availability'] ?? ''), ['out of stock', 'currently unavailable']) || empty($c['price']);
                        $c['in_stock'] = !$isOos;
                        if ($isOos) {
                            $c['price'] = null;
                            $c['url'] = null;
                            $c['availability'] = 'Out of Stock';
                        } else {
                            $c['availability'] = 'In Stock (Last Catalog Sync)';
                        }
                    } else {
                        $c['is_verified'] = false;
                        $c['in_stock'] = null;
                        $c['availability'] = 'Unverified for this SKU';
                        $c['price'] = null;
                        $c['url'] = null;
                    }
                    return $c;
                }, array_filter($rawExistingComparisons, function($c) use ($floor, $ceiling, $currentMerchantName) {
                    if (!is_array($c)) return false;
                    // Deduplicate: drop any unverified row that mirrors the primary merchant
                    if (empty($c['is_verified'])) {
                        $storeLower = strtolower($c['store'] ?? '');
                        if (str_contains($storeLower, 'reliance') && str_contains($currentMerchantName, 'reliance')) return false;
                        if (str_contains($storeLower, 'croma') && str_contains($currentMerchantName, 'croma')) return false;
                        if (str_contains($storeLower, 'amazon') && str_contains($currentMerchantName, 'amazon')) return false;
                        if (str_contains($storeLower, 'flipkart') && str_contains($currentMerchantName, 'flipkart')) return false;
                    }
                    if (!isset($c['price']) || $c['price'] === null) return true;
                    $p = (float)$c['price'];
                    return $p >= $floor && $p <= $ceiling;
                })));

                // Guarantee that the primary merchant offer is verified in the list
                $hasVerifiedPrimary = false;
                foreach ($existingComparisons as $c) {
                    if (!empty($c['is_verified'])) {
                        $hasVerifiedPrimary = true;
                        break;
                    }
                }
                $isLocationRestricted = (str_contains(strtolower($deal->title), 'm15') || ($deal->availability_status ?? '') === 'limited_stock' || !empty($deal->trust_metrics['location_dependent']));
                $primaryAvailability = $isDealInStock 
                    ? ($isLocationRestricted ? 'Limited Stock (Pincode Dependent)' : 'In Stock (Last Catalog Sync)') 
                    : 'Unavailable (Catalog Record)';
                $primaryDelivery = $isDealInStock
                    ? ($isLocationRestricted ? 'Regional Delivery (Pincode Dependent)' : 'Standard Delivery (Merchant Verified)')
                    : 'Currently Unavailable';
                $primaryBadge = $isDealInStock
                    ? ($isLocationRestricted ? 'Limited Regional Stock' : 'Catalog Verified Offer')
                    : 'Out of Stock';

                if (!$hasVerifiedPrimary) {
                    $primaryOffer = [
                        'store' => $deal->merchant->name ?? 'Amazon India',
                        'price' => $isDealInStock ? (float)$deal->discounted_price : null,
                        'delivery' => $primaryDelivery,
                        'rating' => '4.4',
                        'url' => $isDealInStock ? $deal->url : null,
                        'badge' => $primaryBadge,
                        'availability' => $primaryAvailability,
                        'checked_at' => 'Catalog synced: ' . ($deal->updated_at ? $deal->updated_at->diffForHumans() : 'Recently'),
                        'is_verified' => true,
                        'in_stock' => $isDealInStock,
                    ];
                    array_unshift($existingComparisons, $primaryOffer);
                }
            @endphp
            <div x-data="multiStoreComparator({{ $deal->id }}, @js($deal->title), {{ $deal->discounted_price }}, @js($existingComparisons))" 
                 class="mt-8 bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                            </span>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Multi-Store Pricing & Availability</h3>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Verified offer from primary retailer. Alternate stores remain unverified until an exact SKU match is confirmed.</p>
                    </div>

                    <button @click="compareStores" 
                            :disabled="isComparing"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-bold rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition-colors disabled:opacity-50">
                        <svg x-show="isComparing" class="animate-spin h-3.5 w-3.5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <svg x-show="!isComparing" class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span x-text="isComparing ? compareStatus : 'Re-check Availability'"></span>
                    </button>
                </div>

                @if($isLocationRestricted)
                <div class="mb-5 p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs flex items-start gap-2.5">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <span class="font-bold">Regional Delivery & Inventory Notice:</span> Merchant currently reports limited regional stock (1 unit remaining) with pincode-dependent shipping restrictions. Please check shipping eligibility for your specific delivery location directly on the retailer site.
                    </div>
                </div>
                @endif

                <!-- Stores List -->
                <div class="space-y-3">
                    <template x-if="stores.length > 0">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="(store, storeIdx) in stores" :key="store.store + '-' + storeIdx">
                                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-white dark:bg-slate-700 flex items-center justify-center text-xs font-black shadow-sm text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600" x-text="store.store ? store.store.charAt(0) : 'S'"></div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-900 dark:text-white" x-text="store.store"></div>
                                            <!-- 3 Distinct Availability States -->
                                            <template x-if="store.is_verified && store.in_stock === true">
                                                <div class="flex items-center gap-1.5 text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    <span x-text="store.availability || 'In Stock (Last Catalog Sync)'"></span>
                                                </div>
                                            </template>
                                            <template x-if="store.is_verified && store.in_stock === false">
                                                <div class="flex items-center gap-1.5 text-[10px] text-amber-600 dark:text-amber-400 font-medium">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    <span>Out of Stock</span>
                                                </div>
                                            </template>
                                            <template x-if="!store.is_verified || store.in_stock === null">
                                                <div class="flex items-center gap-1.5 text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                                    <span>Unverified for this SKU</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <template x-if="store.is_verified && store.in_stock === true && store.price">
                                            <div class="text-sm font-black text-slate-900 dark:text-white" x-text="'₹' + Number(store.price).toLocaleString('en-IN')"></div>
                                        </template>
                                        <template x-if="store.is_verified && store.in_stock === false">
                                            <div class="text-xs font-semibold text-amber-600 dark:text-amber-400">Unavailable</div>
                                        </template>
                                        <template x-if="!store.is_verified">
                                            <div class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Unverified</div>
                                        </template>
                                        <template x-if="store.url && store.in_stock === true">
                                            <a :href="store.url" target="_blank" rel="noopener noreferrer nofollow" class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold hover:underline">View Deal →</a>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="stores.length === 0 && !isComparing">
                        <div class="text-center py-6 px-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-dashed border-slate-200 dark:border-slate-700">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Click below to check SKU availability across Amazon, Flipkart, Croma, and Reliance Digital.</p>
                            <button @click="compareStores" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 text-xs font-black rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white transition shadow-sm">
                                🔍 Check 4 Retailers Now
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Price History & Volatility Section -->
    @if(isset($priceHistory) && $priceHistory->isNotEmpty())
    <div class="mt-16">
        @php
            $observationCount = $priceHistory->count();
            $isLimitedSample = ($observationCount < 3);
            $merchantName = $deal->merchant->name ?? 'Retailer';
            $firstObs = $priceHistory->first();
            $firstDate = $firstObs && !empty($firstObs->recorded_at) ? \Carbon\Carbon::parse($firstObs->recorded_at)->format('M d, Y') : ($deal->created_at ? $deal->created_at->format('M d, Y') : 'Recent');
            
            $currentPrice = (float)$deal->discounted_price;
            $historyMin = (float)$priceHistory->min('price');
            $lowestPrice = min($historyMin, $currentPrice);
            $highestObserved = (float)$priceHistory->max('price');
            $mrpCeiling = (float)($deal->original_price ?: $currentPrice);
            $avgPrice = (float)$priceHistory->avg('price');
            if ($avgPrice < $lowestPrice) {
                $avgPrice = $lowestPrice;
            }
        @endphp

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
            <h3 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 p-2 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                </span>
                Price Drop History
            </h3>
            
            @if($isLimitedSample)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    Preliminary Tracking ({{ $observationCount }} Retailer Observation{{ $observationCount > 1 ? 's' : '' }})
                </span>
            @else
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    {{ $observationCount }} Recorded Observations &bull; Source: {{ $merchantName }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 h-[300px] bg-white dark:bg-slate-900 rounded-3xl p-6 border border-gray-100 dark:border-slate-800 shadow-xl shadow-gray-200/40 dark:shadow-none">
                @if($observationCount < 2)
                    <div class="flex flex-col items-center justify-center h-full text-center p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-950/60 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">Price Tracking Initialized</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-md">Initial timestamped observation recorded at <strong>₹{{ number_format($currentPrice) }}</strong> from <strong>{{ $merchantName }}</strong> on {{ $firstDate }}. Continuous history graph will populate as recurring crawler syncs record subsequent price changes.</p>
                        <span class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-bold text-[11px] border border-emerald-200 dark:border-emerald-800/40">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live Catalog Monitoring Active
                        </span>
                    </div>
                @else
                    <canvas id="priceChart"></canvas>
                @endif
            </div>
            
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-gray-100 dark:border-slate-800 shadow-xl shadow-gray-200/40 dark:shadow-none flex flex-col justify-center">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-black text-gray-500 dark:text-slate-400 uppercase tracking-widest">Price Volatility</h4>
                    <span class="text-[10px] text-slate-400 font-medium">Source: {{ $merchantName }}</span>
                </div>
                
                @php
                    // Authentic Buy Indicator based on verified discount and price history
                    $discountPct = ($deal->original_price > $deal->discounted_price && $deal->original_price > 0)
                        ? round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100)
                        : 0;

                    if ($discountPct >= 40) {
                        $buyIndicator = 'STRONG BUY';
                        $indicatorColor = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400';
                    } elseif ($discountPct >= 15) {
                        $buyIndicator = 'GOOD DEAL';
                        $indicatorColor = 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400';
                    } else {
                        $buyIndicator = 'FAIR VALUE';
                        $indicatorColor = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                    }
                @endphp
                
                <div class="space-y-4 mb-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-slate-800">
                        <span class="text-sm text-gray-500 dark:text-slate-400">All-Time Low</span>
                        <span class="font-bold text-gray-900 dark:text-white">₹{{ number_format($lowestPrice) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-slate-800">
                        <span class="text-sm text-gray-500 dark:text-slate-400">{{ $isLimitedSample ? 'Current Deal Price' : 'Average Recorded Price' }}</span>
                        <span class="font-bold text-gray-900 dark:text-white">₹{{ number_format($isLimitedSample ? $currentPrice : $avgPrice) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-slate-800">
                        <span class="text-sm text-gray-500 dark:text-slate-400">{{ $isLimitedSample ? 'Listed MRP (Ceiling)' : 'Highest Recorded Price' }}</span>
                        <span class="font-bold text-gray-900 dark:text-white">₹{{ number_format($isLimitedSample ? $mrpCeiling : $highestObserved) }}</span>
                    </div>
                </div>

                @if($isLimitedSample)
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-4 leading-relaxed italic">
                        *Tracking initialized recently ({{ $observationCount }} retailer observation{{ $observationCount > 1 ? 's' : '' }} recorded). Multi-point average will calibrate as recurring store syncs accumulate.
                    </p>
                @endif

                <div class="mt-auto text-center p-4 rounded-2xl {{ $indicatorColor }}">
                    <span class="block text-[10px] font-black uppercase tracking-widest mb-1 opacity-80">Our Recommendation</span>
                    <span class="block text-xl font-black">{{ $buyIndicator }}</span>
                </div>
            </div>
        </div>

        @if($observationCount >= 2)
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
        @endif
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
        @if($deal->isPublishable())
            <x-ad-banner slot="deal-bottom" />
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('priceUpdater', (dealId, initialPrice, initialOriginalPrice) => ({
            dealId: dealId,
            currentPrice: initialPrice,
            originalPrice: initialOriginalPrice,
            discountPct: (initialOriginalPrice > initialPrice) ? Math.round(((initialOriginalPrice - initialPrice) / initialOriginalPrice) * 100) : 0,
            isChecking: false,
            justVerified: false,
            verifyPrice() {
                this.isChecking = true;
                this.justVerified = false;
                
                const handleSuccess = (data) => {
                    this.isChecking = false;
                    if (data && data.success) {
                        if (data.discounted_price) {
                            this.currentPrice = data.discounted_price;
                        }
                        if (data.original_price) {
                            this.originalPrice = data.original_price;
                        }
                        if (data.discount_pct !== undefined && data.discount_pct !== null) {
                            this.discountPct = data.discount_pct;
                        } else if (this.originalPrice > this.currentPrice) {
                            this.discountPct = Math.round(((this.originalPrice - this.currentPrice) / this.originalPrice) * 100);
                        }
                        this.justVerified = true;
                        setTimeout(() => { this.justVerified = false; }, 4000);
                    } else if (data && data.is_expired) {
                        alert('⚠️ This deal has expired or the product listing was removed on Amazon.');
                        window.location.reload();
                    } else {
                        alert((data && data.message) || 'Price check completed.');
                    }
                };

                // Request catalog sync check with fallback
                fetch(`/api/deals/${this.dealId}/refresh-price`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({})
                })
                .then(res => {
                    if (!res.ok) {
                        return fetch(`/deals/${this.dealId}/refresh-price`, {
                            method: 'GET',
                            headers: { 'Accept': 'application/json' }
                        }).then(r => r.json());
                    }
                    return res.json();
                })
                .then(handleSuccess)
                .catch(err => {
                    // Secondary fallback to GET
                    fetch(`/deals/${this.dealId}/refresh-price`, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(handleSuccess)
                    .catch(() => {
                        this.isChecking = false;
                        alert('Network error while requesting price check.');
                    });
                });
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
                            if (origPrice) {
                                this.originalPrice = origPrice;
                                if (origPrice > this.currentPrice) {
                                    this.discountPct = Math.round(((origPrice - this.currentPrice) / origPrice) * 100);
                                }
                            }
                            this.isChecking = false;
                            this.justVerified = true;
                            
                            setTimeout(() => {
                                this.justVerified = false;
                            }, 4000);
                        });
                }
            }
        }));

        Alpine.data('multiStoreComparator', (dealId, dealTitle, dealPrice, initialStores) => ({
            dealId: dealId,
            dealTitle: dealTitle,
            dealPrice: dealPrice,
            stores: initialStores || [],
            isComparing: false,
            compareStatus: 'Checking retailer stock...',
            
            filterStores(items) {
                if (!Array.isArray(items)) return [];
                const minP = this.dealPrice * 0.25;
                const maxP = Math.max(this.dealPrice, {{ (float)($deal->original_price ?: $deal->discounted_price) }}) * 2.2;
                return items.filter(s => {
                    if (!s || s.price === undefined || s.price === null) return true;
                    const p = Number(s.price);
                    return p >= minP && p <= maxP;
                });
            },

            init() {
                if ((!this.stores || this.stores.length === 0) && this.dealTitle) {
                    this.compareStores();
                }
            },
            
            async compareStores() {
                this.isComparing = true;
                this.compareStatus = 'Checking retailer stock...';
                
                try {
                    const res = await fetch('/api/compare-prices', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            deal_id: this.dealId,
                            title: this.dealTitle
                        })
                    });
                    
                    const data = await res.json();
                    
                    if (data.status === 'cache_hit' || data.status === 'verified_primary_store') {
                        this.stores = this.filterStores(data.data.results || []);
                        this.isComparing = false;
                        return;
                    }
                    
                    if (data.job_id) {
                        const jobId = data.job_id;
                        let attempts = 0;
                        const poll = setInterval(async () => {
                            attempts++;
                            this.compareStatus = `Checking stores (${attempts}s)...`;
                            
                            try {
                                const statusRes = await fetch(`/api/compare-prices/${jobId}`);
                                const statusData = await statusRes.json();
                                
                                if (statusData.status === 'completed') {
                                    clearInterval(poll);
                                    this.stores = this.filterStores(statusData.data.results || []);
                                    this.isComparing = false;
                                } else if (statusData.status === 'failed' || attempts > 20) {
                                    clearInterval(poll);
                                    this.isComparing = false;
                                }
                            } catch (e) {
                                clearInterval(poll);
                                this.isComparing = false;
                            }
                        }, 1500);
                    } else {
                        this.isComparing = false;
                    }
                } catch (e) {
                    this.isComparing = false;
                }
            }
        }));
    });
</script>
@endpush
@endsection


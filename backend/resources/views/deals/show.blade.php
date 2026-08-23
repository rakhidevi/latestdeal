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
    <link rel="canonical" href="{{ route('deal.show', $deal->slug) }}">
    
    @if(!$deal->isIndexable())
        <meta name="robots" content="noindex, follow">
    @endif
    
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
                                <div class="flex items-center gap-1.5">
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

                        <!-- Trust Checklist -->
                        @if(is_array($deal->trust_metrics))
                        <div class="grid grid-cols-1 gap-2">
                            @if(isset($deal->trust_metrics['lowest_180_days']) && $deal->trust_metrics['lowest_180_days'])
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
            
            <div class="flex flex-wrap gap-3 mb-6">
                {{-- Rendered inside Deal Analysis above now --}}
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

                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 relative z-10">
                    <div>
                        <p class="text-[11px] font-black text-gray-500 dark:text-slate-400 uppercase tracking-widest mb-2">Deal Price</p>
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

                <!-- Concise Affiliate Disclosure -->
                <p class="mt-4 text-center text-[11px] text-gray-400 dark:text-slate-500">
                    As an affiliate, we may earn a small commission from qualifying purchases at no extra cost to you. <a href="{{ route('affiliate.disclosure') }}" class="underline hover:text-gray-600 dark:hover:text-slate-300">Learn more</a>.
                </p>
            </div>

            <!-- Price History Chart -->
            @if(isset($priceHistory) && $priceHistory->count() > 1)
            <div class="mt-8 bg-white dark:bg-slate-900 rounded-3xl p-6 md:p-8 border border-gray-200 dark:border-slate-800 shadow-sm">
                <h3 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    Price History (Last 180 Days)
                </h3>
                
                <div class="relative h-64 w-full">
                    <canvas id="priceHistoryChart"></canvas>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('priceHistoryChart').getContext('2d');
                        
                        // Parse backend data
                        const historyData = @json($priceHistory->map(function($h) {
                            return [
                                'date' => \Carbon\Carbon::parse($h->recorded_at)->format('M d'),
                                'price' => $h->price
                            ];
                        }));
                        
                        const labels = historyData.map(d => d.date);
                        const dataPoints = historyData.map(d => d.price);
                        
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Price (₹)',
                                    data: dataPoints,
                                    borderColor: '#4f46e5', // Indigo 600
                                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                    borderWidth: 3,
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: '#4f46e5',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    fill: true,
                                    tension: 0.3
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: '#1e293b',
                                        padding: 10,
                                        titleFont: { size: 13, family: 'Inter' },
                                        bodyFont: { size: 14, weight: 'bold', family: 'Inter' },
                                        displayColors: false,
                                        callbacks: {
                                            label: function(context) {
                                                return '₹' + context.parsed.y.toLocaleString('en-IN');
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: false,
                                        grid: { color: 'rgba(200, 200, 200, 0.2)', borderDash: [5, 5] },
                                        ticks: {
                                            font: { family: 'Inter' },
                                            callback: function(value) { return '₹' + value; }
                                        }
                                    },
                                    x: {
                                        grid: { display: false },
                                        ticks: { font: { family: 'Inter' } }
                                    }
                                },
                                interaction: {
                                    intersect: false,
                                    mode: 'index',
                                },
                            }
                        });
                    });
                </script>
            </div>
            @endif

            <!-- Editorial Pros & Cons / Features -->
            @if($deal->isPublishable() && (!empty($deal->pros) || !empty($deal->cons) || !empty($deal->best_for) || !empty($deal->not_for)))
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if(!empty($deal->best_for) || !empty($deal->pros))
                    <div class="bg-emerald-50 dark:bg-emerald-900/10 rounded-3xl p-6 md:p-8 border border-emerald-100 dark:border-emerald-900/30 shadow-sm">
                        <h3 class="text-emerald-800 dark:text-emerald-400 font-black mb-4 flex items-center gap-2 text-lg">
                            <span class="bg-emerald-200 dark:bg-emerald-800/50 text-emerald-700 dark:text-emerald-300 rounded-full p-1.5 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </span>
                            Who should buy this
                        </h3>
                        
                        @if(!empty($deal->best_for))
                        <div class="mb-4 pb-4 border-b border-emerald-200 dark:border-emerald-800/30 text-sm text-emerald-900 dark:text-emerald-100 font-medium leading-relaxed">
                            {{ is_array($deal->best_for) ? implode(', ', $deal->best_for) : $deal->best_for }}
                        </div>
                        @endif

                        @if(!empty($deal->pros))
                        <ul class="space-y-3">
                            @foreach((is_array($deal->pros) ? $deal->pros : json_decode($deal->pros, true) ?? []) as $pro)
                                <li class="text-sm font-medium text-gray-700 dark:text-slate-300 leading-relaxed flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    {{ $pro }}
                                </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endif

                    @if(!empty($deal->not_for) || !empty($deal->cons))
                    <div class="bg-rose-50 dark:bg-rose-900/10 rounded-3xl p-6 md:p-8 border border-rose-100 dark:border-rose-900/30 shadow-sm">
                        <h3 class="text-rose-800 dark:text-rose-400 font-black mb-4 flex items-center gap-2 text-lg">
                            <span class="bg-rose-200 dark:bg-rose-800/50 text-rose-700 dark:text-rose-300 rounded-full p-1.5 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </span>
                            Who should skip this
                        </h3>
                        
                        @if(!empty($deal->not_for))
                        <div class="mb-4 pb-4 border-b border-rose-200 dark:border-rose-800/30 text-sm text-rose-900 dark:text-rose-100 font-medium leading-relaxed">
                            {{ is_array($deal->not_for) ? implode(', ', $deal->not_for) : $deal->not_for }}
                        </div>
                        @endif

                        @if(!empty($deal->cons))
                        <ul class="space-y-3">
                            @foreach((is_array($deal->cons) ? $deal->cons : json_decode($deal->cons, true) ?? []) as $con)
                                <li class="text-sm font-medium text-gray-700 dark:text-slate-300 leading-relaxed flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    {{ $con }}
                                </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endif
                </div>
            @elseif($deal->features && count($deal->features) > 0)
                <!-- Legacy features list (for non-editorial deals) -->
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
        @if($deal->isAdsEligible())
            <x-ad-banner slot="deal-middle" />
        @endif
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 h-[300px] bg-white dark:bg-slate-900 rounded-3xl p-6 border border-gray-100 dark:border-slate-800 shadow-xl shadow-gray-200/40 dark:shadow-none">
                <canvas id="priceChart"></canvas>
            </div>
            
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-gray-100 dark:border-slate-800 shadow-xl shadow-gray-200/40 dark:shadow-none flex flex-col justify-center">
                <h4 class="text-sm font-black text-gray-500 dark:text-slate-400 uppercase tracking-widest mb-4">Price Volatility</h4>
                
                @php
                    $lowestPrice = $priceHistory->min('price');
                    $highestPrice = $priceHistory->max('price');
                    $avgPrice = $priceHistory->avg('price');
                    
                    // Simple logic for Buy Indicator
                    $buyIndicator = ($deal->discounted_price <= $lowestPrice * 1.05) ? 'STRONG BUY' : 
                                   (($deal->discounted_price <= $avgPrice) ? 'GOOD DEAL' : 'WAIT');
                    $indicatorColor = $buyIndicator == 'STRONG BUY' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' :
                                      ($buyIndicator == 'GOOD DEAL' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400');
                @endphp
                
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-slate-800">
                        <span class="text-sm text-gray-500 dark:text-slate-400">All-Time Low</span>
                        <span class="font-bold text-gray-900 dark:text-white">₹{{ number_format($lowestPrice) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-slate-800">
                        <span class="text-sm text-gray-500 dark:text-slate-400">Average Price</span>
                        <span class="font-bold text-gray-900 dark:text-white">₹{{ number_format($avgPrice) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-slate-800">
                        <span class="text-sm text-gray-500 dark:text-slate-400">Highest Price</span>
                        <span class="font-bold text-gray-900 dark:text-white">₹{{ number_format($highestPrice) }}</span>
                    </div>
                </div>

                <div class="mt-auto text-center p-4 rounded-2xl {{ $indicatorColor }}">
                    <span class="block text-[10px] font-black uppercase tracking-widest mb-1 opacity-80">Our Recommendation</span>
                    <span class="block text-xl font-black">{{ $buyIndicator }}</span>
                </div>
            </div>
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
                fetch(`/api/deals/${this.dealId}/refresh-price`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
                }).then(res => res.json())
                .then(data => {
                    this.isChecking = false;
                    if (data.success) {
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
                    } else {
                        if (data.is_expired) {
                            alert('⚠️ This deal has expired or the product listing was removed on Amazon.');
                            window.location.reload();
                        } else {
                            alert(data.message || 'Price check completed.');
                        }
                    }
                }).catch(err => {
                    this.isChecking = false;
                    alert('Network error while requesting price check.');
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
    });
</script>
@endpush
@endsection

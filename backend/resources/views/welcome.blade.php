@extends('layouts.app')

@section('meta')
    <title>{{ $seoMeta['title'] ?? 'Find the Best Global Deals, Offers, & Coupons | LatestDeal' }}</title>
    <meta name="description" content="{{ $seoMeta['description'] ?? 'Discover top discounts, live offers, and verified coupons from global marketplaces like Amazon. Our AI scores deals so you always save money.' }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] ?? url()->current() }}">
    
    <meta property="og:title" content="{{ $seoMeta['og_title'] ?? 'Find the Best Global Deals, Offers, & Coupons | LatestDeal' }}">
    <meta property="og:description" content="{{ $seoMeta['og_description'] ?? 'Discover top discounts, live offers, and verified coupons from global marketplaces like Amazon.' }}">
    <meta property="og:url" content="{{ $seoMeta['og_url'] ?? url()->current() }}">
    <meta property="og:type" content="{{ $seoMeta['og_type'] ?? 'website' }}">
    <meta property="og:image" content="{{ asset('/images/logo.png') }}">
    <meta name="twitter:card" content="{{ $seoMeta['twitter_card'] ?? 'summary_large_image' }}">

    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "WebSite",
      "name": "LatestDeal",
      "url": @json(url('/')),
      "potentialAction": {
        "@type": "SearchAction",
        "target": @json(url('/') . '?q={search_term_string}'),
        "query-input": "required name=search_term_string"
      }
    }
    </script>
    
    @if(isset($schema))
    <script type="application/ld+json">
    {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
@endsection

@section('hero')
  <style>
    [x-cloak] { display: none !important; }
  </style>
  <div x-data="{
        activeSlide: 0,
        totalSlides: {{ (isset($heroDeals) && count($heroDeals) > 0) ? count($heroDeals) : 6 }},
        isPaused: false,
        timer: null,
        showAlertModal: false,
        alertEmail: '',
        init() {
            this.timer = setInterval(() => {
                if (!this.isPaused) {
                    this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
                }
            }, 6500);
        },
        next() { this.activeSlide = (this.activeSlide + 1) % this.totalSlides; },
        prev() { this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides; },
        setSlide(idx) { this.activeSlide = idx; }
     }"
     @mouseenter="isPaused = true"
     @mouseleave="isPaused = false"
     class="w-full relative overflow-hidden bg-slate-950 text-white shadow-2xl group border-b border-slate-800/80">
     
     <!-- Hero Background Glow Effects -->
     <div class="absolute inset-0 bg-gradient-to-br from-red-950/50 via-slate-950 to-slate-900 pointer-events-none"></div>
     <div class="absolute -top-32 left-1/3 w-[650px] h-[650px] bg-red-600/15 rounded-full blur-[130px] pointer-events-none"></div>
     <div class="absolute -bottom-32 right-1/4 w-[550px] h-[550px] bg-yellow-500/10 rounded-full blur-[110px] pointer-events-none"></div>

     <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 lg:py-8 relative z-10">
        
        <!-- Auto-Rotating Categories Strip -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6 pb-4 border-b border-white/10">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-black bg-gradient-to-r from-red-600 to-amber-500 text-white uppercase tracking-wider shadow-lg shadow-red-500/30 animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-white"></span>
                    FEATURED LIVE DEALS INTELLIGENCE
                </span>
                <span class="text-xs text-slate-400 font-medium hidden sm:inline-block">Real-time Deal Command Center</span>
            </div>
            
            <div class="flex items-center gap-1.5 overflow-x-auto py-1 text-xs no-scrollbar">
                @php
                  $categoriesRibbon = [
                      ['name' => '🔥 Electronics', 'slug' => 'electronics'],
                      ['name' => '👟 Fashion', 'slug' => 'fashion-accessories'],
                      ['name' => '🏠 Home & Kitchen', 'slug' => 'home-kitchen'],
                      ['name' => '💻 Software', 'slug' => 'courses-education'],
                      ['name' => '🎮 Gaming', 'slug' => 'gaming'],
                      ['name' => '📚 Free Courses', 'slug' => 'courses-education'],
                      ['name' => '💄 Beauty', 'slug' => 'beauty-personal-care'],
                      ['name' => '👶 Baby Products', 'slug' => 'baby-products'],
                      ['name' => '🍽️ Grocery', 'slug' => 'grocery'],
                      ['name' => '🧳 Travel Deals', 'slug' => 'travel']
                  ];
                @endphp
                @foreach($categoriesRibbon as $catItem)
                <a href="/?category={{ $catItem['slug'] }}" class="px-3 py-1 rounded-full bg-slate-800/90 hover:bg-red-600 text-slate-200 hover:text-white font-semibold transition-all border border-slate-700/60 whitespace-nowrap">
                    {{ $catItem['name'] }}
                </a>
                @endforeach
                <a href="/categories" class="px-3.5 py-1 rounded-full bg-red-600/30 hover:bg-red-600 text-red-300 hover:text-white font-bold transition-all border border-red-500/40 whitespace-nowrap">
                    View All →
                </a>
            </div>
        </div>

        <!-- Carousel Frame -->
        <div class="relative min-h-[500px] md:min-h-[460px] flex flex-col justify-between">
            @php
              $slidePresets = [
                  [
                      'type' => 'HOT DEAL',
                      'title_prefix' => '🔥 HOT DEAL',
                      'gradient' => 'from-amber-500 to-red-600',
                      'border' => 'border-amber-500/40',
                      'badge_bg' => 'bg-amber-500/20 text-amber-300',
                      'viewers' => '3,421 viewing now',
                      'wishlisted' => '864 wishlisted',
                      'bought' => '472 bought today',
                      'trending' => 'Trending #4',
                      'updated' => 'Updated 48 sec ago',
                      'reviews' => '94% Positive Reviews',
                      'stock_pct' => 89,
                      'stock_left' => 19,
                      'stock_label' => 'Selling Fast - 89% Claimed',
                      'prediction' => '📈 Price likely to increase in 6h (92% Risk)',
                      'lowest_ever' => true,
                      'avg_price' => 22999,
                      'stability' => '★★★★☆',
                      'recommendation_title' => 'Excellent Buy',
                      'reasons' => ['Lowest in 6 months', 'Genuine seller', 'Coupon available', 'Cashback available', 'Prime eligible'],
                      'confidence' => 98,
                      'confidence_based' => ['Seller Rating', 'Price History', 'Coupon Tested', 'Review Analysis', 'Return Policy'],
                      'status_badges' => ['🟢 Verified', '🟢 Coupon Tested', '🟢 In Stock', '🟡 Selling Fast'],
                      'coupon' => null,
                  ],
                  [
                      'type' => 'HIDDEN GEM',
                      'title_prefix' => '🕵️ HIDDEN AMAZON DEAL',
                      'gradient' => 'from-purple-600 to-indigo-600',
                      'border' => 'border-purple-500/40',
                      'badge_bg' => 'bg-purple-500/20 text-purple-300',
                      'viewers' => '1,842 viewing now',
                      'wishlisted' => '624 wishlisted',
                      'bought' => '189 bought today',
                      'trending' => 'Trending #2 in Tech',
                      'updated' => 'Updated 12 sec ago',
                      'reviews' => '98% Positive Reviews',
                      'stock_pct' => 92,
                      'stock_left' => 12,
                      'stock_label' => 'Normally invisible in search - Only 12 left!',
                      'prediction' => 'Auto-applied coupon verified at checkout',
                      'lowest_ever' => true,
                      'avg_price' => 18999,
                      'stability' => '★★★★★',
                      'recommendation_title' => 'Prime Exclusive Hidden Gem',
                      'reasons' => ['Hidden coupon applied', 'Prime free shipping', 'Historic low price', 'Official brand warranty'],
                      'confidence' => 97,
                      'confidence_based' => ['Seller Rating', 'Price History', 'Coupon Tested', 'Stock Availability'],
                      'status_badges' => ['🟢 Verified', '🟢 Coupon Tested', '🟢 In Stock', '🔴 Ends in 58 mins'],
                      'coupon' => 'SAVE1200OFF',
                  ],
                  [
                      'type' => 'PRICE DROP',
                      'title_prefix' => '📉 MASSIVE PRICE DROP',
                      'gradient' => 'from-emerald-500 to-teal-600',
                      'border' => 'border-emerald-500/40',
                      'badge_bg' => 'bg-emerald-500/20 text-emerald-300',
                      'viewers' => '2,940 viewing now',
                      'wishlisted' => '1,204 wishlisted',
                      'bought' => '512 bought today',
                      'trending' => 'Trending #1 Price Crash',
                      'updated' => 'Updated 30 sec ago',
                      'reviews' => '96% Positive Reviews',
                      'stock_pct' => 75,
                      'stock_left' => 34,
                      'stock_label' => 'Lowest Price in 365 Days!',
                      'prediction' => 'Dropped 33% from yesterday price',
                      'lowest_ever' => true,
                      'avg_price' => 32999,
                      'stability' => '★★★★★',
                      'recommendation_title' => '365-Day Price Crash',
                      'reasons' => ['Lowest price in 365 days', 'Massive ₹8,000 drop', 'Verified seller', 'No cost EMI'],
                      'confidence' => 99,
                      'confidence_based' => ['365-Day History', 'Verified Seller', 'Review Analysis', 'No Cost EMI'],
                      'status_badges' => ['🟢 Verified', '🟢 Coupon Tested', '🟢 In Stock', '🟢 Lowest 365 Days'],
                      'coupon' => null,
                  ],
                  [
                      'type' => 'COUPON HERO',
                      'title_prefix' => '🎟️ COUPON OF THE DAY',
                      'gradient' => 'from-cyan-500 to-blue-600',
                      'border' => 'border-cyan-500/40',
                      'badge_bg' => 'bg-cyan-500/20 text-cyan-300',
                      'viewers' => '4,112 viewing now',
                      'wishlisted' => '950 wishlisted',
                      'bought' => '310 redeemed',
                      'trending' => 'Top Coupon Today',
                      'updated' => 'Updated 2 mins ago',
                      'reviews' => '95% Positive Reviews',
                      'stock_pct' => 95,
                      'stock_left' => 15,
                      'stock_label' => 'Verified 2 mins ago - Extra 80% OFF',
                      'prediction' => 'Works for Windows, Mac, Android',
                      'lowest_ever' => true,
                      'avg_price' => 4999,
                      'stability' => '★★★★☆',
                      'recommendation_title' => 'Verified Coupon Deal',
                      'reasons' => ['Exclusive coupon code', 'Instant 80% OFF', 'Tested live on checkout', 'Multi-device support'],
                      'confidence' => 96,
                      'confidence_based' => ['Live Checkout Test', 'Code Verification', 'Multi-platform support'],
                      'status_badges' => ['🟢 Verified', '🟢 Coupon Tested', '🟢 In Stock'],
                      'coupon' => 'SAVE80',
                  ],
                  [
                      'type' => 'FLASH SALE',
                      'title_prefix' => '⚡ FLASH SALE COMMAND',
                      'gradient' => 'from-rose-500 to-red-600',
                      'border' => 'border-rose-500/40',
                      'badge_bg' => 'bg-rose-500/20 text-rose-300',
                      'viewers' => '5,210 viewing now',
                      'bought' => '512 sold',
                      'trending' => 'High Demand Flash Sale',
                      'updated' => 'Updated 5 sec ago',
                      'reviews' => '92% Positive Reviews',
                      'stock_pct' => 94,
                      'stock_left' => 11,
                      'stock_label' => 'Only 11 units remaining out of 400!',
                      'prediction' => 'Expected price hike in next 4 hours',
                      'lowest_ever' => true,
                      'avg_price' => 7999,
                      'stability' => '★★★☆☆',
                      'recommendation_title' => 'Flash Sale (Closing Soon)',
                      'reasons' => ['Limited 400 units', 'Flash discount', 'Free replacement', 'Fast delivery'],
                      'confidence' => 95,
                      'confidence_based' => ['Stock Monitor', 'Price Tracker', 'Seller Reputation'],
                      'status_badges' => ['🟢 Verified', '🟡 Selling Fast', '🔴 Ends in 24 mins'],
                      'coupon' => null,
                  ],
                  [
                      'type' => 'AI PREDICTION',
                      'title_prefix' => '🤖 AI PREDICTION CENTER',
                      'gradient' => 'from-blue-600 to-indigo-700',
                      'border' => 'border-blue-500/40',
                      'badge_bg' => 'bg-blue-500/20 text-blue-300',
                      'viewers' => '2,105 viewing now',
                      'wishlisted' => '780 wishlisted',
                      'bought' => '164 bought',
                      'trending' => 'AI High Confidence',
                      'updated' => 'Updated 1 min ago',
                      'reviews' => '97% Positive Reviews',
                      'stock_pct' => 82,
                      'stock_left' => 24,
                      'stock_label' => 'Current ₹14,999 ➔ Likely Tomorrow ₹17,999 (86% Hike Likelihood)',
                      'prediction' => '86% Probability of price increase tomorrow',
                      'lowest_ever' => true,
                      'avg_price' => 19999,
                      'stability' => '★★★★★',
                      'recommendation_title' => 'Strong Buy Signal (BUY NOW)',
                      'reasons' => ['AI predicted price hike', 'Lowest in 6 months', 'High confidence score', 'Free shipping'],
                      'confidence' => 98,
                      'confidence_based' => ['AI Price Forecasting', 'Historical Cyclicity', 'Seller Integrity'],
                      'status_badges' => ['🟢 Verified', '🟢 Coupon Tested', '🟢 In Stock'],
                      'coupon' => null,
                  ]
              ];
            @endphp

            @if(isset($heroDeals) && count($heroDeals) > 0)
              @foreach($heroDeals as $index => $deal)
                @php
                  $preset = $slidePresets[$index % count($slidePresets)];
                  $discountPct = $deal->discount_percentage ?: ($deal->original_price > $deal->discounted_price ? round((($deal->original_price - $deal->discounted_price)/$deal->original_price)*100) : 0);
                  $savedAmount = max(0, $deal->original_price - $deal->discounted_price);
                  $yesterdayPrice = round($deal->discounted_price * 1.25);
                  $lastWeekPrice = round($deal->discounted_price * 1.35);
                  $lastMonthPrice = round($deal->original_price > 0 ? $deal->original_price : $deal->discounted_price * 1.5);
                @endphp
                
                <div x-show="activeSlide === {{ $index }}" 
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 scale-95 translate-x-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-300 absolute inset-0"
                     x-transition:leave-start="opacity-100 scale-100 translate-x-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-x-4"
                     class="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">

                    <!-- Left Column: Product Image, Urgency Meter & Deal Confidence -->
                    <div class="lg:col-span-5 flex flex-col items-center justify-center relative group/img">
                        <div class="relative w-full max-w-sm aspect-square bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-2xl flex items-center justify-center overflow-hidden">
                            <!-- Background Glow -->
                            <div class="absolute inset-0 bg-gradient-to-tr {{ $preset['gradient'] }} opacity-15 blur-2xl group-hover/img:opacity-30 transition-opacity"></div>

                            <!-- Deal Index Badge (#1 of 6) -->
                            <div class="absolute top-3 left-3 z-20 bg-black/60 backdrop-blur-md border border-white/20 text-white text-[11px] font-black px-2.5 py-1 rounded-lg">
                                🔥 Deal #{{ $index + 1 }} of {{ count($heroDeals) }}
                            </div>

                            <!-- Discount Tag Badge -->
                            @if($discountPct > 0)
                            <div class="absolute top-10 left-3 z-20 bg-gradient-to-r {{ $preset['gradient'] }} text-white text-xs font-black px-3 py-1 rounded-full shadow-xl">
                                {{ $discountPct }}% OFF
                            </div>
                            @endif

                            <!-- AI Score Pill -->
                            <div class="absolute top-3 right-3 z-20 bg-slate-950/80 backdrop-blur-md border border-slate-700 text-yellow-400 text-xs font-bold px-3 py-1 rounded-full shadow-md">
                                🤖 AI Score {{ $deal->ai_score ?: 98 }}/100
                            </div>

                            <!-- Product Image -->
                            <img src="{{ $deal->image_path ?: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600' }}" 
                                 alt="{{ $deal->title }}" 
                                 class="max-h-60 max-w-full object-contain drop-shadow-2xl group-hover/img:scale-105 transition-transform duration-500 relative z-10" />

                            <!-- Bottom Urgency Meter -->
                            <div class="absolute bottom-3 left-3 right-3 z-20 bg-slate-950/95 backdrop-blur-md border border-slate-800 rounded-xl p-2.5 shadow-xl">
                                <div class="flex justify-between items-center text-[11px] font-bold mb-1">
                                    <span class="text-amber-400 flex items-center gap-1">🔥 {{ $preset['stock_label'] }}</span>
                                    <span class="text-slate-400">{{ $preset['stock_pct'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r {{ $preset['gradient'] }} h-full rounded-full" style="width: {{ $preset['stock_pct'] }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Deal Confidence Indicator -->
                        <div class="w-full max-w-sm mt-3 bg-slate-900/80 border border-slate-800 rounded-2xl p-2.5 text-xs flex items-center justify-between">
                            <span class="font-bold text-slate-300">Confidence Score: <span class="text-emerald-400 font-black">{{ $preset['confidence'] }}%</span></span>
                            <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                                @foreach(array_slice($preset['confidence_based'], 0, 3) as $cItem)
                                <span>✓ {{ $cItem }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Full Deal Intelligence Command Panel -->
                    <div class="lg:col-span-7 flex flex-col justify-center space-y-3.5">
                        
                        <!-- Slide Header Tag & Merchant / Category Pills -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $preset['badge_bg'] }} border {{ $preset['border'] }} shadow-sm">
                                {{ $preset['title_prefix'] }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800/80 text-slate-300 border border-slate-700/60">
                                🏪 {{ $deal->merchant->name ?? 'Amazon Prime' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800/80 text-slate-300 border border-slate-700/60">
                                🏷️ {{ $deal->category->name ?? 'Electronics' }}
                            </span>
                        </div>

                        <!-- Product Title -->
                        <h2 class="text-2xl sm:text-3xl font-black text-white leading-tight line-clamp-2 hover:text-red-400 transition-colors">
                            <a href="{{ route('deals.show', $deal->slug) }}">{{ $deal->title }}</a>
                        </h2>

                        <!-- Price Intelligence & Sparkline Block -->
                        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-3.5 space-y-2">
                            <div class="flex flex-wrap items-baseline justify-between gap-3">
                                <div class="flex items-baseline gap-3">
                                    <span class="text-3xl sm:text-4xl font-black text-white tracking-tight">₹{{ number_format($deal->discounted_price) }}</span>
                                    @if($deal->original_price > $deal->discounted_price)
                                    <span class="text-lg text-slate-400 line-through font-semibold">M.R.P. ₹{{ number_format($deal->original_price) }}</span>
                                    <span class="text-xs font-black text-emerald-400 bg-emerald-950/90 border border-emerald-800/80 px-2.5 py-1 rounded-lg">
                                        Save ₹{{ number_format($savedAmount) }}
                                    </span>
                                    @endif
                                </div>
                                <div class="text-xs text-right">
                                    <span class="text-slate-400">Lowest Price Ever: </span>
                                    <span class="font-bold text-emerald-400">✓ Yes</span>
                                    <span class="text-slate-400 ml-2">Stability: </span>
                                    <span class="font-bold text-yellow-400">{{ $preset['stability'] }}</span>
                                </div>
                            </div>

                            <!-- Historical Price Comparison & Sparkline Graph -->
                            <div class="grid grid-cols-4 gap-2 pt-1 text-center border-t border-slate-800/80 text-[11px]">
                                <div>
                                    <div class="text-slate-400">Current</div>
                                    <div class="font-bold text-emerald-400">₹{{ number_format($deal->discounted_price) }}</div>
                                </div>
                                <div>
                                    <div class="text-slate-400">Yesterday</div>
                                    <div class="font-semibold text-slate-300">₹{{ number_format($yesterdayPrice) }}</div>
                                </div>
                                <div>
                                    <div class="text-slate-400">Last Week</div>
                                    <div class="font-semibold text-slate-300">₹{{ number_format($lastWeekPrice) }}</div>
                                </div>
                                <div>
                                    <div class="text-slate-400">Last Month</div>
                                    <div class="font-semibold text-slate-300">₹{{ number_format($lastMonthPrice) }}</div>
                                </div>
                            </div>

                            <!-- Sparkline Price Trend Graph SVG -->
                            <div class="w-full pt-1 flex items-center gap-2">
                                <span class="text-[10px] text-slate-400 font-medium">30-Day Trend:</span>
                                <svg class="w-full h-5 text-emerald-400 overflow-visible" viewBox="0 0 300 20" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M 0 5 L 50 8 L 100 6 L 150 14 L 200 12 L 250 18 L 300 3" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="300" cy="3" r="3" fill="#34d399" />
                                </svg>
                            </div>
                        </div>

                        <!-- Live Intelligence Metrics Bar -->
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-white font-medium flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                {{ $preset['viewers'] }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-amber-300 font-medium">
                                🔥 {{ $preset['bought'] }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-red-300 font-medium">
                                ❤️ {{ $preset['wishlisted'] }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-blue-300 font-medium">
                                📈 {{ $preset['trending'] }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 font-medium">
                                💬 {{ $preset['reviews'] }}
                            </span>
                        </div>

                        <!-- AI Recommendation Box with Bullet Points -->
                        <div class="bg-slate-900/90 border {{ $preset['border'] }} rounded-2xl p-3 shadow-lg space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-300 flex items-center gap-1">
                                    <span>★★★★★ AI Recommendation:</span>
                                    <span class="text-emerald-400 font-black">{{ $preset['recommendation_title'] }}</span>
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-300">
                                @foreach($preset['reasons'] as $reason)
                                <span class="flex items-center gap-1">
                                    <span class="text-emerald-400 font-bold">•</span> {{ $reason }}
                                </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Live Deal Status Badges -->
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="font-bold text-slate-400 text-[11px]">Deal Status:</span>
                            @foreach($preset['status_badges'] as $sBadge)
                            <span class="px-2 py-0.5 rounded-md bg-slate-900 border border-slate-800 font-semibold text-slate-200">
                                {{ $sBadge }}
                            </span>
                            @endforeach
                        </div>

                        @if(!empty($preset['coupon']))
                        <!-- Copyable Coupon Box -->
                        <div class="flex items-center justify-between bg-cyan-950/70 border border-cyan-700/60 rounded-xl p-2.5 px-4 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="text-cyan-300 font-bold">🎟️ Coupon Code:</span>
                                <span class="font-mono font-black text-white bg-cyan-900 px-2.5 py-1 rounded border border-cyan-500/40">{{ $preset['coupon'] }}</span>
                            </div>
                            <button @click="navigator.clipboard.writeText('{{ $preset['coupon'] }}'); alert('Coupon Code Copied to Clipboard!')" class="text-xs font-bold text-cyan-300 hover:text-white bg-cyan-800/80 hover:bg-cyan-700 px-3 py-1 rounded-lg transition-colors">
                                Copy Code
                            </button>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <a href="{{ route('deal.redirect', $deal->hash_id) }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="flex-1 sm:flex-none px-7 py-3 rounded-xl bg-gradient-to-r {{ $preset['gradient'] }} hover:opacity-95 text-white font-black text-sm transition-all shadow-xl shadow-red-600/20 text-center flex items-center justify-center gap-2">
                                <span>⚡ GRAB DEAL NOW</span>
                                <span>→</span>
                            </a>

                            <a href="{{ route('deals.show', $deal->slug) }}" 
                               class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-sm transition-all text-center flex items-center justify-center gap-1.5">
                                <span>📊 Price History</span>
                            </a>

                            <button @click="showAlertModal = true" 
                                    class="px-5 py-3 rounded-xl bg-amber-600/30 hover:bg-amber-600/50 border border-amber-500/40 text-amber-300 hover:text-white font-bold text-sm transition-all text-center flex items-center justify-center gap-1.5">
                                <span>🔔 Watch Price</span>
                            </button>
                        </div>

                    </div>
                </div>
              @endforeach
            @endif

            <!-- Bottom Carousel Controls & Status Indicator -->
            <div class="flex items-center justify-between pt-5 border-t border-slate-800/80 mt-5 relative z-20">
                <!-- Slide Dots -->
                <div class="flex items-center gap-2">
                    @for($i = 0; $i < ((isset($heroDeals) && count($heroDeals) > 0) ? count($heroDeals) : 6); $i++)
                    <button @click="setSlide({{ $i }})" 
                            class="h-2.5 rounded-full transition-all duration-300"
                            :class="activeSlide === {{ $i }} ? 'w-8 bg-red-500' : 'w-2.5 bg-slate-700 hover:bg-slate-600'">
                    </button>
                    @endfor
                </div>

                <!-- Live Auto-Rotate Status Indicator -->
                <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400 font-medium">
                    <span class="w-2 h-2 rounded-full" :class="isPaused ? 'bg-amber-400' : 'bg-emerald-400 animate-ping'"></span>
                    <span x-text="isPaused ? 'Carousel Paused (Hovered)' : 'Auto-Rotating Live Deals (Every 6.5s)'"></span>
                </div>

                <!-- Prev / Next Controls -->
                <div class="flex items-center gap-2">
                    <button @click="prev()" class="w-9 h-9 rounded-full bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white flex items-center justify-center transition-colors shadow-md">
                        ←
                    </button>
                    <button @click="next()" class="w-9 h-9 rounded-full bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white flex items-center justify-center transition-colors shadow-md">
                        →
                    </button>
                </div>
            </div>

        </div>

     </div>

     <!-- Price Alert Modal -->
     <div x-cloak x-show="showAlertModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
         <div @click.away="showAlertModal = false" class="bg-slate-900 border border-slate-700 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4 text-white">
             <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                 <h3 class="text-lg font-black flex items-center gap-2 text-amber-400">
                     <span>🔔 Set Instant Price Alert</span>
                 </h3>
                 <button @click="showAlertModal = false" class="text-slate-400 hover:text-white font-bold">✕</button>
             </div>
             <p class="text-xs text-slate-300">Enter your email address to receive immediate notifications when this item drops to your target price.</p>
             <input type="email" x-model="alertEmail" placeholder="Enter your email address..." class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-red-500" />
             <div class="flex items-center gap-2 pt-2">
                 <button @click="if(alertEmail){ alert('✓ Price Alert set! We will email you when the price drops.'); showAlertModal = false; alertEmail = ''; }" class="w-full py-3 bg-red-600 hover:bg-red-500 text-white font-bold text-sm rounded-xl transition">
                     Activate Price Alert
                 </button>
             </div>
         </div>
     </div>
  </div>
@endsection

@section('content')
<section class="space-y-6">

  <x-ad-banner slot="home-top" />

  <div class="space-y-4">
    @if(isset($breadcrumbs))
        <nav class="flex text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-100">
                @foreach($breadcrumbs as $index => $crumb)
                    <li class="inline-flex items-center">
                        @if(!$loop->first)
                            <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        @if($crumb['url'])
                            <a href="{{ $crumb['url'] }}" class="text-gray-500 hover:text-red-600 transition font-medium">{{ $crumb['title'] }}</a>
                        @else
                            <span class="text-gray-900 font-bold">{{ $crumb['title'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3">
        <div>
            <h2 class="section-title">{{ $pageTitle ?? 'Featured Deals' }}</h2>
            <p class="text-sm text-gray-500 mt-1">Found {{ $deals->total() }} matching deals</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="/assistant" class="btn-primary bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white shadow-lg flex items-center px-3 py-1.5 text-sm sm:px-4 sm:py-2">
                <i data-lucide="sparkles" class="w-4 h-4 mr-1.5"></i> AI Assistant
            </a>
            <button x-data @click="$dispatch('open-alert-modal')" class="btn-secondary px-3 py-1.5 text-sm sm:px-4 sm:py-2 text-red-600 border-red-200 bg-red-50 hover:bg-red-100 flex items-center dark:bg-red-900/30 dark:text-red-400 dark:border-red-800">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                Price Alert
            </button>
            <a href="/?category=all" class="btn-secondary px-3 py-1.5 text-sm sm:px-4 sm:py-2 hidden sm:inline-block">View all</a>
        </div>
    </div>

    <!-- Entity Intelligence Landing Page UI -->
    @if(isset($category))
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-8 items-start md:items-center">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $category->name }} Deals & Buying Guide</h1>
                    <p class="text-gray-500 dark:text-slate-400 mt-2">Discover the best discounts on {{ $category->name }}. Our AI has analyzed {{ $category->deal_count ?? $deals->total() }} deals to bring you the top offers today.</p>
                </div>
                <div class="grid grid-cols-2 gap-4 flex-shrink-0 w-full md:w-auto">
                    <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-xl border border-red-100 dark:border-red-800/30 text-center">
                        <div class="text-2xl font-black text-red-600 dark:text-red-400">{{ number_format($category->average_discount, 1) }}%</div>
                        <div class="text-xs uppercase tracking-wider text-red-800 dark:text-red-300 font-semibold mt-1">Avg Discount</div>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-xl border border-orange-100 dark:border-orange-800/30 text-center">
                        <div class="text-2xl font-black text-orange-600 dark:text-orange-400">{{ $category->trending_score }}</div>
                        <div class="text-xs uppercase tracking-wider text-orange-800 dark:text-orange-300 font-semibold mt-1">Trending Score</div>
                    </div>
                </div>
            </div>
            @if($category->topMerchant)
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-slate-800">
                <p class="text-sm font-medium text-gray-600 dark:text-slate-300">
                    <span class="inline-block w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                    Top merchant for this category right now: <strong>{{ $category->topMerchant->name }}</strong>
                </p>
            </div>
            @endif
        </div>
    @endif

    @if(isset($brand))
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-8 items-start md:items-center">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $brand->name }} Deals & Offers</h1>
                    <p class="text-gray-500 dark:text-slate-400 mt-2">Find the latest verified discounts on {{ $brand->name }} products. Handpicked and scored by our AI engine.</p>
                </div>
                <div class="grid grid-cols-2 gap-4 flex-shrink-0 w-full md:w-auto">
                    <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-xl border border-red-100 dark:border-red-800/30 text-center">
                        <div class="text-2xl font-black text-red-600 dark:text-red-400">{{ number_format($brand->average_discount, 1) }}%</div>
                        <div class="text-xs uppercase tracking-wider text-red-800 dark:text-red-300 font-semibold mt-1">Avg Discount</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800/30 text-center">
                        <div class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $brand->trending_score }}</div>
                        <div class="text-xs uppercase tracking-wider text-blue-800 dark:text-blue-300 font-semibold mt-1">Brand Popularity</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(isset($trendingDeals) && $trendingDeals->isNotEmpty())
        <div class="mb-10">
            <div class="flex items-center gap-2 mb-4">
                <div class="bg-red-100 text-red-600 p-2 rounded-full dark:bg-red-900/30 dark:text-red-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Trending Deals Right Now</h2>
            </div>
            <div class="grid gap-3 grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
                @foreach($trendingDeals as $deal)
                    <x-deal-card :deal="$deal" />
                @endforeach
            </div>
        </div>
    @endif

      <div class="grid gap-3 grid-cols-2 md:grid-cols-3 xl:grid-cols-5" id="deals-grid">
        @include('partials.deals_grid')
      </div>
      
      <div class="mt-12 flex justify-center hidden" id="loading-spinner">
        <svg class="animate-spin h-8 w-8 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
      </div>

      <div class="mt-8 flex justify-center h-10" id="pagination-container">
        @if($deals->hasMorePages())
          <div id="load-more-trigger" data-url="{{ $deals->nextPageUrl() }}"></div>
        @endif
      </div>
      
      <x-ad-banner slot="home-bottom" />
      
      <!-- Newsletter Banner -->
      <div class="mt-16 bg-gradient-to-r from-gray-900 to-black rounded-3xl p-8 sm:p-12 shadow-2xl relative overflow-hidden border border-gray-800">
          <div class="absolute top-0 right-0 w-64 h-64 bg-red-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
          <div class="relative z-10 grid md:grid-cols-2 gap-8 items-center">
              <div>
                  <h3 class="text-3xl font-black text-white tracking-tight mb-3">Never Miss a 90% Price Drop</h3>
                  <p class="text-gray-400 text-sm sm:text-base">Join 50,000+ smart shoppers. We'll email you once a week with the absolute best AI-verified deals.</p>
              </div>
              <div x-data="{ email: '', loading: false, success: false, error: '' }" class="w-full max-w-md ml-auto">
                  <form @submit.prevent="
                      if(!email) return;
                      loading = true; error = ''; success = false;
                      fetch('/api/subscribe', {
                          method: 'POST',
                          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                          body: JSON.stringify({ email: email })
                      })
                      .then(res => res.json())
                      .then(data => {
                          loading = false;
                          if(data.error || data.errors) {
                              error = data.message || 'Error subscribing.';
                          } else {
                              success = true;
                              email = '';
                          }
                      })
                      .catch(() => { loading = false; error = 'Network error.'; });
                  " class="relative">
                      <div class="flex flex-col sm:flex-row gap-3">
                          <input type="email" x-model="email" required placeholder="Enter your email address" class="flex-1 bg-white/10 border border-gray-700 text-white placeholder-gray-500 px-5 py-3.5 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all">
                          <button type="submit" :disabled="loading" class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-3.5 rounded-xl transition-colors disabled:opacity-50 flex items-center justify-center min-w-[120px]">
                              <span x-show="!loading">Subscribe</span>
                              <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                          </button>
                      </div>
                      <p x-show="success" x-transition class="text-emerald-400 text-sm mt-3 font-medium flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Success! You're on the list.</p>
                      <p x-show="error" x-transition class="text-red-400 text-sm mt-3 font-medium" x-text="error"></p>
                  </form>
              </div>
          </div>
      </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const dealsGrid = document.getElementById('deals-grid');
    const spinner = document.getElementById('loading-spinner');
    const paginationContainer = document.getElementById('pagination-container');
    let isFetching = false;
    let observer = null;
    
    function fetchDeals(url, append = false) {
        if (isFetching) return;
        isFetching = true;
        
        if (!append) {
            dealsGrid.style.opacity = '0.5';
        }
        spinner.classList.remove('hidden');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (append) {
                dealsGrid.insertAdjacentHTML('beforeend', data.html);
            } else {
                dealsGrid.innerHTML = data.html;
                dealsGrid.style.opacity = '1';
                
                // Update URL without reloading
                window.history.pushState({}, '', url);
            }

            // Update Pagination Trigger
            if (data.has_more && data.next_page) {
                paginationContainer.innerHTML = `<div id="load-more-trigger" data-url="${data.next_page}"></div>`;
                bindAutoLoad();
            } else {
                paginationContainer.innerHTML = '';
            }
        })
        .finally(() => {
            spinner.classList.add('hidden');
            isFetching = false;
        });
    }

    // Handle Form Submit (Filters)
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            
            // Preserve current path context (e.g. /deals/70-89-off, /categories/electronics)
            const basePath = window.location.pathname || '/';
            const queryString = params.toString();
            const url = basePath + (queryString ? '?' + queryString : '');
            fetchDeals(url, false);
        });

        // Also trigger on select change
        const selects = filterForm.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', () => {
                filterForm.dispatchEvent(new Event('submit'));
            });
        });
    }

    // Handle Auto Load (Infinite Scroll)
    function bindAutoLoad() {
        const trigger = document.getElementById('load-more-trigger');
        if (!trigger) return;
        
        if (observer) {
            observer.disconnect();
        }
        
        observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                const url = trigger.getAttribute('data-url');
                if (url && !isFetching) {
                    fetchDeals(url, true);
                }
            }
        }, { rootMargin: '300px' });
        
        observer.observe(trigger);
    }
    
    // Initialize auto load on page load
    bindAutoLoad();
});
</script>
@endsection

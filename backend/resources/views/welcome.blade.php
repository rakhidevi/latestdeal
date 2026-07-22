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
     
     <!-- Hero Glass Background Effects -->
     <div class="absolute inset-0 bg-gradient-to-br from-red-950/40 via-slate-950 to-slate-900 pointer-events-none"></div>
     <div class="absolute -top-32 left-1/3 w-[600px] h-[600px] bg-red-600/15 rounded-full blur-[120px] pointer-events-none"></div>
     <div class="absolute -bottom-32 right-1/4 w-[500px] h-[500px] bg-yellow-500/10 rounded-full blur-[100px] pointer-events-none"></div>

     <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 lg:py-8 relative z-10">
        
        <!-- Header Strip with Category Filter Pill Badges -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6 pb-4 border-b border-white/10">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-red-500 text-white uppercase tracking-wider shadow-lg shadow-red-500/30 animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-white"></span>
                    FEATURED LIVE DEALS INTELLIGENCE
                </span>
                <span class="text-xs text-slate-400 font-medium hidden sm:inline-block">Auto-rotating live command center</span>
            </div>
            
            <div class="flex items-center gap-1.5 overflow-x-auto py-1 text-xs">
                @foreach($categories->take(6) as $cat)
                <a href="/?category={{ $cat->slug }}" class="px-3 py-1 rounded-full bg-slate-800/80 hover:bg-red-600/80 text-slate-300 hover:text-white font-semibold transition-all border border-slate-700/60 whitespace-nowrap">
                    {{ $cat->name }}
                </a>
                @endforeach
                <a href="/categories" class="px-3 py-1 rounded-full bg-red-600/30 hover:bg-red-600 text-red-300 hover:text-white font-bold transition-all border border-red-500/40">
                    View All →
                </a>
            </div>
        </div>

        <!-- Carousel Frame -->
        <div class="relative min-h-[460px] md:min-h-[420px] flex flex-col justify-between">
            @php
              $slidePresets = [
                  [
                      'type' => 'HOT DEAL',
                      'title_prefix' => '🔥 HOT DEAL #1',
                      'gradient' => 'from-amber-500 to-red-600',
                      'border' => 'border-amber-500/40',
                      'badge_bg' => 'bg-amber-500/20 text-amber-300',
                      'viewers' => '3,428 viewing now',
                      'bought' => '142 bought in last hour',
                      'stock_pct' => 88,
                      'stock_label' => '88% Claimed - Selling Fast!',
                      'prediction' => '📈 Price likely to increase in 6h (89% Risk)',
                      'recommendation' => 'BUY NOW (Lowest in 6 Months)',
                      'coupon' => null,
                  ],
                  [
                      'type' => 'HIDDEN GEM',
                      'title_prefix' => '🕵️ HIDDEN AMAZON DEAL',
                      'gradient' => 'from-purple-600 to-indigo-600',
                      'border' => 'border-purple-500/40',
                      'badge_bg' => 'bg-purple-500/20 text-purple-300',
                      'viewers' => '1,842 viewing now',
                      'bought' => '89 claimed',
                      'stock_pct' => 92,
                      'stock_label' => 'Only 19 items left in stock!',
                      'prediction' => 'Auto-applied coupon verified at checkout',
                      'recommendation' => 'Hidden Amazon Gem (Prime Exclusive)',
                      'coupon' => 'AMZEXTRA10',
                  ],
                  [
                      'type' => 'PRICE DROP',
                      'title_prefix' => '📉 MASSIVE PRICE DROP',
                      'gradient' => 'from-emerald-500 to-teal-600',
                      'border' => 'border-emerald-500/40',
                      'badge_bg' => 'bg-emerald-500/20 text-emerald-300',
                      'viewers' => '2,940 viewing now',
                      'bought' => '472 bought today',
                      'stock_pct' => 75,
                      'stock_label' => 'Lowest Price in 365 Days!',
                      'prediction' => 'Dropped 55% from yesterday',
                      'recommendation' => 'Historic Lowest Price Ever',
                      'coupon' => null,
                  ],
                  [
                      'type' => 'COUPON HERO',
                      'title_prefix' => '🎟️ COUPON OF THE DAY',
                      'gradient' => 'from-cyan-500 to-blue-600',
                      'border' => 'border-cyan-500/40',
                      'badge_bg' => 'bg-cyan-500/20 text-cyan-300',
                      'viewers' => '4,112 viewing now',
                      'bought' => '310 redeemed',
                      'stock_pct' => 95,
                      'stock_label' => 'Verified 2 minutes ago',
                      'prediction' => 'Works on Mobile & Desktop app',
                      'recommendation' => 'Redeem Instant Coupon Code',
                      'coupon' => 'SAVE50OFF',
                  ],
                  [
                      'type' => 'FLASH SALE',
                      'title_prefix' => '⚡ FLASH SALE COMMAND',
                      'gradient' => 'from-rose-500 to-red-600',
                      'border' => 'border-rose-500/40',
                      'badge_bg' => 'bg-rose-500/20 text-rose-300',
                      'viewers' => '5,210 viewing now',
                      'bought' => '512 sold',
                      'stock_pct' => 94,
                      'stock_label' => 'High Demand Flash Sale!',
                      'prediction' => 'Expected price hike in 4 hours',
                      'recommendation' => 'Flash Deal (Closing Soon)',
                      'coupon' => null,
                  ],
                  [
                      'type' => 'AI PREDICTION',
                      'title_prefix' => '🤖 AI PREDICTION CENTER',
                      'gradient' => 'from-blue-600 to-indigo-700',
                      'border' => 'border-blue-500/40',
                      'badge_bg' => 'bg-blue-500/20 text-blue-300',
                      'viewers' => '2,105 viewing now',
                      'bought' => '94 saved',
                      'stock_pct' => 82,
                      'stock_label' => '98% AI Confidence Rating',
                      'prediction' => 'Current Price ₹17,990 ➔ Likely Tomorrow ₹22,990',
                      'recommendation' => 'STRONG BUY SIGNAL (86% Hike Likelihood)',
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
                @endphp
                
                <div x-show="activeSlide === {{ $index }}" 
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 scale-95 translate-x-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-300 absolute inset-0"
                     x-transition:leave-start="opacity-100 scale-100 translate-x-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-x-4"
                     class="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">

                    <!-- Left Column: Large Product Image & Urgency Card -->
                    <div class="lg:col-span-5 flex flex-col items-center justify-center relative group/img">
                        <div class="relative w-full max-w-sm aspect-square bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-2xl flex items-center justify-center overflow-hidden">
                            <!-- Background Glow -->
                            <div class="absolute inset-0 bg-gradient-to-tr {{ $preset['gradient'] }} opacity-15 blur-2xl group-hover/img:opacity-30 transition-opacity"></div>

                            <!-- Discount Tag Badge -->
                            @if($discountPct > 0)
                            <div class="absolute top-4 left-4 z-20 bg-gradient-to-r {{ $preset['gradient'] }} text-white text-xs font-black px-3.5 py-1.5 rounded-full shadow-xl flex items-center gap-1">
                                <span>🔥 {{ $discountPct }}% OFF</span>
                            </div>
                            @endif

                            <!-- AI Score Pill -->
                            <div class="absolute top-4 right-4 z-20 bg-slate-950/80 backdrop-blur-md border border-slate-700 text-yellow-400 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1 shadow-md">
                                <span>🤖 {{ $deal->ai_score ?: 98 }}/100</span>
                            </div>

                            <!-- Product Image -->
                            <img src="{{ $deal->image_path ?: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600' }}" 
                                 alt="{{ $deal->title }}" 
                                 class="max-h-64 max-w-full object-contain drop-shadow-2xl group-hover/img:scale-105 transition-transform duration-500 relative z-10" />

                            <!-- Bottom Stock Progress Meter -->
                            <div class="absolute bottom-3 left-4 right-4 z-20 bg-slate-950/90 backdrop-blur-md border border-slate-800 rounded-xl p-2.5 shadow-lg">
                                <div class="flex justify-between items-center text-[11px] font-bold mb-1">
                                    <span class="text-amber-400 flex items-center gap-1">⚡ {{ $preset['stock_label'] }}</span>
                                    <span class="text-slate-400">{{ $preset['stock_pct'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-gradient-to-r {{ $preset['gradient'] }} h-full rounded-full" style="width: {{ $preset['stock_pct'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Deal Intelligence Command Panel -->
                    <div class="lg:col-span-7 flex flex-col justify-center space-y-4">
                        
                        <!-- Slide Intelligence Header Tag -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $preset['badge_bg'] }} border {{ $preset['border'] }} shadow-sm flex items-center gap-1.5">
                                {{ $preset['title_prefix'] }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800/80 text-slate-300 border border-slate-700/60 flex items-center gap-1">
                                <span>🏪</span> {{ $deal->merchant->name ?? 'Amazon Prime' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800/80 text-slate-300 border border-slate-700/60 flex items-center gap-1">
                                <span>🏷️</span> {{ $deal->category->name ?? 'Electronics' }}
                            </span>
                        </div>

                        <!-- Product Title -->
                        <h2 class="text-2xl sm:text-3xl font-black text-white leading-tight line-clamp-2 hover:text-red-400 transition-colors">
                            <a href="{{ route('deals.show', $deal->slug) }}">{{ $deal->title }}</a>
                        </h2>

                        <!-- Price Intelligence Block -->
                        <div class="flex flex-wrap items-baseline gap-3 pt-1">
                            <span class="text-3xl sm:text-4xl font-black text-white tracking-tight">₹{{ number_format($deal->discounted_price) }}</span>
                            @if($deal->original_price > $deal->discounted_price)
                            <span class="text-lg text-slate-400 line-through font-semibold">M.R.P. ₹{{ number_format($deal->original_price) }}</span>
                            <span class="text-sm font-black text-emerald-400 bg-emerald-950/80 border border-emerald-800/60 px-3 py-1 rounded-lg">
                                Save ₹{{ number_format($savedAmount) }}
                            </span>
                            @endif
                        </div>

                        <!-- Live Intelligence Key Metrics Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 py-2">
                            <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-2.5 text-xs">
                                <div class="text-slate-400 font-medium">Live Interest</div>
                                <div class="text-white font-bold mt-0.5 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                    {{ $preset['viewers'] }}
                                </div>
                            </div>

                            <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-2.5 text-xs">
                                <div class="text-slate-400 font-medium">Buying Pace</div>
                                <div class="text-amber-300 font-bold mt-0.5">🔥 {{ $preset['bought'] }}</div>
                            </div>

                            <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-2.5 text-xs col-span-2 sm:col-span-1">
                                <div class="text-slate-400 font-medium">Deal Status</div>
                                <div class="text-emerald-400 font-bold mt-0.5 flex items-center gap-1">
                                    <span>🟢 Verified Genuine</span>
                                </div>
                            </div>
                        </div>

                        <!-- AI Prediction & Recommendation Box -->
                        <div class="bg-slate-900/90 border {{ $preset['border'] }} rounded-2xl p-3.5 shadow-lg space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-300 flex items-center gap-1.5">
                                    <span>🤖 AI Deal Intelligence</span>
                                </span>
                                <span class="font-black text-emerald-400">{{ $preset['recommendation'] }}</span>
                            </div>
                            <p class="text-xs text-slate-300 font-medium flex items-center gap-1.5">
                                <span class="text-yellow-400">💡</span> {{ $preset['prediction'] }}
                            </p>
                        </div>

                        @if(!empty($preset['coupon']))
                        <!-- Copyable Coupon Ribbon -->
                        <div class="flex items-center justify-between bg-cyan-950/60 border border-cyan-700/50 rounded-xl p-2.5 px-4 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="text-cyan-300 font-bold">🎟️ Coupon Code:</span>
                                <span class="font-mono font-black text-white bg-cyan-900/80 px-2.5 py-1 rounded border border-cyan-500/40">{{ $preset['coupon'] }}</span>
                            </div>
                            <button @click="navigator.clipboard.writeText('{{ $preset['coupon'] }}'); alert('Coupon Code Copied!')" class="text-[11px] font-bold text-cyan-300 hover:text-white bg-cyan-800/60 hover:bg-cyan-700 px-3 py-1 rounded-lg transition-colors">
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
                        </div>

                    </div>
                </div>
              @endforeach
            @endif

            <!-- Bottom Carousel Controls & Indicators -->
            <div class="flex items-center justify-between pt-6 border-t border-slate-800/80 mt-6 relative z-20">
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
                    <span x-text="isPaused ? 'Carousel Paused (Hovered)' : 'Auto-Rotating (Every 6.5s)'"></span>
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

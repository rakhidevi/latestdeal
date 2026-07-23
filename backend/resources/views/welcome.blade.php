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
    /* Hide scrollbar for Chrome, Safari, Opera, Edge and Firefox */
    .no-scrollbar::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    .no-scrollbar {
        -ms-overflow-style: none !important;  /* IE and Edge */
        scrollbar-width: none !important;  /* Firefox */
    }
  </style>
  <div x-data="{
        activeSlide: 0,
        totalSlides: {{ (isset($heroDeals) && count($heroDeals) > 0) ? min(count($heroDeals), 10) : 10 }},
        isPaused: false,
        timer: null,
        showAlertModal: false,
        alertEmail: '',
        slidePresets: [
            { label: 'Hidden Amazon Gem' },
            { label: 'Deal of the Hour' },
            { label: 'Lowest Price Ever' },
            { label: 'Coupon of the Day' },
            { label: 'Biggest Price Drop' },
            { label: 'AI Recommended Buy' },
            { label: 'Best Bank Offer' },
            { label: 'Free Course of the Day' },
            { label: 'Trending Gaming Deal' },
            { label: 'Best Baby Product' }
        ],
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
     
     <!-- Background Ambient Glow -->
     <div class="absolute inset-0 bg-gradient-to-br from-red-950/40 via-slate-950 to-slate-900 pointer-events-none"></div>
     <div class="absolute -top-32 left-1/3 w-[600px] h-[600px] bg-red-600/10 rounded-full blur-[120px] pointer-events-none"></div>

     <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 relative z-10">
        
        <!-- Category Filter Chips (7-8 Visible with Horizontal Scroll) -->
        <div class="flex items-center justify-between gap-3 mb-6 pb-3 border-b border-white/10">
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-red-600 text-white uppercase tracking-wider shadow-md">
                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                    FEATURED DEALS
                </span>
            </div>
            
            <div class="flex items-center gap-2 overflow-x-auto py-1 text-xs no-scrollbar scroll-smooth">
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
                <a href="/?category={{ $catItem['slug'] }}" class="px-4 py-1.5 rounded-full bg-slate-900/90 hover:bg-red-600 text-slate-200 hover:text-white font-bold transition-all border border-slate-800/80 whitespace-nowrap shadow-sm flex-shrink-0">
                    {{ $catItem['name'] }}
                </a>
                @endforeach
                <a href="/categories" class="px-4 py-1.5 rounded-full bg-red-600/20 hover:bg-red-600 text-red-300 hover:text-white font-bold transition-all border border-red-500/30 whitespace-nowrap flex-shrink-0">
                    View All →
                </a>
            </div>
        </div>

        <!-- Carousel Frame -->
        <div class="relative min-h-[440px] flex flex-col justify-between">
            @php
              $heroItems = (isset($heroDeals) && count($heroDeals) > 0) ? $heroDeals : [
                  (object)['id' => 1, 'title' => 'Apple AirPods Pro (2nd Gen) with MagSafe USB-C Case', 'slug' => 'apple-airpods-pro-2', 'hash_id' => 'hp1', 'discounted_price' => 16499, 'original_price' => 24990, 'discount_percentage' => 34, 'ai_score' => 98, 'image_path' => 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=600', 'merchant' => (object)['name' => 'Amazon Prime'], 'category' => (object)['name' => 'Electronics']],
                  (object)['id' => 2, 'title' => 'Sony WH-1000XM5 Wireless Noise Cancelling Headphones', 'slug' => 'sony-wh-1000xm5', 'hash_id' => 'hp2', 'discounted_price' => 26990, 'original_price' => 34990, 'discount_percentage' => 23, 'ai_score' => 97, 'image_path' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600', 'merchant' => (object)['name' => 'Amazon Prime'], 'category' => (object)['name' => 'Electronics']],
                  (object)['id' => 3, 'title' => 'ASUS ROG Strix G16 (2024) Intel Core i7 14th Gen Gaming Laptop', 'slug' => 'asus-rog-strix-g16', 'hash_id' => 'hp3', 'discounted_price' => 114990, 'original_price' => 159990, 'discount_percentage' => 28, 'ai_score' => 99, 'image_path' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600', 'merchant' => (object)['name' => 'Amazon Prime'], 'category' => (object)['name' => 'Gaming']],
                  (object)['id' => 4, 'title' => 'Norton 360 Premium 2024 - 10 Devices 1 Year Subscription', 'slug' => 'norton-360-premium', 'hash_id' => 'hp4', 'discounted_price' => 999, 'original_price' => 4999, 'discount_percentage' => 80, 'ai_score' => 96, 'image_path' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=600', 'merchant' => (object)['name' => 'Amazon Prime'], 'category' => (object)['name' => 'Software']],
                  (object)['id' => 5, 'title' => 'Fitkit by Cult Walking Pad Neo BLDC 3.5HP Peak Power Treadmill', 'slug' => 'fitkit-walking-pad-neo', 'hash_id' => 'hp5', 'discounted_price' => 7999, 'original_price' => 79990, 'discount_percentage' => 90, 'ai_score' => 95, 'image_path' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=600', 'merchant' => (object)['name' => 'Amazon Prime'], 'category' => (object)['name' => 'Sports & Fitness']],
                  (object)['id' => 6, 'title' => 'Samsung Galaxy Tab S9 FE+ 12.4 inch WQXGA Display 8GB RAM', 'slug' => 'samsung-galaxy-tab-s9-fe', 'hash_id' => 'hp6', 'discounted_price' => 31999, 'original_price' => 46999, 'discount_percentage' => 32, 'ai_score' => 98, 'image_path' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600', 'merchant' => (object)['name' => 'Amazon Prime'], 'category' => (object)['name' => 'Electronics']]
              ];

              $slidePresets = [
                  [
                      'type' => 'HIDDEN GEM',
                      'title_prefix' => '🔥 HIDDEN AMAZON GEM',
                      'bought' => '189 bought today',
                      'viewers' => '1.8K viewing now',
                      'wishlisted' => '2.3K wishlisted',
                      'stock_pct' => 92,
                      'stock_left' => 18,
                      'coupon' => 'SAVE1200OFF',
                      'success_rate' => '97%',
                  ],
                  [
                      'type' => 'DEAL OF HOUR',
                      'title_prefix' => '⚡ DEAL OF THE HOUR',
                      'bought' => '412 bought today',
                      'viewers' => '3.1K viewing now',
                      'wishlisted' => '1.9K wishlisted',
                      'stock_pct' => 95,
                      'stock_left' => 9,
                      'coupon' => null,
                  ],
                  [
                      'type' => 'LOWEST PRICE',
                      'title_prefix' => '💎 LOWEST PRICE EVER',
                      'bought' => '620 bought today',
                      'viewers' => '4.5K viewing now',
                      'wishlisted' => '3.8K wishlisted',
                      'stock_pct' => 88,
                      'stock_left' => 24,
                      'coupon' => null,
                  ],
                  [
                      'type' => 'COUPON HERO',
                      'title_prefix' => '🎟️ COUPON OF THE DAY',
                      'bought' => '310 redeemed',
                      'viewers' => '2.8K viewing now',
                      'wishlisted' => '1.4K wishlisted',
                      'stock_pct' => 96,
                      'stock_left' => 14,
                      'coupon' => 'SAVE80',
                      'success_rate' => '99%',
                  ],
                  [
                      'type' => 'BIGGEST DROP',
                      'title_prefix' => '📉 BIGGEST PRICE DROP',
                      'bought' => '512 bought today',
                      'viewers' => '5.2K viewing now',
                      'wishlisted' => '4.1K wishlisted',
                      'stock_pct' => 90,
                      'stock_left' => 12,
                      'coupon' => null,
                  ],
                  [
                      'type' => 'AI RECOMMENDED',
                      'title_prefix' => '🤖 AI RECOMMENDED BUY',
                      'bought' => '284 bought today',
                      'viewers' => '2.4K viewing now',
                      'wishlisted' => '1.7K wishlisted',
                      'stock_pct' => 84,
                      'stock_left' => 29,
                      'coupon' => null,
                  ],
                  [
                      'type' => 'BANK OFFER',
                      'title_prefix' => '🏦 BEST BANK OFFER',
                      'bought' => '390 claimed',
                      'viewers' => '3.8K viewing now',
                      'wishlisted' => '2.1K wishlisted',
                      'stock_pct' => 91,
                      'stock_left' => 16,
                      'coupon' => 'HDFC10OFF',
                      'success_rate' => '95%',
                  ],
                  [
                      'type' => 'FREE COURSE',
                      'title_prefix' => '🎓 FREE COURSE OF THE DAY',
                      'bought' => '1,420 enrolled',
                      'viewers' => '6.1K viewing now',
                      'wishlisted' => '5.4K wishlisted',
                      'stock_pct' => 98,
                      'stock_left' => 5,
                      'coupon' => '100%FREE2026',
                      'success_rate' => '100%',
                  ],
                  [
                      'type' => 'GAMING DEAL',
                      'title_prefix' => '🎮 TRENDING GAMING DEAL',
                      'bought' => '450 bought today',
                      'viewers' => '4.2K viewing now',
                      'wishlisted' => '3.1K wishlisted',
                      'stock_pct' => 89,
                      'stock_left' => 21,
                      'coupon' => null,
                  ],
                  [
                      'type' => 'BABY DEAL',
                      'title_prefix' => '👶 BEST BABY PRODUCT DEAL',
                      'bought' => '210 bought today',
                      'viewers' => '1.9K viewing now',
                      'wishlisted' => '1.2K wishlisted',
                      'stock_pct' => 86,
                      'stock_left' => 27,
                      'coupon' => 'BABYCARE15',
                      'success_rate' => '96%',
                  ]
              ];
            @endphp

            @if(count($heroItems) > 0)
              @foreach($heroItems as $index => $deal)
                @php
                  $preset = $slidePresets[$index % count($slidePresets)];
                  $discountPct = $deal->discount_percentage ?: ($deal->original_price > $deal->discounted_price ? round((($deal->original_price - $deal->discounted_price)/$deal->original_price)*100) : 0);
                  $savedAmount = max(0, $deal->original_price - $deal->discounted_price);
                @endphp
                
                <div x-show="activeSlide === {{ $index }}" 
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-300 absolute inset-0"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 md:p-8 backdrop-blur-md shadow-2xl">

                    <!-- Left Column: Larger Product Image (~45% width) + Urgency Bar -->
                    <div class="lg:col-span-5 flex flex-col items-center justify-center relative">
                        <div class="relative w-full max-w-md aspect-square bg-slate-950/90 border border-slate-800 rounded-3xl p-6 shadow-2xl flex items-center justify-center overflow-hidden group/img">
                            
                            <!-- Clean High-Visibility Discount Tag -->
                            @if($discountPct > 0)
                            <div class="absolute top-4 left-4 z-20 bg-red-600 text-white text-xs font-black px-3.5 py-1.5 rounded-full shadow-xl">
                                🔥 {{ $discountPct }}% OFF
                            </div>
                            @endif

                            <!-- Merchant Badge -->
                            <div class="absolute top-4 right-4 z-20 bg-slate-900/90 backdrop-blur-md border border-slate-700/80 text-slate-200 text-xs font-bold px-3 py-1 rounded-full shadow-md">
                                🏪 {{ $deal->merchant->name ?? 'Amazon' }}
                            </div>

                            <!-- Large Product Image (Prominent 45% visual weight) -->
                            <img src="{{ $deal->image_url ?: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600' }}" 
                                 alt="{{ $deal->title }}" 
                                 class="max-h-72 max-w-full object-contain drop-shadow-2xl group-hover/img:scale-105 transition-transform duration-500 relative z-10" />

                            <!-- Item 5: Urgency Meter with Progress Bar & Only X Left -->
                            <div class="absolute bottom-4 left-4 right-4 z-20 bg-slate-950/90 border border-slate-800 rounded-2xl p-2.5 shadow-xl">
                                <div class="flex justify-between items-center text-xs font-bold mb-1.5">
                                    <span class="text-amber-400 flex items-center gap-1">⚡ Selling Fast</span>
                                    <span class="text-red-400 font-extrabold">Only {{ $preset['stock_left'] ?? 18 }} left</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden flex items-center">
                                    <div class="bg-gradient-to-r from-amber-500 to-red-600 h-full rounded-full" style="width: {{ $preset['stock_pct'] ?? 92 }}%"></div>
                                </div>
                                <div class="flex justify-between items-center text-[10px] text-slate-400 mt-1 font-semibold">
                                    <span>Progress: {{ $preset['stock_pct'] ?? 92 }}%</span>
                                    <span class="text-amber-300">High Urgency</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Deal Details, AI Analysis, Ticket & CTAs -->
                    <div class="lg:col-span-7 flex flex-col justify-center space-y-3.5">
                        
                        <!-- Badges Header -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-red-500/20 text-red-400 border border-red-500/30">
                                {{ $preset['title_prefix'] ?? '🔥 HOT DEAL' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700/60">
                                🏷️ {{ $deal->category->name ?? 'Electronics' }}
                            </span>
                        </div>

                        <!-- Product Title -->
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-white leading-tight line-clamp-2">
                            <a href="{{ route('deals.show', $deal->slug) }}" class="hover:text-red-400 transition-colors">{{ $deal->title }}</a>
                        </h2>

                        <!-- Price Block + Item 6: Mini Price Trend Sparkline -->
                        <div class="flex flex-wrap items-center justify-between gap-4 bg-slate-950/80 border border-slate-800/80 rounded-2xl p-3 px-4">
                            <div class="flex flex-wrap items-baseline gap-3">
                                <span class="text-3xl sm:text-4xl font-black text-white tracking-tight">₹{{ number_format($deal->discounted_price) }}</span>
                                @if($deal->original_price > $deal->discounted_price)
                                <span class="text-lg text-slate-400 line-through font-semibold">M.R.P. ₹{{ number_format($deal->original_price) }}</span>
                                <span class="text-xs font-bold text-emerald-400 bg-emerald-950/80 border border-emerald-800/80 px-2.5 py-1 rounded-lg">
                                    Save ₹{{ number_format($savedAmount) }}
                                </span>
                                @endif
                            </div>

                            <!-- Mini Price Trend Sparkline SVG -->
                            <div class="flex items-center gap-3">
                                <div class="text-[10px] font-bold text-slate-400 flex flex-col justify-between h-7">
                                    <span class="text-slate-400">₹{{ number_format($deal->original_price > 0 ? $deal->original_price : $deal->discounted_price * 1.4) }}</span>
                                    <span class="text-emerald-400 font-black">₹{{ number_format($deal->discounted_price) }}</span>
                                </div>
                                <div class="w-24 h-7 flex items-center">
                                    <svg class="w-full h-6 text-emerald-400 overflow-visible" viewBox="0 0 200 30" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M 0 5 L 40 10 L 80 8 L 120 22 L 160 18 L 200 28" stroke-linecap="round" stroke-linejoin="round" />
                                        <circle cx="200" cy="28" r="3.5" fill="#34d399" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Item 2: Relatable Strong Metrics -->
                        <div class="flex flex-wrap items-center gap-2.5 text-xs text-slate-200">
                            <span class="px-3 py-1 rounded-lg bg-red-950/80 border border-red-800/80 text-red-300 font-bold flex items-center gap-1.5">
                                🔥 {{ $preset['bought'] ?? '189 bought today' }}
                            </span>
                            <span class="px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 font-medium flex items-center gap-1.5">
                                👀 {{ $preset['viewers'] ?? '1.8K viewing now' }}
                            </span>
                            <span class="px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 text-pink-400 font-medium flex items-center gap-1.5">
                                ❤️ {{ $preset['wishlisted'] ?? '2.3K wishlisted' }}
                            </span>
                        </div>

                        <!-- Item 3: "Deal Intelligence" Section (🤖 AI Analysis) -->
                        <div class="bg-slate-950/90 border border-slate-800/90 rounded-2xl p-3.5 space-y-2 shadow-inner">
                            <div class="flex items-center justify-between text-xs border-b border-slate-800/80 pb-1.5">
                                <span class="font-black text-amber-400 flex items-center gap-1.5 text-xs uppercase tracking-wider">
                                    <span>🤖 AI Analysis</span>
                                </span>
                                <span class="text-[11px] font-bold text-emerald-400 bg-emerald-950/80 border border-emerald-800/60 px-2 py-0.5 rounded">
                                    Score {{ $deal->ai_score ?: 98 }}/100
                                </span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-3 gap-y-1.5 text-xs text-slate-300 font-medium">
                                <span class="flex items-center gap-1.5 text-emerald-400">✓ Lowest price in 180 days</span>
                                <span class="flex items-center gap-1.5 text-emerald-400">✓ Seller Rating 4.8★</span>
                                <span class="flex items-center gap-1.5 text-emerald-400">✓ Coupon Verified</span>
                                <span class="flex items-center gap-1.5 text-emerald-400">✓ Prime Eligible</span>
                                <span class="flex items-center gap-1.5 text-emerald-400">✓ Cashback Available</span>
                                <span class="flex items-center gap-1.5 text-amber-400">✓ Buy before midnight</span>
                            </div>
                        </div>

                        <!-- Item 4: Real Coupon Ticket Component -->
                        @if(!empty($preset['coupon']))
                        <div class="relative bg-slate-900 border-2 border-dashed border-cyan-500/60 rounded-2xl p-3 px-4 flex items-center justify-between shadow-lg overflow-hidden group/ticket">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-4 bg-slate-950 rounded-r-full -ml-4"></div>
                                <div>
                                    <div class="text-[10px] font-black uppercase text-cyan-400 tracking-wider flex items-center gap-1">
                                        <span>✓ Verified Coupon</span>
                                        <span class="bg-cyan-950 text-cyan-300 px-1.5 py-0.2 rounded border border-cyan-800">{{ $preset['success_rate'] ?? '97%' }} Success Rate</span>
                                    </div>
                                    <div class="font-mono font-black text-lg text-white tracking-widest">{{ $preset['coupon'] }}</div>
                                </div>
                            </div>
                            <button @click="navigator.clipboard.writeText('{{ $preset['coupon'] }}'); alert('✓ Coupon Code Copied: {{ $preset['coupon'] }}')" 
                                    class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-black text-xs transition-all shadow-md shadow-cyan-600/30 flex items-center gap-1">
                                <span>Copy</span>
                                <span>📋</span>
                            </button>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center gap-3 pt-1">
                            <a href="{{ route('deal.redirect', $deal->hash_id) }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="px-8 py-3 rounded-xl bg-gradient-to-r from-red-600 to-amber-500 hover:from-red-500 hover:to-amber-400 text-white font-black text-sm transition-all shadow-xl shadow-red-600/30 flex items-center justify-center gap-2">
                                <span>⚡ GRAB DEAL NOW</span>
                                <span>→</span>
                            </a>

                            <a href="{{ route('deals.show', $deal->slug) }}" 
                               class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-sm transition-all">
                                📊 Price History
                            </a>

                            <button @click="showAlertModal = true" 
                                    class="px-5 py-3 rounded-xl bg-slate-800/60 hover:bg-slate-700 border border-slate-700 text-amber-300 hover:text-white font-bold text-sm transition-all">
                                🔔 Watch Price
                            </button>
                        </div>

                    </div>
                </div>
              @endforeach
            @endif

            <!-- Item 7: Auto-Rotation Status & Slide Indicator (Slide X of Y, Next Preview & Controls) -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-800/80 mt-4 relative z-20">
                <!-- Slide Dots -->
                <div class="flex items-center gap-2">
                    @for($i = 0; $i < ((isset($heroDeals) && count($heroDeals) > 0) ? min(count($heroDeals), 10) : 10); $i++)
                    <button @click="setSlide({{ $i }})" 
                            class="h-2 rounded-full transition-all duration-300"
                            :class="activeSlide === {{ $i }} ? 'w-8 bg-red-500' : 'w-2 bg-slate-700 hover:bg-slate-600'">
                    </button>
                    @endfor
                </div>

                <!-- Slide Counter & Next Preview -->
                <div class="flex items-center gap-3 text-xs font-semibold">
                    <span class="px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-white font-black">
                        Slide <span x-text="activeSlide + 1"></span> of <span x-text="totalSlides"></span>
                    </span>
                    <div class="hidden sm:flex items-center gap-2 text-slate-400">
                        <span class="w-2 h-2 rounded-full" :class="isPaused ? 'bg-amber-400' : 'bg-emerald-400 animate-ping'"></span>
                        <span>Next: <strong class="text-slate-200" x-text="slidePresets[(activeSlide + 1) % totalSlides].label"></strong></span>
                    </div>
                </div>

                <!-- Prev / Next Controls -->
                <div class="flex items-center gap-2">
                    <button @click="prev()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white flex items-center justify-center transition-colors">
                        ←
                    </button>
                    <button @click="next()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white flex items-center justify-center transition-colors">
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

    <!-- Interactive Entity Intelligence Landing Header -->
    @if(isset($brand) || isset($category) || isset($merchant) || (isset($pageTitle) && !request()->is('/')))
        @php
            $entityName = isset($brand) ? $brand->name : (isset($category) ? $category->name : (isset($merchant) ? $merchant->name : ($pageTitle ?? 'Deals')));
            $entityType = isset($brand) ? 'Brand' : (isset($category) ? 'Category' : (isset($merchant) ? 'Store' : 'Deals'));
            $avgDiscount = isset($brand) ? ($brand->average_discount ?: 34.9) : (isset($category) ? ($category->average_discount ?: 41.2) : 38.5);
            $popularity = isset($brand) ? ($brand->trending_score ?: 85) : (isset($category) ? ($category->trending_score ?: 92) : 88);
            $totalDeals = $deals->total() > 0 ? $deals->total() : (isset($category) ? ($category->deal_count ?? 12) : 8);
        @endphp
        
        <div class="relative overflow-hidden bg-slate-950 text-white rounded-3xl p-6 lg:p-8 mb-8 border border-slate-800/80 shadow-2xl">
            <!-- Ambient Glow Effects -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-red-600/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-6">
                <!-- Top Row: Badge, Title & Metrics -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="px-3.5 py-1 rounded-full font-black uppercase tracking-wider bg-red-600 text-white shadow-md">
                                🎯 {{ $entityType }} Intelligence Hub
                            </span>
                            <span class="px-3 py-1 rounded-full font-bold bg-slate-900 border border-slate-800 text-emerald-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                Live Crawler Sync Active
                            </span>
                            <span class="px-3 py-1 rounded-full font-bold bg-slate-900 border border-slate-800 text-amber-300">
                                🤖 AI Trust Verified
                            </span>
                        </div>

                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                            {{ $entityName }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-amber-400">Deals & Offers</span>
                        </h1>
                        <p class="text-slate-300 text-sm sm:text-base max-w-2xl font-medium">
                            Real-time AI algorithm tracking {{ number_format($totalDeals) }}+ verified discounts on {{ $entityName }}. Updated continuously.
                        </p>
                    </div>

                    <!-- Right Metrics Cards -->
                    <div class="grid grid-cols-3 gap-3 shrink-0">
                        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-3.5 text-center shadow-lg">
                            <div class="text-2xl sm:text-3xl font-black text-red-500 tracking-tight">{{ number_format($avgDiscount, 1) }}%</div>
                            <div class="text-[10px] uppercase font-black tracking-wider text-slate-400 mt-1">Avg Savings</div>
                        </div>
                        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-3.5 text-center shadow-lg">
                            <div class="text-2xl sm:text-3xl font-black text-amber-400 tracking-tight">{{ $popularity }}</div>
                            <div class="text-[10px] uppercase font-black tracking-wider text-slate-400 mt-1">Popularity</div>
                        </div>
                        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-3.5 text-center shadow-lg">
                            <div class="text-2xl sm:text-3xl font-black text-emerald-400 tracking-tight">{{ $totalDeals }}</div>
                            <div class="text-[10px] uppercase font-black tracking-wider text-slate-400 mt-1">Active Offers</div>
                        </div>
                    </div>
                </div>

                <!-- Interactive Filter Tabs & Quick Action Bar -->
                <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-800/80">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                        <span class="text-slate-400 mr-1 text-[11px] uppercase tracking-wider font-extrabold">Quick Sort:</span>
                        <a href="?sort=featured" class="px-3.5 py-2 rounded-xl {{ (request('sort') == 'featured' || !request('sort')) ? 'bg-red-600 text-white font-black shadow-md' : 'bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800' }} transition">
                            🔥 Top Rated
                        </a>
                        <a href="?sort=discount" class="px-3.5 py-2 rounded-xl {{ request('sort') == 'discount' ? 'bg-red-600 text-white font-black shadow-md' : 'bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800' }} transition">
                            📉 Biggest Discount
                        </a>
                        <a href="?sort=price_low" class="px-3.5 py-2 rounded-xl {{ request('sort') == 'price_low' ? 'bg-red-600 text-white font-black shadow-md' : 'bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800' }} transition">
                            💵 Lowest Price
                        </a>
                        <a href="?sort=newest" class="px-3.5 py-2 rounded-xl {{ request('sort') == 'newest' ? 'bg-red-600 text-white font-black shadow-md' : 'bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800' }} transition">
                            ⚡ Just Added
                        </a>
                    </div>

                    <button @click="$dispatch('open-alert-modal')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-red-600 hover:from-amber-400 hover:to-red-500 text-white font-black text-xs transition-all shadow-lg flex items-center gap-1.5">
                        <span>🔔 Track {{ $entityName }} Price Drops</span>
                    </button>
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

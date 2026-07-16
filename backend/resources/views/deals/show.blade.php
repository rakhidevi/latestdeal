@extends('layouts.app')

@section('meta')
    @php
        $discountPercent = 0;
        if ($deal->original_price > 0 && $deal->original_price > $deal->discounted_price) {
            $discountPercent = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
        }
    @endphp
    <title>{{ $deal->title }} | {{ $discountPercent > 0 ? "Save {$discountPercent}%" : 'Best Price' }} | LatestDeal.in</title>
    <meta name="description" content="Get {{ $deal->title }} for just ₹{{ $deal->discounted_price }}. Original price: ₹{{ $deal->original_price }}.">
    
    <!-- Open Graph for WhatsApp/Telegram Previews -->
    <meta property="og:title" content="{{ $deal->title }} | Save {{ $discountPercent }}%">
    <meta property="og:description" content="Get it for just ₹{{ $deal->discounted_price }}! Regular Price: ₹{{ $deal->original_price }}.">
    <meta property="og:image" content="{{ filter_var($deal->image_path, FILTER_VALIDATE_URL) ? $deal->image_path : asset($deal->image_path) }}">
    <meta property="og:url" content="{{ route('deal.show', $deal->slug) }}">
    <meta property="og:type" content="product">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $deal->title }} | Save {{ $discountPercent }}%">
    <meta name="twitter:description" content="Get it for just ₹{{ $deal->discounted_price }}!">
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
<!-- Ambient Background Glow -->
<div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none dark:hidden">
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-red-400 rounded-full mix-blend-multiply filter blur-[100px] opacity-20"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-400 rounded-full mix-blend-multiply filter blur-[100px] opacity-20"></div>
</div>

<div class="max-w-6xl mx-auto surface rounded-3xl p-6 sm:p-10 mb-12 relative overflow-hidden mt-6 shadow-2xl shadow-gray-200/50 dark:shadow-none border border-slate-100 dark:border-slate-800 bg-white/70 backdrop-blur-xl dark:bg-slate-900/80">
    <!-- Decorative top gradient -->
    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-red-500 via-rose-400 to-red-500"></div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Left Column: Image & AI Verdict -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            <div class="relative group rounded-3xl overflow-hidden bg-slate-50 dark:bg-slate-800/50 p-6 flex items-center justify-center border border-slate-100 dark:border-slate-800 min-h-[300px] md:min-h-[400px]">
                <img src="{{ filter_var($deal->image_path, FILTER_VALIDATE_URL) ? $deal->image_path : asset($deal->image_path) }}" alt="{{ Str::limit($deal->title, 50) }}" class="w-full max-w-sm object-contain drop-shadow-xl transition-transform duration-700 group-hover:scale-105 mix-blend-multiply dark:mix-blend-normal" style="max-height: 400px;" onerror="this.style.display='none';">
                @if($deal->original_price > 0 && $deal->original_price > $deal->discounted_price)
                    <div class="absolute top-4 left-4 bg-red-500 text-white text-sm font-black px-3 py-1.5 rounded-full shadow-lg transform -rotate-2 border border-red-400">
                        🔥 {{ round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) }}% OFF
                    </div>
                @endif
            </div>

            @if($deal->verdict)
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950/40 dark:to-purple-950/40 rounded-2xl p-5 border border-indigo-100 dark:border-indigo-900/50 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 text-indigo-200 dark:text-indigo-900/30 opacity-50">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    </div>
                    <div class="relative z-10">
                        <h3 class="text-xs font-black text-indigo-700 dark:text-indigo-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <span class="text-base">❤️</span> LatestDeal Pick
                        </h3>
                        <p class="text-slate-700 dark:text-slate-300 font-medium leading-relaxed text-sm">{{ $deal->verdict }}</p>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Right Column: Details & Actions -->
        <div class="lg:col-span-7 flex flex-col justify-center">
            
            <div class="flex flex-wrap gap-2 mb-4">
                @if($deal->trust_metrics)
                    <span class="inline-flex items-center gap-1.5 bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 px-3 py-1 rounded-full text-xs font-bold border border-amber-200 dark:border-amber-500/20 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        {{ $deal->trust_metrics }}
                    </span>
                @endif
                @if($deal->brand)
                    <span class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-3 py-1 rounded-full text-xs font-bold border border-slate-200 dark:border-slate-700 shadow-sm">
                        {{ $deal->brand }}
                    </span>
                @endif
            </div>

            <div class="flex justify-between items-start gap-4">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white leading-tight">{{ $deal->title }}</h1>
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
                <p class="mt-4 text-slate-600 dark:text-slate-400 leading-relaxed text-sm">
                    {{ $deal->description }}
                </p>
            @endif
            
            <div x-data="priceUpdater({{ $deal->id }}, {{ $deal->discounted_price }})"
                 x-init="listenForUpdates"
                 class="mt-6 flex flex-col sm:flex-row flex-wrap items-baseline gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 w-fit">
                
                <div class="flex items-baseline gap-3">
                    <span class="text-4xl sm:text-5xl font-black text-red-600 dark:text-red-500 tracking-tight" x-text="'₹' + new Intl.NumberFormat('en-IN').format(currentPrice)">
                        ₹{{ number_format($deal->discounted_price) }}
                    </span>
                    @if($deal->original_price > 0 && $deal->original_price > $deal->discounted_price)
                        <span class="text-lg text-slate-400 dark:text-slate-500 line-through font-medium">M.R.P: ₹{{ number_format($deal->original_price) }}</span>
                    @endif
                </div>

                <!-- Verify Live Price Button -->
                <button @click="verifyPrice" 
                        :disabled="isChecking"
                        class="ml-0 sm:ml-4 text-xs font-bold text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-full hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors flex items-center gap-1.5 disabled:opacity-50">
                    <svg x-show="isChecking" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <svg x-show="!isChecking" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span x-text="isChecking ? 'Checking Amazon...' : 'Verify Live Price'"></span>
                </button>
            </div>

            <!-- AI Pros & Cons / Features -->
            @if($deal->features && count($deal->features) > 0)
                @if(isset($deal->features['pros']) || isset($deal->features['cons']))
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(isset($deal->features['pros']) && count($deal->features['pros']) > 0)
                        <div class="bg-green-50 dark:bg-green-900/10 rounded-2xl p-5 border border-green-100 dark:border-green-900/30">
                            <h3 class="text-green-800 dark:text-green-400 font-bold mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Pros
                            </h3>
                            <ul class="space-y-2">
                                @foreach($deal->features['pros'] as $pro)
                                    <li class="text-sm text-slate-700 dark:text-slate-300 flex items-start gap-2">
                                        <span class="text-green-500 mt-1">•</span> {{ $pro }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        @if(isset($deal->features['cons']) && count($deal->features['cons']) > 0)
                        <div class="bg-red-50 dark:bg-red-900/10 rounded-2xl p-5 border border-red-100 dark:border-red-900/30">
                            <h3 class="text-red-800 dark:text-red-400 font-bold mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Cons
                            </h3>
                            <ul class="space-y-2">
                                @foreach($deal->features['cons'] as $con)
                                    <li class="text-sm text-slate-700 dark:text-slate-300 flex items-start gap-2">
                                        <span class="text-red-500 mt-1">•</span> {{ $con }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                @else
                    <!-- Legacy features list -->
                    <div class="mt-8 space-y-3">
                        @foreach($deal->features as $feature)
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 shrink-0 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full p-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                                <span class="text-slate-700 dark:text-slate-300 text-sm leading-relaxed">{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            <!-- Copy Code & Go -->
            <div class="mt-8">
                @if($deal->promo_code || $deal->coupon_code)
                    <div class="bg-slate-900 dark:bg-black rounded-2xl p-2 pl-6 flex flex-col sm:flex-row sm:items-center justify-between shadow-xl gap-4">
                        <div class="pt-2 sm:pt-0">
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Use Code at Checkout</p>
                            <p class="text-2xl font-mono font-bold text-white tracking-wider" id="promo-code">{{ $deal->promo_code ?? $deal->coupon_code }}</p>
                        </div>
                        <button onclick="copyAndGo()" class="w-full sm:w-auto bg-gradient-to-r from-red-600 to-rose-500 hover:from-red-500 hover:to-rose-400 text-white px-8 py-4 rounded-xl font-bold text-base transition shadow-lg pulse-btn">Copy & Go →</button>
                    </div>
                    <script>
                        function copyAndGo() {
                            navigator.clipboard.writeText("{{ $deal->promo_code ?? $deal->coupon_code }}");
                            alert("Code copied!");
                            window.open("{{ route('deal.redirect', $deal->hash_id) }}", "_blank");
                        }
                    </script>
                @else
                    <a href="{{ route('deal.redirect', $deal->hash_id) }}" target="_blank" class="flex justify-center items-center w-full sm:w-80 bg-gradient-to-r from-red-600 to-rose-500 text-white px-8 py-4 rounded-2xl font-black text-lg hover:from-red-500 hover:to-rose-400 transition-all transform hover:-translate-y-1 shadow-xl hover:shadow-red-500/20 pulse-btn">
                        Get Deal Now
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                @endif
            </div>

            <!-- AI Caption Copy & Share Buttons -->
            <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                @if($deal->ai_caption)
                    <button onclick="copyCaption()" class="w-full sm:w-auto flex justify-center items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition bg-slate-100 dark:bg-slate-800 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:bg-slate-200 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        Copy Telegram Post
                    </button>
                    <script>
                        function copyCaption() {
                            let caption = @js($deal->ai_caption);
                            // Ensure literal \n strings from the LLM are converted to actual newlines
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
    
    <x-ad-banner slot="deal-middle" />

    <!-- Price History Chart -->
    @if($priceHistory->count() > 1)
    <div class="mt-12 pt-8 border-t border-slate-100 dark:border-slate-800">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            Price Drop History
        </h3>
        <div class="h-64 bg-white dark:bg-slate-900 rounded-2xl p-4 border border-gray-100 dark:border-slate-800 shadow-sm">
            <canvas id="priceChart"></canvas>
        </div>
        <script>
            const ctx = document.getElementById('priceChart').getContext('2d');
            const data = @json($priceHistory->map(fn($p) => ['x' => $p->recorded_at->format('Y-m-d'), 'y' => $p->price]));
            
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
    <div class="mt-12 pt-8 border-t border-slate-100 dark:border-slate-800">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            Compare Similar Deals
        </h3>
        <div class="grid gap-4 grid-cols-2 md:grid-cols-4">
            @foreach($similarDeals as $similarDeal)
                <x-deal-card :deal="$similarDeal" />
            @endforeach
        </div>
    </div>
    @endif
    
    <x-ad-banner slot="deal-bottom" />
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('priceUpdater', (dealId, initialPrice) => ({
            dealId: dealId,
            currentPrice: initialPrice,
            isChecking: false,
            verifyPrice() {
                this.isChecking = true;
                fetch(`/api/deals/${this.dealId}/refresh-price`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
                }).catch(err => {
                    this.isChecking = false;
                });
                
                // Fallback timeout just in case python worker is offline
                setTimeout(() => {
                    if (this.isChecking) {
                        this.isChecking = false;
                        alert('Verification timed out. Desktop Worker might be offline.');
                    }
                }, 15000);
            },
            listenForUpdates() {
                if (window.Echo) {
                    window.Echo.channel(`deals.${this.dealId}`)
                        .listen('.DealUpdated', (e) => {
                            this.currentPrice = e.new_price;
                            this.isChecking = false;
                        });
                }
            }
        }));
    });
</script>
@endpush
@endsection

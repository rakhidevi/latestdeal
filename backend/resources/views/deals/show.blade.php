@extends('layouts.app')

@section('meta')
    <title>{{ $deal->title }} - Latest Deals</title>
    <meta name="description" content="Get {{ $deal->title }} for just ₹{{ $deal->discounted_price }}. Original price: ₹{{ $deal->original_price }}.">
    
    <!-- Open Graph for WhatsApp/Telegram Previews -->
    <meta property="og:title" content="{{ $deal->title }}">
    <meta property="og:description" content="Get it for just ₹{{ $deal->discounted_price }}! Regular Price: ₹{{ $deal->original_price }}.">
    <meta property="og:image" content="{{ asset($deal->image_path) }}">
    <meta property="og:url" content="{{ route('deal.show', $deal->id) }}">
    <meta property="og:type" content="product">
    
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "{{ addslashes($deal->title) }}",
      "image": "{{ asset($deal->image_path) }}",
      "description": "Get {{ addslashes($deal->title) }} at a discounted price.",
      "offers": {
        "@type": "Offer",
        "url": "{{ route('deal.show', $deal->id) }}",
        "priceCurrency": "INR",
        "price": "{{ $deal->discounted_price }}",
        "itemCondition": "https://schema.org/NewCondition",
        "availability": "https://schema.org/InStock"
      }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0,0,0,0.02);
        }
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
<div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none">
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-red-400 rounded-full mix-blend-multiply filter blur-[100px] opacity-20"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-400 rounded-full mix-blend-multiply filter blur-[100px] opacity-20"></div>
</div>

<div class="max-w-5xl mx-auto glass-panel rounded-3xl p-6 sm:p-12 mb-12 relative overflow-hidden">
    <!-- Decorative top gradient -->
    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-red-500 via-orange-400 to-red-500"></div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Left Column: Image & AI Verdict -->
        <div class="space-y-6">
            <div class="relative group rounded-2xl overflow-hidden bg-white p-4 shadow-sm border border-gray-100">
                <img src="{{ asset($deal->image_path) }}" alt="{{ $deal->title }}" class="w-full object-contain rounded-xl transition duration-500 group-hover:scale-105" style="max-height: 400px;">
                @if($deal->original_price > 0)
                    <div class="absolute top-6 left-6 bg-red-500 text-white text-sm font-black px-4 py-2 rounded-full shadow-lg transform -rotate-2">
                        🔥 {{ round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) }}% OFF
                    </div>
                @endif
            </div>

            @if($deal->verdict)
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-5 border border-indigo-100 shadow-inner relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 text-indigo-200 opacity-50">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    </div>
                    <div class="relative z-10">
                        <h3 class="text-sm font-bold text-indigo-800 uppercase tracking-wider mb-2 flex items-center gap-2">
                            <span>❤️</span> LatestDeal Pick
                        </h3>
                        <p class="text-gray-800 font-medium leading-relaxed">{{ $deal->verdict }}</p>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Right Column: Details & Actions -->
        <div class="flex flex-col justify-center">
            
            @if($deal->trust_metrics)
                <div class="inline-flex items-center gap-2 bg-yellow-50 text-yellow-800 px-4 py-1.5 rounded-full text-sm font-semibold border border-yellow-200 mb-4 w-fit">
                    {{ $deal->trust_metrics }}
                </div>
            @endif

            <div class="flex justify-between items-start gap-4">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight">{{ $deal->title }}</h1>
                @auth
                    <form action="{{ route('deal.save', $deal->id) }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" class="p-3 bg-white rounded-full shadow-sm border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-200 transition group" title="Save Deal">
                            <svg class="w-6 h-6 group-hover:fill-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </button>
                    </form>
                @endauth
            </div>
            
            <div class="mt-6 flex items-baseline gap-4">
                <span class="text-5xl font-black text-red-600 tracking-tight">₹{{ number_format($deal->discounted_price) }}</span>
                <span class="text-xl text-gray-400 line-through font-medium">M.R.P: ₹{{ number_format($deal->original_price) }}</span>
            </div>

            <!-- Features -->
            @if($deal->features && count($deal->features) > 0)
                <div class="mt-8 space-y-3">
                    @foreach($deal->features as $feature)
                        <div class="flex items-start gap-3">
                            <span class="text-gray-700 text-lg leading-snug">{{ $feature }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Copy Code & Go -->
            <div class="mt-10">
                @if($deal->promo_code || $deal->coupon_code)
                    <div class="bg-gray-900 rounded-2xl p-2 pl-6 flex items-center justify-between shadow-xl mb-4">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-1">Use Code at Checkout</p>
                            <p class="text-2xl font-mono font-bold text-white tracking-wider" id="promo-code">{{ $deal->promo_code ?? $deal->coupon_code }}</p>
                        </div>
                        <button onclick="copyAndGo()" class="bg-red-500 hover:bg-red-600 text-white px-8 py-4 rounded-xl font-bold text-lg transition shadow-lg pulse-btn">Copy & Go →</button>
                    </div>
                    <script>
                        function copyAndGo() {
                            navigator.clipboard.writeText("{{ $deal->promo_code ?? $deal->coupon_code }}");
                            alert("Code copied!");
                            window.open("{{ route('deal.redirect', $deal->id) }}", "_blank");
                        }
                    </script>
                @else
                    <a href="{{ route('deal.redirect', $deal->id) }}" target="_blank" class="block w-full text-center bg-gradient-to-r from-red-600 to-red-500 text-white px-8 py-4 rounded-2xl font-black text-xl hover:from-red-500 hover:to-red-400 transition transform hover:-translate-y-1 pulse-btn">
                        Get Deal Now 🚀
                    </a>
                @endif
            </div>

            <!-- AI Caption Copy & Share Buttons -->
            <div class="mt-10 pt-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                @if($deal->ai_caption)
                    <button onclick="copyCaption()" class="flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900 transition bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        Copy Telegram Post
                    </button>
                    <script>
                        function copyCaption() {
                            const caption = `{!! addslashes(str_replace("\n", "\\n", $deal->ai_caption)) !!}`;
                            navigator.clipboard.writeText(caption);
                            alert("Social media caption copied to clipboard!");
                        }
                    </script>
                @endif
                
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Share:</span>
                    <x-deal-share :deal="$deal" />
                </div>
            </div>        
        </div>
    </div>

    <!-- Price History Chart -->
    @if($priceHistory->count() > 1)
    <div class="mt-16 pt-10 border-t border-gray-100">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            Price Drop History
        </h3>
        <div class="h-64 bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
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
</div>
@endsection

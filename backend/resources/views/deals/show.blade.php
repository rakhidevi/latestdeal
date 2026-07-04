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
      "@@context": "https://schema.org/",
      "@@type": "Product",
      "name": "{{ addslashes($deal->title) }}",
      "image": "{{ asset($deal->image_path) }}",
      "description": "Get {{ addslashes($deal->title) }} at a discounted price.",
      "offers": {
        "@@type": "Offer",
        "url": "{{ route('deal.show', $deal->id) }}",
        "priceCurrency": "INR",
        "price": "{{ $deal->discounted_price }}",
        "itemCondition": "https://schema.org/NewCondition",
        "availability": "https://schema.org/InStock"
      }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border p-6 sm:p-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <!-- Image & Tags -->
        <div>
            <img src="{{ asset($deal->image_path) }}" alt="{{ $deal->title }}" class="w-full rounded-xl object-cover">
            @if($deal->tags && $deal->tags->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($deal->tags as $tag)
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- Details & Actions -->
        <div class="flex flex-col justify-center">
            <div class="flex justify-between items-start">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $deal->title }}</h1>
                @auth
                    <form action="{{ route('deal.save', $deal->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-accent transition ml-4" title="Save Deal">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                        </button>
                    </form>
                @endauth
            </div>
            
            <div class="mt-4 flex items-end gap-4">
                <span class="text-4xl font-extrabold text-accent">₹{{ number_format($deal->discounted_price) }}</span>
                <span class="text-lg text-gray-400 line-through mb-1">₹{{ number_format($deal->original_price) }}</span>
                @if($deal->original_price > 0)
                    <span class="mb-1 text-sm font-semibold text-green-600">
                        {{ round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) }}% OFF
                    </span>
                @endif
            </div>

            <!-- Copy Code & Go -->
            <div class="mt-8">
                @if($deal->promo_code || $deal->coupon_code)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Promo Code</p>
                            <p class="text-lg font-mono font-bold text-gray-900" id="promo-code">{{ $deal->promo_code ?? $deal->coupon_code }}</p>
                        </div>
                        <button onclick="copyAndGo()" class="bg-gray-900 text-white px-4 py-2 rounded-md font-semibold hover:bg-gray-800 transition">Copy & Go</button>
                    </div>
                    <script>
                        function copyAndGo() {
                            navigator.clipboard.writeText("{{ $deal->promo_code ?? $deal->coupon_code }}");
                            alert("Code copied!");
                            window.open("{{ route('deal.redirect', $deal->id) }}", "_blank");
                        }
                    </script>
                @else
                    <a href="{{ route('deal.redirect', $deal->id) }}" target="_blank" class="block w-full text-center bg-accent text-white px-6 py-3 rounded-xl font-bold text-lg hover:bg-accent/90 transition shadow-lg shadow-accent/30">
                        Get Deal Now
                    </a>
                @endif
            </div>

            <!-- Share Buttons -->
            <div class="mt-8 pt-6 border-t">
                <p class="text-sm text-gray-500 mb-3">Share this deal:</p>
                <div class="-mt-4">
                    <x-deal-share :deal="$deal" />
                </div>
            </div>        
        </div>
    </div>

    <!-- Price History Chart -->
    @if($priceHistory->count() > 1)
    <div class="mt-12 pt-8 border-t">
        <h3 class="text-xl font-bold text-gray-900 mb-6">Price History (Last 30 Days)</h3>
        <div class="h-64">
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
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { type: 'category', labels: data.map(d => d.x) },
                        y: { beginAtZero: false }
                    }
                }
            });
        </script>
    </div>
    @endif
</div>
@endsection

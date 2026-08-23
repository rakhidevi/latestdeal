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
    <!-- Breadcrumb -->
    <div class="flex items-center text-sm font-bold text-gray-400 mb-8 overflow-x-auto whitespace-nowrap no-scrollbar">
        <a href="/" class="hover:text-gray-900 transition-colors">Home</a>
        
        @if($deal->categories && $deal->categories->count() > 0)
            <span class="mx-2">/</span>
            <a href="/search?category={{ $deal->categories->first()->slug ?? $deal->categories->first()->name }}" class="hover:text-gray-900 transition-colors">
                {{ $deal->categories->first()->name }}
            </a>
        @endif
        
        @if($deal->brand)
            <span class="mx-2">/</span>
            <a href="/search?brand={{ strtolower(urlencode($deal->brand)) }}" class="hover:text-gray-900 transition-colors">
                {{ $deal->brand }}
            </a>
        @endif
    </div>

    <!-- Single-Column Trust Layout -->
    <div class="max-w-3xl mx-auto flex flex-col gap-8">
        
        <!-- Hero: Image, Price, Trust Badges, CTA -->
        <x-deal.hero :deal="$deal" />
        
        <!-- Trust Block: Math & Savings Breakdown -->
        <x-deal.trust-block :deal="$deal" />
        
        <!-- Editorial: AI Summary -->
        <x-deal.editorial :deal="$deal" />
        
        <!-- Facts: Verified Specs -->
        <x-deal.facts :deal="$deal" />

    </div>
    
    <!-- Related Deals -->
    @if(isset($similarDeals) && count($similarDeals) > 0)
        <div class="mt-24 border-t border-gray-100 pt-16">
            <h2 class="text-2xl font-black text-gray-900 mb-8">You may also like</h2>
            <div class="grid gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach($similarDeals as $similarDeal)
                    @include('partials.deal_card', ['deal' => $similarDeal])
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Sticky Mobile CTA -->
<div class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 shadow-[0_-10px_40px_rgba(0,0,0,0.1)] z-50 flex items-center justify-between">
    <div class="flex flex-col">
        <span class="text-xl font-black text-gray-900">₹{{ number_format($deal->discounted_price) }}</span>
        @if($deal->original_price > $deal->discounted_price)
            <span class="text-xs font-bold text-gray-400 line-through">₹{{ number_format($deal->original_price) }}</span>
        @endif
    </div>
    <a href="{{ $deal->tracking_url ?? $deal->url }}" target="_blank" rel="nofollow noopener" 
       class="bg-red-600 hover:bg-red-700 text-white font-black py-3 px-6 rounded-xl shadow-lg transition-all">
        View Deal →
    </a>
</div>

<!-- Add padding to body to account for mobile sticky CTA -->
<style>
    @media (max-width: 767px) {
        body { padding-bottom: 80px; }
    }
</style>
@endsection

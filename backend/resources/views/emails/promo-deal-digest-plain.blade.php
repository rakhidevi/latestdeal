{{ $headline }} — LatestDeal.in
{{ str_repeat('=', 40) }}

{{ $subheadline }}

⏰ Limited Time — Prices may change anytime!

{{ str_repeat('-', 40) }}
TODAY'S TOP DEALS
{{ str_repeat('-', 40) }}

@foreach($deals as $deal)
{{ $loop->iteration }}. {{ $deal->title }}
   @if($deal->brandRelation)Brand: {{ $deal->brandRelation->name }}
   @endif
   Price: ₹{{ number_format($deal->discounted_price ?? $deal->effective_price ?? 0) }}
   @if($deal->original_price && $deal->original_price > ($deal->discounted_price ?? 0))MRP: ₹{{ number_format($deal->original_price) }} ({{ round($deal->discount_percentage) }}% OFF — Save ₹{{ number_format($deal->amount_saved ?? ($deal->original_price - ($deal->discounted_price ?? 0))) }})
   @endif
   Grab Deal: {{ $deal->affiliate_url }}
   View on LatestDeal: {{ url('/deal/' . $deal->slug . '/' . $deal->hash_id) }}

@endforeach
{{ str_repeat('-', 40) }}

View All Deals: {{ url('/') }}

—
LatestDeal.in — Autonomous Deal Discovery & Price Tracking Engine
© {{ date('Y') }} LatestDeal.in. All rights reserved.

@if(isset($unsubscribeUrl))
Unsubscribe from deal alerts: {{ $unsubscribeUrl }}
@endif

@props(['deal'])

@php
    $postedOn = $deal->created_at ? $deal->created_at->format('M j, Y') : 'Today';
    $imageUrl = $deal->image_url ?: ($deal->image_path ? asset($deal->image_path) : 'https://picsum.photos/seed/deal-'.$deal->id.'/320/220');
    $storeUrl = $deal->url ?? '#';
    
    // Calculate discount percent if not explicitly stored
    $discountPct = 0;
    if(isset($deal->discount_percent) && $deal->discount_percent) {
        $discountPct = $deal->discount_percent;
    } elseif(isset($deal->original_price) && $deal->original_price > 0 && isset($deal->discounted_price)) {
        $discountPct = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
    }
@endphp

<article class="group overflow-hidden rounded-xl border border-red-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900 flex flex-col h-full">
  <div class="relative h-32 bg-gradient-to-br from-red-100 to-rose-100 p-2 dark:from-slate-800 dark:to-slate-700">
    <img src="{{ $imageUrl }}" alt="{{ $deal->title }}" class="h-full w-full rounded-lg object-cover" />
    
    @if($discountPct > 0)
    <div class="absolute left-3 top-3 rounded-full bg-red-600 px-2.5 py-1 text-xs font-bold text-white">{{ $discountPct }}% OFF</div>
    @endif
    
    @if(isset($deal->ai_score))
    <div class="absolute right-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-sm border border-slate-100">AI {{ $deal->ai_score }}</div>
    @endif
  </div>

  <div class="space-y-2 p-3 flex flex-col flex-1">
    <div>
        <h3 class="line-clamp-2 text-xs font-semibold text-gray-900 group-hover:text-red-700 dark:text-slate-100" title="{{ $deal->title }}">{{ $deal->title }}</h3>
        <p class="line-clamp-2 text-[11px] leading-4 text-gray-600 dark:text-slate-400 mt-1">{{ Str::limit($deal->ai_caption ?? $deal->description ?? 'Amazing deal handpicked for you. Verify prices before buying.', 80) }}</p>
    </div>

    <div class="flex items-center justify-between border-t border-red-100 pt-2 dark:border-slate-800 mt-auto">
      <div>
        <p class="text-[11px] uppercase tracking-wide text-gray-400">Price</p>
        @if(isset($deal->discounted_price))
          <p class="text-[11px] font-semibold text-gray-900 dark:text-slate-200">
            ₹{{ number_format($deal->discounted_price) }}
            @if(isset($deal->original_price) && $deal->original_price > $deal->discounted_price)
              <span class="text-[10px] text-gray-400 line-through ml-1">₹{{ number_format($deal->original_price) }}</span>
            @endif
          </p>
        @else
          <p class="text-[11px] font-semibold text-gray-900 dark:text-slate-200">Check Site</p>
        @endif
      </div>
      <div class="flex gap-1.5">
        <a href="{{ route('deal.show', $deal->id) }}" class="rounded-lg border border-red-200 bg-white px-2 py-1 text-[11px] font-semibold text-red-700">Details</a>
        <a href="{{ route('deal.redirect', $deal->id) }}" target="_blank" rel="noreferrer" class="rounded-lg bg-red-500 px-2 py-1 text-[11px] font-semibold text-white transition hover:bg-red-600">
          Visit
        </a>
      </div>
    </div>
  </div>
</article>

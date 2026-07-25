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

<article class="group overflow-hidden rounded-xl border border-red-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900 flex flex-col h-full {{ $deal->status === 'expired' ? 'opacity-75 grayscale-[0.5]' : '' }}">
  <div class="relative h-32 sm:h-40 bg-gradient-to-br from-red-100 to-rose-100 p-2 dark:from-slate-800 dark:to-slate-700 overflow-hidden flex items-center justify-center flex-shrink-0">
    <img src="{{ $imageUrl }}" alt="{{ Str::limit($deal->title, 20) }}" class="max-h-full max-w-full rounded-lg object-contain mix-blend-multiply dark:mix-blend-normal" onerror="this.style.display='none';" />
    
    @if($deal->status === 'expired')
    <div class="absolute inset-0 bg-black/40 flex items-center justify-center backdrop-blur-[2px] z-10">
        <span class="bg-red-600 text-white font-black px-3 py-1.5 rounded-lg shadow-lg border border-red-400/50 rotate-[-10deg] text-lg uppercase tracking-widest">Expired</span>
    </div>
    @elseif($discountPct > 0)
    <div class="absolute left-3 top-3 rounded-full bg-red-600 px-2.5 py-1 text-xs font-bold text-white">{{ $discountPct }}% OFF</div>
    @endif
    
    @if(isset($deal->ai_score) && $deal->ai_score > 0)
    <div class="absolute right-2 top-2 rounded-xl {{ $deal->ai_score >= 8 ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white border-none shadow-md shadow-red-500/30' : 'bg-white/90 text-slate-700 border-slate-200' }} px-2 py-1 text-[10px] font-black tracking-wide border backdrop-blur-md flex items-center gap-1">
        @if($deal->ai_score >= 8)
            <svg class="w-3 h-3 text-yellow-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"></path></svg>
        @else
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
        @endif
        AI {{ $deal->ai_score }}/10
    </div>
    @endif
  </div>

  <div class="space-y-2 p-3 flex flex-col flex-1">
    <div>
        <h3 class="line-clamp-2 text-xs font-semibold text-gray-900 group-hover:text-red-700 dark:text-slate-100" title="{{ $deal->title }}">{{ $deal->title }}</h3>
        <p class="line-clamp-2 text-[11px] leading-4 text-gray-600 dark:text-slate-400 mt-1">{{ Str::limit($deal->ai_caption ?? $deal->description ?? 'Amazing deal handpicked for you. Verify prices before buying.', 80) }}</p>
    </div>

    <div class="flex flex-col xl:flex-row xl:items-center justify-between border-t border-red-100 pt-2 dark:border-slate-800 mt-auto gap-2">
      <div>
        <p class="text-[10px] uppercase tracking-wide text-gray-400">Price</p>
        @if(isset($deal->discounted_price))
          <p class="text-[11px] font-semibold {{ $deal->status === 'expired' ? 'text-gray-400 line-through' : 'text-gray-900 dark:text-slate-200' }}">
            ₹{{ number_format($deal->discounted_price) }}
            @if(isset($deal->original_price) && $deal->original_price > $deal->discounted_price)
              <span class="text-[10px] text-gray-400 line-through ml-0.5">₹{{ number_format($deal->original_price) }}</span>
            @endif
          </p>
        @else
          <p class="text-[11px] font-semibold text-gray-900 dark:text-slate-200">Check Site</p>
        @endif
      </div>
      <div class="flex gap-1.5 w-full xl:w-auto">
        <a href="{{ route('deal.show', $deal->slug) }}" class="flex-1 xl:flex-none text-center rounded border border-red-200 bg-white px-2 py-1 text-[11px] font-semibold text-red-700 hover:bg-red-50 dark:border-slate-700 dark:bg-slate-800 dark:text-red-400 dark:hover:bg-slate-700 transition">Details</a>
        <a href="{{ route('deal.redirect', $deal->hash_id) }}" target="_blank" rel="noreferrer" class="flex-1 xl:flex-none text-center rounded bg-red-500 px-2 py-1 text-[11px] font-semibold text-white transition hover:bg-red-600">
          Visit
        </a>
      </div>
    </div>
  </div>
</article>

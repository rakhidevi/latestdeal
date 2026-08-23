@props(['deal'])

@php
  $postedOn = $deal->created_at ? $deal->created_at->diffForHumans(null, true, true) : 'just now';
  $fallbackText = urlencode($deal->brand ?? 'Deal');
  $imageUrl = $deal->image_url ?: "https://placehold.co/320x220/f8fafc/64748b?text={$fallbackText}";
  $storeUrl = $deal->url ?? '#';
  
  // Calculate discount percent
  $discountPct = 0;
  if(isset($deal->discount_percent) && $deal->discount_percent) {
    $discountPct = $deal->discount_percent;
  } elseif(isset($deal->original_price) && $deal->original_price > 0 && isset($deal->discounted_price)) {
    $discountPct = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
  }
  
  // Generate pseudo-random social stats based on deal ID to simulate community activity
  $upvotes = ($deal->id % 45) + 12;
  $comments = ($deal->id % 15) + 2;
@endphp

<article class="group relative flex flex-col h-full overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl  {{ $deal->status === 'expired' ? 'opacity-75 grayscale-[0.5]' : '' }}">
  
  <!-- Top Image Container -->
  <div class="relative flex h-52 w-full items-center justify-center bg-white p-6 border-b border-gray-50 ">
    <img src="{{ $imageUrl }}" alt="{{ Str::limit($deal->title, 50) }}" class="max-h-full max-w-full rounded object-contain mix-blend-multiply transition-transform duration-500 group-hover:scale-105" loading="lazy" onerror="this.src='https://placehold.co/320x220/f8fafc/64748b?text={{ $fallbackText }}';" />
    
    <!-- Top Left: Discount Badge -->
    @if($deal->status === 'expired')
    <div class="absolute left-3 top-3 z-10">
      <span class="bg-gray-800 text-white font-bold px-2.5 py-1 rounded-md text-[11px] shadow-sm uppercase tracking-wide">Expired</span>
    </div>
    @elseif($discountPct > 0)
    <div class="absolute left-3 top-3 z-10">
      <span class="bg-blue-600 text-white font-black px-2.5 py-1 rounded-md text-[11px] shadow-sm tracking-wide">{{ $discountPct }}% OFF</span>
    </div>
    @endif
    
    <!-- Top Right: Psychological Status Badge -->
    @if($discountPct >= 65)
    <div class="absolute right-3 top-3 z-10">
      <span class="bg-gradient-to-r from-red-600 to-rose-600 text-white font-black px-2.5 py-1 rounded-md text-[11px] shadow-lg shadow-red-500/30 uppercase tracking-widest animate-pulse">🔥 Loot</span>
    </div>
    @elseif($discountPct >= 40)
    <div class="absolute right-3 top-3 z-10">
      <span class="bg-emerald-500 text-white font-black px-2.5 py-1 rounded-md text-[11px] shadow-sm uppercase tracking-widest">⚡ Super Deal</span>
    </div>
    @endif
  </div>

  <!-- Content Container -->
  <div class="flex flex-1 flex-col p-5">
    <!-- Brand & Freshness Timestamp -->
    <div class="flex items-center justify-between mb-1.5">
      <div class="text-[12px] font-bold text-gray-500 uppercase tracking-widest ">
        {{ $deal->brand ?? $deal->merchant->name ?? 'Deal' }}
      </div>
      <div class="flex items-center text-[10px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-2 py-0.5 rounded">
        {{ $postedOn }} ago
      </div>
    </div>
    
    <!-- Title -->
    <a href="{{ route('deal.show', $deal->slug) }}" class="block">
      <h3 class="mb-4 text-[15px] font-bold leading-snug text-gray-900 line-clamp-2 group-hover:text-red-600 transition-colors " title="{{ $deal->title }}">
        {{ $deal->title }}
      </h3>
    </a>
    
    <!-- Push bottom elements to the end -->
    <div class="mt-auto space-y-4">
      
      <!-- Price Row -->
      <div class="flex items-baseline gap-2.5">
        @if(isset($deal->discounted_price))
          <span class="text-[22px] font-black tracking-tight text-emerald-700 ">₹{{ number_format($deal->discounted_price) }}</span>
          @if(isset($deal->original_price) && $deal->original_price > $deal->discounted_price)
            <span class="text-[14px] font-bold text-gray-400 line-through">₹{{ number_format($deal->original_price) }}</span>
          @endif
        @else
          <span class="text-[22px] font-black tracking-tight text-gray-900 ">Check Site</span>
        @endif
      </div>

      <!-- Social Proof & Tags Row -->
      <div class="flex items-center justify-between border-t border-gray-50 pt-3">
        <div class="flex gap-3 text-[12px] font-bold text-gray-400">
          <div class="flex items-center gap-1 hover:text-emerald-500 cursor-pointer transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
            {{ $upvotes }}
          </div>
          <div class="flex items-center gap-1 hover:text-blue-500 cursor-pointer transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            {{ $comments }}
          </div>
        </div>
        
        @if(isset($deal->merchant->name))
          <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-0.5 text-[11px] font-bold text-gray-500  border border-gray-100 ">
            {{ $deal->merchant->name }}
          </span>
        @endif
      </div>

      <!-- CTA Button -->
      <div class="pt-1">
        <a href="{{ route('deal.redirect', $deal->hash_id ?? $deal->id) }}" target="_blank" rel="noreferrer" class="flex w-full items-center justify-center gap-2 rounded-xl bg-gray-900 px-4 py-2.5 text-[14px] font-bold text-white transition-all hover:bg-red-600 active:scale-[0.98] shadow-sm hover:shadow">
          Shop Now
          <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </a>
      </div>
      
    </div>
  </div>
</article>

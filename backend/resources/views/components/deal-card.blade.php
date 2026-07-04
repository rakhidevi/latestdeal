@props(['deal'])

<div class="group relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 ease-out flex flex-col h-full">
    <!-- Image Section -->
    <div class="relative w-full pt-[100%] bg-gray-50 overflow-hidden">
        <a href="{{ route('deal.show', $deal->id) }}" class="absolute inset-0 block">
            <img src="{{ asset($deal->image_path) }}" alt="{{ $deal->title }}" class="absolute inset-0 w-full h-full object-contain p-6 mix-blend-multiply group-hover:scale-110 transition-transform duration-500 ease-out">
        </a>
        
        <!-- Discount Badge (Glassmorphism) -->
        @php
            $discount = 0;
            if ($deal->original_price > 0) {
                $discount = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
            }
        @endphp
        @if($discount > 0)
        <div class="absolute top-4 left-4 z-10">
            <div class="bg-red-500/90 backdrop-blur-md text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg shadow-red-500/30 flex items-center gap-1 border border-red-400/50">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                {{ $discount }}% OFF
            </div>
        </div>
        @endif
        
        <!-- Hover Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
    </div>

    <!-- Content Section -->
    <div class="p-6 flex flex-col flex-grow bg-white relative">
        <a href="{{ route('deal.show', $deal->id) }}" class="block mb-2 flex-grow">
            <h3 class="text-[17px] font-bold text-gray-800 leading-snug line-clamp-2 group-hover:text-red-600 transition-colors duration-200" title="{{ $deal->title }}">
                {{ $deal->title }}
            </h3>
        </a>
        
        <div class="mt-auto pt-4 border-t border-gray-50">
            <div class="flex items-end gap-2 mb-4">
                <span class="text-3xl font-extrabold text-gray-900 tracking-tight">₹{{ number_format($deal->discounted_price) }}</span>
                @if($discount > 0)
                <span class="text-sm font-semibold text-gray-400 line-through mb-1">₹{{ number_format($deal->original_price) }}</span>
                @endif
            </div>

            <!-- Grab Deal Button (Premium gradient & icon) -->
            <a href="{{ route('deal.redirect', ['deal' => $deal->id]) }}" target="_blank" rel="noopener nofollow" class="block w-full">
                <button class="w-full relative overflow-hidden bg-gradient-to-r from-gray-900 to-gray-800 hover:from-black hover:to-gray-900 text-white font-semibold py-3.5 px-6 rounded-xl flex items-center justify-center gap-2 transition-all duration-300 shadow-md shadow-gray-900/20 group/btn">
                    <span class="relative z-10">Grab Deal Now</span>
                    <svg class="w-5 h-5 transform group-hover/btn:translate-x-1 transition-transform relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </a>
            
            <x-deal-share :deal="$deal" />
        </div>
    </div>
</div>

@props(['deal'])

@php
    $merchantName = $deal->merchant ? $deal->merchant->name : 'Store';
@endphp

<div class="flex flex-col gap-3">
    <a href="{{ $deal->tracking_url ?? $deal->url }}" target="_blank" rel="nofollow noopener" 
       class="w-full flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white text-lg font-black py-4 px-8 rounded-2xl shadow-lg shadow-red-500/30 transition-all hover:-translate-y-1">
        View Deal at {{ $merchantName }}
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
    </a>
    
    <div class="text-center">
        <p class="text-[11px] text-gray-400 font-medium tracking-wide">
            We may earn a commission if you purchase through this link.
        </p>
    </div>
</div>

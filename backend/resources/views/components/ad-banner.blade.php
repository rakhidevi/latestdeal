@props(['format' => 'auto', 'slot' => ''])

@php
    $adClientId = config('services.google.adsense_id', 'ca-pub-3274200073613804');
    
    // Default to Standard Display Ad
    $adSlotId = "1289514018";
    $adFormat = $format;
    $layoutKey = null;

    if ($slot === 'home-bottom' || $slot === 'deal-bottom') {
        // Multiplex Grid Ad at the bottom of the page
        $adSlotId = "2022378278";
        $adFormat = "autorelaxed";
    } elseif ($slot === 'in-feed') {
        // In-feed Fluid Ad inside the grids
        $adSlotId = "6839590681";
        $adFormat = "fluid";
        $layoutKey = "-7b+dy+4s-33-8p";
    }
@endphp

<div class="w-full overflow-hidden flex justify-center items-center {{ $format == 'vertical' ? 'my-0' : 'my-6' }}">
    @if(config('services.google.adsense_id') && $slot)
        <!-- Google AdSense ({{ $slot }}) -->
        <ins class="adsbygoogle"
             style="display:block"
             @if($layoutKey) data-ad-layout-key="{{ $layoutKey }}" @endif
             data-ad-client="{{ $adClientId }}"
             data-ad-slot="{{ $adSlotId }}"
             data-ad-format="{{ $adFormat }}"
             @if($adFormat !== 'autorelaxed') data-full-width-responsive="true" @endif></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    @else
        <!-- Ad Placeholder -->
        <div class="w-full bg-slate-100 border border-dashed border-slate-300 rounded-lg flex flex-col items-center justify-center text-slate-400 text-sm dark:bg-slate-800/50 dark:border-slate-700 p-4 {{ $format == 'vertical' ? 'h-[600px] max-w-[160px]' : 'h-[90px] max-w-[728px]' }}">
            <div class="flex flex-col items-center gap-2 text-center">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                <span class="text-xs font-semibold uppercase tracking-wider">Ad Space</span>
            </div>
        </div>
    @endif
</div>

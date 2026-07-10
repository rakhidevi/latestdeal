@props(['type' => 'horizontal'])

<div {{ $attributes->merge(['class' => 'w-full flex justify-center overflow-hidden']) }}>
    <div class="bg-gray-50 border border-gray-200 rounded-xl flex flex-col items-center justify-center text-gray-400 text-sm font-medium
        {{ $type === 'horizontal' ? 'w-full h-24 max-w-4xl' : 'w-full h-64 sm:h-auto sm:aspect-video' }}">
        
        <!-- Google AdSense Placeholder -->
        <span class="mb-2 uppercase tracking-widest text-xs">Advertisement</span>
        
        <!-- TODO: Insert actual Google Ads snippet below once AdSense is approved -->
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="ca-pub-3274200073613804"
             data-ad-slot="1289514018"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
        <div class="flex items-center gap-2 text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 11V9a2 2 0 00-2-2m2 4v4a2 2 0 104 0v-1m-4-3H9m2 0h4m6 1a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Google Ads Space
        </div>
    </div>
</div>

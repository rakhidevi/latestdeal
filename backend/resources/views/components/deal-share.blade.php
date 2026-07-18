@props(['deal'])

@php
    $discount = 0;
    if ($deal->original_price > 0) {
        $discount = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
    }
    
    $formattedShareText = "🔥 *" . $deal->title . "* 🔥\n\n" .
                          "💰 *Deal Price:* ₹" . number_format($deal->discounted_price) . "\n" .
                          ($discount > 0 ? "❌ *Regular Price:* ₹" . number_format($deal->original_price) . " (" . $discount . "% OFF)\n\n" : "\n") .
                          "🛒 *Buy it Now:*\n" . route('deal.redirect', $deal->hash_id) . "\n\n" .
                          "👇 *View Deal Details:*\n" . route('deal.show', $deal->slug);
@endphp

<div x-data="{
    shareUrl: '{{ route('deal.show', $deal->slug) }}',
    shareText: @js($formattedShareText),
    
    async webShare() {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: 'LatestDeal',
                    text: this.shareText,
                    url: this.shareUrl
                });
            } catch (err) {
                console.log('Error sharing:', err);
            }
        } else {
            // Fallback: Copy to clipboard
            navigator.clipboard.writeText(this.shareText);
            alert('Link copied to clipboard!');
        }
    }
}" class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
    
    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Share</span>
    
    <div class="flex items-center gap-3">
        <!-- WhatsApp -->
        <a :href="`https://api.whatsapp.com/send?text=${encodeURIComponent(shareText)}`" target="_blank" rel="noopener" class="text-green-500 hover:text-green-600 transition hover:scale-110" title="Share on WhatsApp">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
        </a>
        
        <!-- Telegram -->
        <a :href="`https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}`" target="_blank" rel="noopener" class="text-blue-500 hover:text-blue-600 transition hover:scale-110" title="Share on Telegram">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
        </a>

        <!-- Facebook -->
        <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-700 transition hover:scale-110" title="Share on Facebook">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </a>

        <!-- Snapchat -->
        <a :href="`https://snapchat.com/scan?attachmentUrl=${encodeURIComponent(shareUrl)}`" target="_blank" rel="noopener" class="text-yellow-400 hover:text-yellow-500 transition hover:scale-110" title="Share on Snapchat">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.11.127a7.962 7.962 0 00-6.195 2.766 8.163 8.163 0 00-1.848 5.707 15.006 15.006 0 001.077 5.088c.277.72.635 1.398 1.066 2.015.05.074.072.162.062.25-.018.17-.116.326-.264.42l-1.02.617c-.36.216-.48.68-.269 1.04a.78.78 0 001.02.26l2.19-1.096c.214-.107.458-.103.67.014.21.114.364.3.428.528.293 1.01.761 1.956 1.385 2.793C11.023 21.436 12 22 13.064 22h.001c1.066-.002 2.043-.564 2.65-1.428.626-.837 1.093-1.785 1.385-2.797.065-.227.218-.415.428-.528a.8.8 0 01.67-.015l2.19 1.097a.78.78 0 001.02-.262c.211-.36.09-.824-.268-1.04l-1.02-.617a.49.49 0 01-.265-.42.476.476 0 01.062-.249c.431-.617.79-1.296 1.066-2.016a15.005 15.005 0 001.077-5.088 8.163 8.163 0 00-1.848-5.707A7.962 7.962 0 0012.11.127zm-.006 16.962a2.33 2.33 0 01-1.417-.468c-.624-.46-1.122-1.055-1.474-1.758a.56.56 0 01.5-.81h.002a.561.561 0 01.499.31c.212.423.513.784.887 1.06a1.187 1.187 0 001.442.062 1.636 1.636 0 00.56-.639.563.563 0 011.014.475 2.795 2.795 0 01-.96 1.106 2.308 2.308 0 01-1.053.662zm1.405-7.14a.972.972 0 01-.971.972.973.973 0 010-1.944.972.972 0 01.97.973zm-3.033.972a.973.973 0 11.002-1.944.973.973 0 01-.002 1.944z"/></svg>
        </a>
        
        <!-- Native Share / Copy -->
        <button @click="webShare()" class="text-gray-400 hover:text-gray-600 transition hover:scale-110" title="More options...">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
        </button>
    </div>
</div>

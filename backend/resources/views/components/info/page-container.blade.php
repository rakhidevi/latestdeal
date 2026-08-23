@props(['title' => 'Information'])
<div class="relative min-h-screen pt-20 pb-24 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        {{ $slot }}
    </div>
    
    <!-- Global Trust Strip -->
    <div class="max-w-4xl mx-auto px-4 mt-20">
        <div class="border-t border-slate-200 pt-8 pb-4">
            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider mb-4 text-center">LatestDeal Trust Principles</h4>
            <div class="flex flex-wrap justify-center gap-x-6 gap-y-3 text-sm font-bold text-slate-500">
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> Price checked</span>
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> Discount calculated</span>
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> Duplicate checked</span>
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> AI-assisted</span>
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> Human review required before publication</span>
            </div>
        </div>
    </div>
</div>

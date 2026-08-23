@props(['title', 'icon' => 'info'])
<div class="bg-red-50 border border-red-100 rounded-2xl p-6 my-8">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-white border border-red-100 flex items-center justify-center shrink-0">
            @if($icon == 'info')
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @elseif($icon == 'shield')
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            @endif
        </div>
        <div>
            @if($title)
                <h4 class="font-black text-slate-900 mb-2">{{ $title }}</h4>
            @endif
            <div class="text-slate-600 font-medium text-sm leading-relaxed">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

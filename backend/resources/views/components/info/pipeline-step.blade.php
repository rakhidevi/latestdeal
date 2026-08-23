@props(['number', 'title'])
<div class="flex gap-6 relative">
    <div class="flex flex-col items-center">
        <div class="w-12 h-12 rounded-full bg-slate-900 text-white flex items-center justify-center font-black text-lg z-10 shadow-md">
            {{ $number }}
        </div>
        @if(!$attributes->has('last'))
            <div class="w-0.5 h-full bg-slate-200 -my-2"></div>
        @endif
    </div>
    <div class="pb-12 pt-2">
        <h4 class="text-xl font-black text-slate-900 mb-2">{{ $title }}</h4>
        <div class="text-slate-500 font-medium">
            {{ $slot }}
        </div>
    </div>
</div>

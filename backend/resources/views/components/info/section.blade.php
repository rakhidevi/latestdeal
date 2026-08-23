@props(['id', 'number' => null, 'title'])
<div id="{{ $id }}" class="mb-12 scroll-mt-24">
    <div class="flex items-start gap-4 mb-6">
        @if($number)
            <span class="text-2xl font-black text-slate-300 font-mono mt-1">{{ $number }}</span>
        @endif
        <h2 class="text-2xl md:text-3xl font-black text-slate-900">{{ $title }}</h2>
    </div>
    <div class="space-y-6 text-slate-600 font-medium leading-relaxed [&>p]:mb-4 [&>ul]:list-disc [&>ul]:ml-5 [&>ul>li]:mb-2 [&>strong]:text-slate-900">
        {{ $slot }}
    </div>
</div>

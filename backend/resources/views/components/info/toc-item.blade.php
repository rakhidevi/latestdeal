@props(['href', 'number' => null])
<a href="{{ $href }}" class="flex items-center gap-3 group">
    @if($number)
        <span class="text-slate-400 font-mono font-bold text-sm group-hover:text-red-500 transition-colors">{{ $number }}</span>
    @endif
    <span class="text-slate-600 font-bold group-hover:text-slate-900 transition-colors">{{ $slot }}</span>
</a>

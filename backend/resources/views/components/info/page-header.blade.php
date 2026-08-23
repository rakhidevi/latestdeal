@props(['title', 'label' => null])
<div class="mb-12">
    @if($label)
        <span class="inline-block py-1 px-3 rounded-full bg-red-50 border border-red-100 text-red-600 text-xs font-black tracking-widest uppercase mb-4 shadow-sm">
            {{ $label }}
        </span>
    @endif
    <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-4">
        {{ $title }}
    </h1>
    @if(isset($slot) && $slot->isNotEmpty())
        <div class="text-lg text-slate-500 font-medium max-w-2xl">
            {{ $slot }}
        </div>
    @endif
</div>

@props(['title', 'icon' => ''])
<div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm mb-6">
    @if($icon)
        <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mb-6">
            {!! $icon !!}
        </div>
    @endif
    <h3 class="text-2xl font-black text-slate-900 mb-4">{{ $title }}</h3>
    <div class="text-slate-600 font-medium leading-relaxed">
        {{ $slot }}
    </div>
</div>

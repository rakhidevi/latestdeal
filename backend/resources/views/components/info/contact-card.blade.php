@props(['title', 'description', 'email', 'icon'])
<div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex flex-col h-full">
    <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center mb-6 shrink-0 border border-red-100">
        {!! $icon !!}
    </div>
    <h3 class="text-lg font-black text-slate-900 mb-2">{{ $title }}</h3>
    <p class="text-slate-500 text-sm font-medium mb-6 flex-1">{{ $description }}</p>
    <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 font-bold text-red-600 hover:text-red-700 transition-colors">
        {{ $email }}
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
    </a>
</div>

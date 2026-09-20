@extends('admin.layout')

@section('title', 'Template Preview: ' . $templateTitle)

@section('content')
<div class="h-full flex flex-col space-y-4" x-data="{
    device: 'desktop',
    testEmail: '{{ auth()->user()->email ?? '' }}',
    refreshKey: 1,
    refreshIframe() {
        this.refreshKey++;
        let frame = document.getElementById('emailPreviewFrame');
        if (frame) {
            frame.src = '{{ route('admin.marketing.templates.render', $templateKey) }}?v=' + Date.now();
        }
    }
}">
    <!-- Header Control Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.marketing.templates') }}" class="p-2 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors text-slate-500">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h2 class="font-bold text-slate-800 text-lg leading-tight">{{ $templateTitle }}</h2>
                <span class="text-xs font-mono text-slate-400">{{ $templateKey }}</span>
            </div>
        </div>

        <!-- Center: Device Width Toggle -->
        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200">
            <button @click="device = 'desktop'" :class="device === 'desktop' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5">
                <i data-lucide="monitor" class="w-3.5 h-3.5"></i> Desktop (650px)
            </button>
            <button @click="device = 'mobile'" :class="device === 'mobile' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5">
                <i data-lucide="smartphone" class="w-3.5 h-3.5"></i> Mobile (375px)
            </button>
            <button @click="refreshIframe()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg transition-colors" title="Reload with latest data">
                <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
            </button>
        </div>

        <!-- Right: Inline Test Email Sender -->
        <form action="{{ route('admin.marketing.templates.test') }}" method="POST" class="flex items-center gap-2">
            @csrf
            <input type="hidden" name="template" value="{{ $templateKey }}">
            <div class="relative">
                <i data-lucide="mail" class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400"></i>
                <input type="email" name="email" required x-model="testEmail" placeholder="Test inbox email..." class="pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none w-48">
            </div>
            <button type="submit" class="px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                <i data-lucide="send" class="w-3.5 h-3.5"></i> Send Test
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="p-3.5 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-2 text-xs font-semibold">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center gap-2 text-xs font-semibold">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Preview Canvas -->
    <div class="flex-1 bg-slate-100 rounded-2xl border border-slate-200 p-6 flex justify-center items-start overflow-y-auto min-h-[680px]">
        <div class="transition-all duration-300 shadow-2xl rounded-2xl overflow-hidden border border-slate-300 bg-white"
             :style="device === 'desktop' ? 'width: 680px; min-height: 800px;' : 'width: 390px; min-height: 800px;'">
            
            <!-- Mock browser window top bar -->
            <div class="bg-slate-800 px-4 py-2.5 flex items-center justify-between text-slate-400 text-xs">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-500 inline-block"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span>
                </div>
                <div class="bg-slate-900 px-3 py-0.5 rounded text-[11px] font-mono text-slate-300 truncate max-w-xs">
                    preview://{{ $templateKey }}
                </div>
                <div class="text-[10px] uppercase font-bold text-slate-400" x-text="device"></div>
            </div>

            <iframe id="emailPreviewFrame" src="{{ route('admin.marketing.templates.render', $templateKey) }}" class="w-full h-[800px] border-0"></iframe>
        </div>
    </div>
</div>
@endsection

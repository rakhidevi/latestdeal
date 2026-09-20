@extends('admin.layout')

@section('title', 'Email Templates & Previews')

@section('content')
<div class="space-y-6" x-data="{
    showTestModal: false,
    selectedTemplate: 'promo-deal-digest',
    testEmail: '{{ auth()->user()->email ?? '' }}'
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Email Templates</h1>
            <p class="text-sm text-slate-500">Live production email layouts, interactive device previews, and instant deliverability testing.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.marketing.campaigns') }}" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-500/20 transition-all inline-flex items-center gap-2 transform active:scale-95">
                <i data-lucide="send" class="w-4 h-4"></i> Dispatch Newsletter
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-2 font-medium">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center gap-2 font-medium">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($templates as $tmpl)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden flex flex-col justify-between group">
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        {{ $tmpl['category'] }}
                    </span>
                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                    </span>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1 group-hover:text-red-600 transition-colors">
                        {{ $tmpl['name'] }}
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ $tmpl['description'] }}
                    </p>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                    <div class="text-[11px] uppercase font-bold text-slate-400">Sample Subject Line</div>
                    <div class="text-xs font-medium text-slate-700 truncate font-mono">
                        {{ $tmpl['subject_sample'] }}
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center gap-2">
                <a href="{{ route('admin.marketing.templates.preview', $tmpl['id']) }}" class="flex-1 py-2 bg-white hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold border border-slate-200 text-center transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <i data-lucide="eye" class="w-3.5 h-3.5 text-blue-500"></i> Live Preview
                </a>
                <button @click="selectedTemplate = '{{ $tmpl['id'] }}'; showTestModal = true;" class="flex-1 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold text-center transition-colors flex items-center justify-center gap-1.5 shadow-sm shadow-red-500/20">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i> Send Test
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Test Dispatch Modal -->
    <div x-show="showTestModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div @click.away="showTestModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 animate-fade-in">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800">Dispatch Test Email</h3>
                <button @click="showTestModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <p class="text-xs text-slate-500">
                This will render the real template with active deals and send it directly to your test email inbox via your configured mail server.
            </p>

            <form action="{{ route('admin.marketing.templates.test') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="template" x-model="selectedTemplate">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Selected Template</label>
                    <input type="text" readonly x-model="selectedTemplate" class="w-full px-4 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-mono text-slate-600 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Your Destination Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required x-model="testEmail" placeholder="your-email@example.com" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showTestModal = false" class="px-4 py-2 border border-slate-300 rounded-xl text-sm text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-md shadow-red-500/20 flex items-center gap-1.5">
                        <i data-lucide="send" class="w-4 h-4"></i> Dispatch Test Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

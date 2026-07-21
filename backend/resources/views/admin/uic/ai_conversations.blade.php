@extends('admin.layout')

@section('title', 'AI Conversations - UIC')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">AI Conversation Center</h1>
        <p class="text-sm text-slate-500 mt-1">Questions asked to AI Shopping Assistant and user intent distribution</p>
    </div>
    <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-xl text-sm font-bold">
        {{ number_format($totalQuestions) }} Questions Logged
    </span>
</div>

<div class="glass-panel rounded-3xl p-8 shadow-lg">
    <h3 class="text-xl font-bold text-slate-800 mb-6">Recent User Questions & AI Metadata</h3>
    <div class="space-y-4">
        @forelse($conversations as $c)
            <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50">
                <div class="flex items-center justify-between mb-2">
                    <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-md text-xs font-bold uppercase">{{ $c->intent ?? 'General' }}</span>
                    <span class="text-xs text-slate-400 font-mono">{{ $c->created_at ? $c->created_at->diffForHumans() : '' }}</span>
                </div>
                <p class="font-bold text-slate-800 text-base">"{{ $c->question }}"</p>
                @if($c->ai_response_summary)
                    <p class="text-xs text-slate-600 mt-2 bg-white p-3 rounded-lg border border-slate-100 italic">{{ \Illuminate\Support\Str::limit($c->ai_response_summary, 200) }}</p>
                @endif
            </div>
        @empty
            <p class="text-slate-400 italic text-center p-6">No AI conversations logged yet.</p>
        @endforelse
    </div>
    <div class="mt-6">
        {{ $conversations instanceof \Illuminate\Pagination\LengthAwarePaginator ? $conversations->links() : '' }}
    </div>
</div>
@endsection

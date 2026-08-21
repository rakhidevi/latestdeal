@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editorial Review Queue</h1>
        <div class="bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400 px-4 py-2 rounded-lg text-sm font-semibold">
            {{ $deals->total() }} Pending
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    @forelse($deals as $deal)
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 mb-8 overflow-hidden">
        
        <div class="bg-gray-50 dark:bg-slate-900/50 px-6 py-4 border-b border-gray-200 dark:border-slate-700 flex justify-between items-center">
            <div>
                <a href="{{ $deal->url }}" target="_blank" class="text-lg font-bold text-gray-900 dark:text-white hover:text-indigo-500">{{ $deal->title }}</a>
                <p class="text-sm text-gray-500">
                    <span class="font-bold text-green-600">₹{{ number_format($deal->discounted_price) }}</span>
                    @if($deal->original_price > 0)
                        <span class="line-through text-xs ml-2">₹{{ number_format($deal->original_price) }}</span>
                        <span class="text-xs ml-1 font-semibold text-red-500">({{ round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) }}% OFF)</span>
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                <form action="{{ route('admin.deals.reject', $deal->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-sm font-semibold transition-colors">Reject (Draft)</button>
                </form>
            </div>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.deals.approve', $deal->id) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- Left: Raw Source Info -->
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Merchant Description</h3>
                        <div class="p-4 bg-gray-50 dark:bg-slate-900 rounded-lg text-sm text-gray-700 dark:text-gray-300 max-h-64 overflow-y-auto whitespace-pre-wrap">
                            {{ $deal->description ?: 'No description provided by scraper.' }}
                        </div>
                    </div>

                    <!-- Right: AI Generated Content -->
                    <div class="space-y-6">
                        
                        <!-- Verdict -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">AI Verdict</label>
                                <button type="button" onclick="regenerate({{ $deal->id }}, 'verdict')" class="text-xs text-gray-400 hover:text-indigo-500">Regenerate Verdict</button>
                            </div>
                            <textarea name="editorial_verdict" rows="2" class="w-full text-sm p-3 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-indigo-500">{{ $deal->editorial_verdict }}</textarea>
                        </div>

                        <!-- Summary -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Editorial Summary</label>
                                <button type="button" onclick="regenerate({{ $deal->id }}, 'summary')" class="text-xs text-gray-400 hover:text-indigo-500">Regenerate Summary</button>
                            </div>
                            <textarea name="editorial_summary" rows="4" class="w-full text-sm p-3 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-indigo-500">{{ $deal->editorial_summary }}</textarea>
                        </div>

                        <!-- Pros & Cons -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-green-600">Pros</label>
                                    <button type="button" onclick="regenerate({{ $deal->id }}, 'pros_cons')" class="text-xs text-gray-400 hover:text-indigo-500">Regenerate</button>
                                </div>
                                <textarea name="pros" rows="3" class="w-full text-sm p-3 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-indigo-500" placeholder='["Pro 1", "Pro 2"]'>{{ is_array($deal->pros) ? json_encode($deal->pros) : $deal->pros }}</textarea>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-red-600">Cons</label>
                                </div>
                                <textarea name="cons" rows="3" class="w-full text-sm p-3 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-indigo-500" placeholder='["Con 1", "Con 2"]'>{{ is_array($deal->cons) ? json_encode($deal->cons) : $deal->cons }}</textarea>
                            </div>
                        </div>

                        <!-- Best For / Not For -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-blue-600">Best For</label>
                                    <button type="button" onclick="regenerate({{ $deal->id }}, 'best_for')" class="text-xs text-gray-400 hover:text-indigo-500">Regenerate</button>
                                </div>
                                <input type="text" name="best_for" value="{{ is_array($deal->best_for) ? implode(', ', $deal->best_for) : $deal->best_for }}" class="w-full text-sm p-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-indigo-500">
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-gray-600">Not For</label>
                                    <button type="button" onclick="regenerate({{ $deal->id }}, 'not_for')" class="text-xs text-gray-400 hover:text-indigo-500">Regenerate</button>
                                </div>
                                <input type="text" name="not_for" value="{{ is_array($deal->not_for) ? implode(', ', $deal->not_for) : $deal->not_for }}" class="w-full text-sm p-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-indigo-500">
                            </div>
                        </div>

                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-slate-700 flex justify-between items-center bg-gray-50 dark:bg-slate-900/50 -mx-6 -mb-6 px-6 py-4 rounded-b-xl">
                    <button type="button" onclick="regenerate({{ $deal->id }}, 'all')" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">↻ Regenerate All</button>
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-sm transition-colors text-lg flex items-center gap-2">
                        <span>✓</span> Approve & Publish
                    </button>
                </div>
            </form>
        </div>
    </div>
    @empty
        <div class="text-center py-20 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Review Queue Empty</h3>
            <p class="text-gray-500">All AI-generated deals have been reviewed.</p>
        </div>
    @endforelse

    <div class="mt-6">
        {{ $deals->links() }}
    </div>
</div>

<form id="regenerate-form" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="target" id="regenerate-target">
</form>

<script>
function regenerate(dealId, target) {
    const form = document.getElementById('regenerate-form');
    form.action = `/admin/deals/review-queue/${dealId}/regenerate`;
    document.getElementById('regenerate-target').value = target;
    form.submit();
}
</script>
@endsection

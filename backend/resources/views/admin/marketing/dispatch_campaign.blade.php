@extends('admin.layout')

@section('title', 'Dispatch Newsletter Broadcast')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Newsletter Dispatcher</h1>
            <p class="text-sm text-slate-500">Curate top discounts and broadcast a responsive deal digest to your active audience.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.marketing.templates.preview', 'promo-deal-digest') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 shadow-sm">
                <i data-lucide="eye" class="w-4 h-4 text-blue-500"></i> Preview Digest Design
            </a>
            <a href="{{ route('admin.marketing.subscribers') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 shadow-sm">
                <i data-lucide="users" class="w-4 h-4"></i> Audience ({{ number_format($subscriberCount) }})
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

    <form action="{{ route('admin.marketing.campaigns.trigger') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Campaign Subject & Subtitle -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="mail" class="w-4 h-4 text-red-500"></i> Email Subject & Header
            </h3>

            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Subject Line <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" required value="🔥 Today's Top Deals: Up to 80% Off Verified Savings!" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Preheader / Subheadline</label>
                    <input type="text" name="subheadline" value="Handpicked deals from Amazon, Flipkart & top stores — verified and live." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Deal Selection -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-blue-500"></i> Deals to Include
                </h3>
                <span class="text-xs text-slate-500">Select specific deals, or leave empty to auto-include top discounts.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($activeDeals as $deal)
                <label class="border border-slate-200 rounded-xl p-3 flex items-start gap-3 hover:bg-slate-50 transition-colors cursor-pointer group">
                    <input type="checkbox" name="deal_ids[]" value="{{ $deal->id }}" checked class="mt-1 rounded text-red-600 focus:ring-red-500 border-slate-300">
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-slate-800 line-clamp-1 group-hover:text-red-600">{{ $deal->title }}</div>
                        <div class="flex items-center justify-between mt-1 text-xs">
                            <span class="font-bold text-emerald-600">₹{{ number_format($deal->discounted_price) }}</span>
                            <span class="text-slate-400 font-semibold">{{ $deal->discount_percentage }}% off</span>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Audience & Dispatch Mode -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="users" class="w-4 h-4 text-purple-500"></i> Target Audience
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="border border-slate-200 rounded-xl p-4 flex items-start gap-3 hover:border-red-400 hover:bg-red-50/30 transition-all cursor-pointer">
                    <input type="radio" name="target_audience" value="admin_test" checked class="mt-1 text-red-600 focus:ring-red-500 border-slate-300">
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Send Test Broadcast to My Inbox</div>
                        <p class="text-xs text-slate-500 mt-0.5">Dispatches single email only to {{ auth()->user()->email ?? 'your email' }}. Safe to test anytime.</p>
                    </div>
                </label>

                <label class="border border-slate-200 rounded-xl p-4 flex items-start gap-3 hover:border-red-400 hover:bg-red-50/30 transition-all cursor-pointer">
                    <input type="radio" name="target_audience" value="subscribers" class="mt-1 text-red-600 focus:ring-red-500 border-slate-300">
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Broadcast to All Active Subscribers</div>
                        <p class="text-xs text-slate-500 mt-0.5">Will be queued to {{ number_format($subscriberCount) }} subscribers in database.</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-500/20 transition-all transform active:scale-95 flex items-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i> Dispatch Broadcast
            </button>
        </div>
    </form>

    <!-- Recent Broadcasts -->
    @if($recentCampaigns->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <h3 class="font-bold text-slate-800 text-base">Recent Dispatches</h3>
        <div class="divide-y divide-slate-100 text-sm">
            @foreach($recentCampaigns as $c)
            <div class="py-3 flex items-center justify-between">
                <div>
                    <div class="font-bold text-slate-800">{{ $c->name }}</div>
                    <div class="text-xs text-slate-400 font-mono">{{ $c->created_at->diffForHumans() }}</div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $c->status === 'Completed' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-700' }}">
                        {{ $c->status }}
                    </span>
                    <span class="text-xs text-slate-500">{{ $c->sent_count }} recipients</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

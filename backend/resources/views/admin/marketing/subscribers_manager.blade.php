@extends('admin.layout')

@section('title', 'Newsletter Subscribers')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Subscribers</h1>
            <p class="text-sm text-slate-500">Manage newsletter email recipients, subscription status, and export mailing lists.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.marketing.subscribers.export') }}" class="px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors inline-flex items-center gap-2 shadow-sm">
                <i data-lucide="download" class="w-4 h-4 text-slate-500"></i> Export CSV
            </a>
            <a href="{{ route('admin.marketing.campaigns') }}" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-500/20 transition-all inline-flex items-center gap-2 transform active:scale-95">
                <i data-lucide="send" class="w-4 h-4"></i> Send Broadcast
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-2 font-medium">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Audience</span>
                <div class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalSubscribers) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Subscribers</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($activeSubscribers) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Unsubscribed / Inactive</span>
                <div class="text-2xl font-black text-slate-500 mt-1">{{ number_format($inactiveSubscribers) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <i data-lucide="user-x" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-3 items-center justify-between">
        <form method="GET" action="{{ route('admin.marketing.subscribers') }}" class="flex gap-2 flex-1 max-w-lg">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by email or name..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
            </div>
            <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-colors">
                Search
            </button>
            @if(!empty($search))
                <a href="{{ route('admin.marketing.subscribers', ['status' => $status]) }}" class="px-3.5 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition-colors flex items-center">
                    Clear
                </a>
            @endif
        </form>

        <div class="flex items-center gap-1 text-xs">
            <a href="{{ route('admin.marketing.subscribers', ['status' => 'all', 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg font-bold {{ $status === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                All ({{ $totalSubscribers }})
            </a>
            <a href="{{ route('admin.marketing.subscribers', ['status' => 'active', 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg font-bold {{ $status === 'active' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Active ({{ $activeSubscribers }})
            </a>
            <a href="{{ route('admin.marketing.subscribers', ['status' => 'inactive', 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg font-bold {{ $status === 'inactive' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Inactive ({{ $inactiveSubscribers }})
            </a>
        </div>
    </div>

    <!-- Subscribers Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Subscriber</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Subscribed Date</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subscribers as $sub)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">{{ $sub->email }}</div>
                            @if(!empty($sub->first_name) || !empty($sub->last_name))
                                <div class="text-xs text-slate-400">{{ trim(($sub->first_name ?? '') . ' ' . ($sub->last_name ?? '')) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($sub->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Unsubscribed
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-xs font-mono">
                            {{ date('M d, Y H:i', strtotime($sub->created_at)) }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <form action="{{ route('admin.marketing.subscribers.toggle', $sub->id) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium transition-colors">
                                    {{ $sub->is_active ? 'Unsubscribe' : 'Reactivate' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.marketing.subscribers.destroy', $sub->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this subscriber?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition-colors">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                            No subscribers found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscribers->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $subscribers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

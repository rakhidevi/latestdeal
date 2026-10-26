@extends('admin.layout')

@section('title', 'Newsletter Subscribers')

@section('content')
<div x-data="{
    addModalOpen: false,
    importModalOpen: false,
    bulkEmails: '',
    get bulkCount() {
        if (!this.bulkEmails) return 0;
        const matches = this.bulkEmails.match(/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g);
        return matches ? (new Set(matches.map(x => x.toLowerCase()))).size : 0;
    }
}" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Subscribers</h1>
            <p class="text-sm text-slate-500">Manage newsletter email recipients, subscription status, and import/export lists.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button @click="addModalOpen = true" type="button" class="px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 shadow-sm">
                <i data-lucide="user-plus" class="w-4 h-4 text-emerald-600"></i> Add Subscriber
            </button>
            <button @click="importModalOpen = true" type="button" class="px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 shadow-sm">
                <i data-lucide="upload-cloud" class="w-4 h-4 text-blue-600"></i> Bulk Import
            </button>
            <a href="{{ route('admin.marketing.subscribers.export') }}" class="px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 shadow-sm">
                <i data-lucide="download" class="w-4 h-4 text-slate-500"></i> Export CSV
            </a>
            <a href="{{ route('admin.marketing.campaigns') }}" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-500/20 transition-all inline-flex items-center gap-1.5 transform active:scale-95">
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

    <!-- Modal: Add Single Subscriber -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <div x-show="addModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="addModalOpen = false"></div>
            
            <div x-show="addModalOpen" x-transition.scale.95 class="inline-block w-full max-w-md p-6 my-8 text-left bg-white rounded-2xl shadow-xl transform transition-all relative z-10 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-5 h-5 text-emerald-600"></i> Add Subscriber
                    </h3>
                    <button @click="addModalOpen = false" type="button" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('admin.marketing.subscribers.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required placeholder="shopper@example.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">First Name (Optional)</label>
                            <input type="text" name="first_name" placeholder="Pankaj" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Last Name (Optional)</label>
                            <input type="text" name="last_name" placeholder="Tiwari" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="addModalOpen = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md shadow-emerald-500/20">Add Subscriber</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Bulk Import Subscribers -->
    <div x-show="importModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <div x-show="importModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="importModalOpen = false"></div>
            
            <div x-show="importModalOpen" x-transition.scale.95 class="inline-block w-full max-w-xl p-6 my-8 text-left bg-white rounded-2xl shadow-xl transform transition-all relative z-10 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="upload-cloud" class="w-5 h-5 text-blue-600"></i> Bulk Import Subscribers
                    </h3>
                    <button @click="importModalOpen = false" type="button" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('admin.marketing.subscribers.bulk-import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">
                                Paste Email Addresses
                            </label>
                            <span class="text-xs text-slate-500">
                                Detected: <strong x-text="bulkCount" class="font-mono text-blue-600"></strong>
                            </span>
                        </div>
                        <textarea
                            name="emails_text"
                            x-model="bulkEmails"
                            rows="6"
                            placeholder="user1@example.com, user2@domain.com&#10;user3@outlook.com&#10;user4@gmail.com"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none"></textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Separated by commas, spaces, or new lines. Formats copied from Google Sheets / Excel are supported.</p>
                    </div>

                    <div class="pt-1 border-t border-slate-100">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Or Upload CSV / Text File (Optional)</label>
                        <input type="file" name="csv_file" accept=".csv,.txt" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    </div>

                    <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-xs text-blue-800 space-y-1">
                        <div class="font-semibold flex items-center gap-1.5">
                            <i data-lucide="info" class="w-4 h-4 text-blue-500"></i> Intelligent Import Engine
                        </div>
                        <p class="text-blue-700/80 leading-relaxed">All duplicate emails are automatically unified. Unregistered emails will be created with active status, and previously unsubscribed addresses will be safely reactivated.</p>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="importModalOpen = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md shadow-blue-500/20 flex items-center gap-1.5">
                            <i data-lucide="upload" class="w-4 h-4"></i> Start Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

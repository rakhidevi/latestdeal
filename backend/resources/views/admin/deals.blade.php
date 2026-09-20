@extends('admin.layout')

@section('title', 'Deal Catalog Management')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Deals Catalog</h1>
            <p class="text-sm text-slate-500">Manage, edit, publish, and monitor your affiliate deals inventory.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.deals.review-queue') }}" class="px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors inline-flex items-center gap-2 shadow-sm">
                <i data-lucide="check-square" class="w-4 h-4 text-orange-500"></i> Review Queue
            </a>
            <a href="{{ route('admin.deals.create') }}" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-500/20 transition-all inline-flex items-center gap-2 transform active:scale-95">
                <i data-lucide="plus" class="w-4 h-4"></i> Add New Deal
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-2 font-medium">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Search & Filter Controls -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.deals') }}" class="flex items-center gap-2 flex-1 max-w-xl">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by deal title, merchant, or URL..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all shadow-sm">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-slate-800 text-white text-sm font-semibold rounded-xl hover:bg-slate-900 transition-colors shadow-sm">
                Search
            </button>
            @if(!empty($search))
                <a href="{{ route('admin.deals', ['status' => $status]) }}" class="px-3.5 py-2.5 text-sm text-slate-600 hover:text-slate-900 border border-slate-200 bg-white rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                    Clear
                </a>
            @endif
        </form>

        <!-- Purge Illegal Deals Button -->
        @if($illegalCount > 0)
            <form action="{{ route('admin.deals.purge-illegal') }}" method="POST" onsubmit="return confirm('⚠️ This will PERMANENTLY DELETE {{ $illegalCount }} deals containing illegal/pirated content. Proceed?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-100 border border-red-200 text-red-700 text-sm font-semibold rounded-xl hover:bg-red-200 transition-colors">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600"></i>
                    Purge Illegal Deals ({{ $illegalCount }})
                </button>
            </form>
        @endif
    </div>

    <!-- Status Tabs -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <a href="{{ route('admin.deals', ['status' => 'active']) }}" class="{{ $status === 'active' ? 'border-red-500 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-3.5 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                Active / Published
                <span class="ml-1.5 {{ $status === 'active' ? 'bg-red-50 text-red-600 font-black' : 'bg-slate-100 text-slate-700' }} py-0.5 px-2.5 rounded-full text-xs">
                    {{ $counts['active'] }}
                </span>
            </a>

            <a href="{{ route('admin.deals', ['status' => 'pending']) }}" class="{{ $status === 'pending' ? 'border-red-500 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-3.5 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
                Pending / Draft
                <span class="ml-1.5 {{ $status === 'pending' ? 'bg-red-50 text-red-600 font-black' : 'bg-slate-100 text-slate-700' }} py-0.5 px-2.5 rounded-full text-xs">
                    {{ $counts['pending'] }}
                </span>
            </a>
            
            <a href="{{ route('admin.deals', ['status' => 'rejected']) }}" class="{{ $status === 'rejected' ? 'border-red-500 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-3.5 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                <i data-lucide="x-circle" class="w-4 h-4 text-red-500"></i>
                Rejected
                <span class="ml-1.5 {{ $status === 'rejected' ? 'bg-red-50 text-red-600 font-black' : 'bg-slate-100 text-slate-700' }} py-0.5 px-2.5 rounded-full text-xs">
                    {{ $counts['rejected'] }}
                </span>
            </a>
        </nav>
    </div>

    <!-- Deals Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($deals as $deal)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between hover:shadow-md transition-shadow group">
            <div class="space-y-3">
                <!-- Deal Header Info -->
                <div class="flex items-start justify-between gap-3">
                    <div class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-100 overflow-hidden flex-shrink-0 flex items-center justify-center p-1">
                        @if($deal->image_path)
                            <img src="{{ $deal->image_path }}" class="w-full h-full object-contain" onerror="this.src='https://via.placeholder.com/150?text=No+Image'">
                        @else
                            <i data-lucide="image" class="w-6 h-6 text-slate-300"></i>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-black text-slate-900 tracking-tight">₹{{ number_format($deal->discounted_price, 2) }}</div>
                        @if($deal->original_price > $deal->discounted_price)
                            <div class="text-xs text-slate-400 line-through">₹{{ number_format($deal->original_price, 2) }}</div>
                            <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-bold text-[10px]">
                                {{ $deal->discount_percentage }}% OFF
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Deal Title -->
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                            {{ $deal->merchant->name ?? 'Direct' }}
                        </span>
                        @if($deal->coupon_code)
                            <span class="text-[11px] font-mono font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">
                                CODE: {{ $deal->coupon_code }}
                            </span>
                        @endif
                        @if($deal->is_editor_pick)
                            <span class="text-[10px] font-black text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-0.5">
                                🔥 Pick
                            </span>
                        @endif
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm line-clamp-2 leading-snug group-hover:text-red-600 transition-colors" title="{{ $deal->title }}">
                        {{ $deal->title }}
                    </h3>
                </div>

                <!-- Meta Links -->
                <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                    <span>Clicks: <strong>{{ number_format($deal->clicks_count ?? $deal->total_clicks ?? 0) }}</strong></span>
                    <div class="flex items-center gap-2">
                        @if($deal->slug)
                            <a href="{{ url('/deal/' . $deal->slug) }}" target="_blank" class="hover:text-blue-600 flex items-center gap-0.5" title="View live on website">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> Site
                            </a>
                        @endif
                        <a href="{{ $deal->url }}" target="_blank" class="hover:text-blue-600 flex items-center gap-0.5" title="Open source store URL">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Source
                        </a>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Footer -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                <a href="{{ route('admin.deals.edit', $deal->id) }}" class="flex-1 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold text-center transition-colors flex items-center justify-center gap-1">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                </a>

                @if($status !== 'active')
                    <form action="{{ route('admin.deals.status', $deal->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="active">
                        <button type="submit" class="w-full py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition-colors flex items-center justify-center gap-1 shadow-sm">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i> Publish
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.deals.status', $deal->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="pending">
                        <button type="submit" class="w-full py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold transition-colors flex items-center justify-center gap-1 border border-amber-200">
                            <i data-lucide="archive" class="w-3.5 h-3.5"></i> Unpublish
                        </button>
                    </form>
                @endif

                <form action="{{ route('admin.deals.duplicate', $deal->id) }}" method="POST" title="Duplicate deal">
                    @csrf
                    <button type="submit" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    </button>
                </form>

                <form action="{{ route('admin.deals.destroy', $deal->id) }}" method="POST" onsubmit="return confirm('Permanently delete this deal?');" title="Delete deal">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center text-slate-400 bg-white rounded-2xl border border-slate-200 space-y-3">
            <i data-lucide="shopping-bag" class="w-12 h-12 mx-auto text-slate-300"></i>
            <p class="text-base font-semibold text-slate-600">No {{ $status }} deals found</p>
            <p class="text-xs text-slate-400 max-w-sm mx-auto">Try adjusting your search query or add a new deal manually.</p>
            <a href="{{ route('admin.deals.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i> Create First Deal
            </a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $deals->links() }}
    </div>
</div>
@endsection

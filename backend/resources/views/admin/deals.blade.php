@extends('admin.layout')

@section('title', 'Deal Approval Pipeline')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Manage Deals</h2>
            <p class="text-sm text-gray-500">Review draft and rejected deals before publication.</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('admin.deals', ['status' => 'pending']) }}" class="{{ $status === 'pending' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                Pending Approval
                <span class="ml-3 {{ $status === 'pending' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-900' }} py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">{{ $counts['pending'] }}</span>
            </a>
            
            <a href="{{ route('admin.deals', ['status' => 'active']) }}" class="{{ $status === 'active' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                Active / Published
                <span class="ml-3 {{ $status === 'active' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-900' }} py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">{{ $counts['active'] }}</span>
            </a>
            
            <a href="{{ route('admin.deals', ['status' => 'rejected']) }}" class="{{ $status === 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                Rejected
                <span class="ml-3 {{ $status === 'rejected' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-900' }} py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">{{ $counts['rejected'] }}</span>
            </a>
        </nav>
    </div>

    <!-- Deals Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
        @forelse($deals as $deal)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <img src="{{ asset($deal->image_path) }}" class="w-12 h-12 rounded object-cover border border-gray-100" onerror="this.src='https://via.placeholder.com/150'">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 uppercase tracking-wide">
                                {{ $status }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-lg text-red-600">₹{{ number_format($deal->discounted_price, 2) }}</p>
                        <p class="text-xs text-gray-500 line-through">₹{{ number_format($deal->original_price, 2) }}</p>
                    </div>
                </div>
                
                <a href="{{ route('deal.show', $deal->slug) }}" target="_blank" class="font-medium text-blue-600 hover:text-blue-800 hover:underline line-clamp-2 text-sm mt-1" title="{{ $deal->title }}">{{ $deal->title }}</a>
                <p class="mt-2 text-xs text-gray-500">
                    {{ $deal->merchant->name ?? 'Unknown Store' }} • 
                    @if($deal->original_price > 0)
                        {{ round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) }}% OFF • 
                    @endif
                    Score: {{ number_format($deal->ai_score ?? 0, 1) }}
                </p>
                
                <div class="mt-2 flex space-x-3 text-xs">
                    <a href="{{ route('deal.redirect', $deal->hash_id) }}" target="_blank" class="text-gray-500 hover:text-gray-900 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        Test Affiliate Link
                    </a>
                    <a href="{{ $deal->url }}" target="_blank" class="text-gray-500 hover:text-gray-900 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        Original URL
                    </a>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2">
                @if($status !== 'active')
                    <form action="{{ route('admin.deals.status', $deal->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="active">
                        <button type="submit" class="w-full justify-center inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Approve
                        </button>
                    </form>
                @endif
                
                @if($status !== 'rejected')
                    <form action="{{ route('admin.deals.status', $deal->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="w-full justify-center inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Reject
                        </button>
                    </form>
                @endif
                
                @if($status === 'active')
                    <form action="{{ route('admin.deals.status', $deal->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="pending">
                        <button type="submit" class="w-full justify-center inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            Draft
                        </button>
                    </form>
                @endif
                
                <form action="{{ route('admin.deals.destroy', $deal->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to permanently delete this deal?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full justify-center inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-gray-500 bg-white rounded-xl border border-gray-200">
            No {{ $status }} deals found.
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $deals->links() }}
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'My Dashboard')
@section('content')
<div class="max-w-6xl mx-auto mt-10">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Welcome, {{ $user->name }}</h1>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="text-gray-600 hover:text-gray-900 font-medium">Logout</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h2 class="text-xl font-bold mb-4">Saved Deals</h2>
            @if($savedDeals->isEmpty())
                <p class="text-gray-500">You haven't saved any deals yet.</p>
            @else
                <div class="space-y-4">
                    @foreach($savedDeals as $deal)
                        <div class="flex items-center justify-between border-b pb-2">
                            <a href="{{ route('deal.show', $deal->slug) }}" class="font-medium hover:text-primary">{{ $deal->title }}</a>
                            <span class="text-accent font-bold">₹{{ $deal->discounted_price }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Price Alerts</h2>
                <button x-data @click="$dispatch('open-alert-modal')" class="text-sm bg-red-100 text-red-700 px-3 py-1 rounded">New Alert</button>
            </div>
            @if($priceAlerts->isEmpty())
                <p class="text-gray-500">You don't have any active price alerts.</p>
            @else
                <div class="space-y-4">
                    @foreach($priceAlerts as $alert)
                        <div class="flex items-center justify-between border-b pb-2">
                            <div>
                                <span class="font-medium">Keyword:</span> {{ $alert->keyword }}
                                <div class="text-xs text-gray-500">Target: ₹{{ $alert->target_price }}</div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="text-xs px-2 py-1 rounded {{ $alert->is_fulfilled ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $alert->is_fulfilled ? 'Triggered' : 'Active' }}
                                </span>
                                <form action="{{ route('price-alerts.destroy', $alert->id) }}" method="POST" onsubmit="return confirm('Delete this alert?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

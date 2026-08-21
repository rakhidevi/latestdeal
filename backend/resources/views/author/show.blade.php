@extends('layouts.app')

@section('title', 'Author: ' . $author->user->name . ' - LatestDeal')
@section('description', 'Read deals and buying advice from ' . $author->user->name . ' on LatestDeal. ' . \Illuminate\Support\Str::limit($author->bio, 120))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    
    <!-- Author Profile Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 mb-8">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            <div class="shrink-0">
                <img src="{{ $author->photo_url }}" alt="{{ $author->user->name }}" class="w-32 h-32 rounded-full object-cover border-4 border-gray-50 dark:border-gray-700 shadow-sm">
            </div>
            
            <div class="text-center md:text-left flex-grow">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $author->user->name }}</h1>
                
                @if($author->expertise)
                <div class="inline-block bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-3 py-1 rounded-full text-sm font-semibold mb-4">
                    Expertise: {{ $author->expertise }}
                </div>
                @endif
                
                <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300">
                    <p>{{ $author->bio }}</p>
                </div>
                
                @if($author->social_links)
                <div class="flex justify-center md:justify-start gap-4 mt-6">
                    @foreach($author->social_links as $platform => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-blue-600 transition-colors">
                            <span class="sr-only">{{ ucfirst($platform) }}</span>
                            <!-- Generic Link Icon, you can replace with FontAwesome or specific SVGs -->
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Author's Deals Feed -->
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Recent Deals Reviewed by {{ explode(' ', trim($author->user->name))[0] }}</h2>
    
    @if($deals->isEmpty())
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <p class="text-gray-500 dark:text-gray-400">No deals published yet.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($deals as $deal)
                <x-deal-card :deal="$deal" />
            @endforeach
        </div>
        
        <div class="mt-8">
            {{ $deals->links() }}
        </div>
    @endif
</div>
@endsection

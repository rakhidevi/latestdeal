@extends('layouts.app')

@section('meta')
    <title>{{ $seoMeta['title'] }}</title>
    <meta name="description" content="{{ $seoMeta['description'] }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] }}">
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-16">
        <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tight mb-4">
            Shopping Guides & Analysis
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
            We don't just track prices. Our editorial team analyzes the data to tell you when to buy, what to avoid, and which deals are actually worth your money.
        </p>
    </div>

    @if($articles->isEmpty())
        <div class="text-center py-16 bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15M9 11l3 3m0 0l3-3m-3 3V8" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No guides published yet</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Our editorial team is working on the first batch of shopping guides. Check back soon!</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($articles as $article)
                <article class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden hover:shadow-md transition-shadow">
                    @if($article->featured_image)
                        <a href="{{ route('articles.show', $article->slug) }}">
                            <img src="{{ $article->featured_image }}" alt="{{ $article->title }}" class="w-full h-48 object-cover">
                        </a>
                    @endif
                    <div class="p-6">
                        @if($article->category)
                            <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">{{ $article->category->name }}</span>
                        @endif
                        <a href="{{ route('articles.show', $article->slug) }}" class="block mt-2">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white leading-tight hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                {{ $article->title }}
                            </h2>
                        </a>
                        <p class="mt-3 text-gray-600 dark:text-gray-400 text-sm line-clamp-3">
                            {{ $article->summary }}
                        </p>
                        <div class="mt-6 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($article->author && $article->author->avatar_url)
                                    <img src="{{ $article->author->avatar_url }}" alt="{{ $article->author->name }}" class="w-8 h-8 rounded-full">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold text-xs">
                                        {{ substr($article->author->name ?? 'A', 0, 1) }}
                                    </div>
                                @endif
                                <div class="text-sm">
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $article->author->name ?? 'Editorial Team' }}</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-500">{{ $article->published_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <div class="mt-12">
        {{ $articles->links() }}
    </div>
</div>
@endsection

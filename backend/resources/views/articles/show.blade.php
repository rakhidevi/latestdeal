@extends('layouts.app')

@section('meta')
    <title>{{ $seoMeta['title'] }}</title>
    <meta name="description" content="{{ $seoMeta['description'] }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] }}">
@endsection

@section('content')
<article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Article Header -->
    <header class="text-center mb-12">
        @if($article->category)
            <span class="text-sm font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">{{ $article->category->name }}</span>
        @endif
        
        <h1 class="mt-4 text-3xl md:text-5xl font-black text-gray-900 dark:text-white leading-tight">
            {{ $article->title }}
        </h1>
        
        @if($article->summary)
        <p class="mt-6 text-xl text-gray-600 dark:text-gray-400 leading-relaxed max-w-3xl mx-auto">
            {{ $article->summary }}
        </p>
        @endif
        
        <div class="mt-8 flex items-center justify-center gap-4 text-sm text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-2">
                @if($article->author && $article->author->avatar_url)
                    <img src="{{ $article->author->avatar_url }}" alt="{{ $article->author->name }}" class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-800 shadow-sm">
                @endif
                <div class="text-left">
                    <span class="block font-bold text-gray-900 dark:text-gray-200">{{ $article->author?->name ?? 'LatestDeal Editorial' }}</span>
                    <span class="block text-xs">Published on {{ $article->published_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </header>

    @if($article->featured_image)
        <div class="mb-12 rounded-3xl overflow-hidden shadow-lg">
            <img src="{{ $article->featured_image }}" alt="{{ $article->title }}" class="w-full h-auto object-cover max-h-[600px]">
        </div>
    @endif

    <!-- Content Body -->
    <div class="prose prose-lg prose-indigo dark:prose-invert max-w-none">
        {!! $article->content !!}
    </div>
    
    <!-- AdSense for Content -->
    <div class="mt-12 mb-12">
        <x-ad-banner slot="article-bottom" />
    </div>

    <!-- Tags -->
    @if($article->tags && $article->tags->count() > 0)
        <div class="mt-12 pt-8 border-t border-gray-200 dark:border-slate-800 flex flex-wrap gap-2">
            <span class="text-sm font-bold text-gray-900 dark:text-white mr-2 flex items-center">Tags:</span>
            @foreach($article->tags as $tag)
                <span class="bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-300 text-xs font-semibold px-3 py-1 rounded-full">
                    {{ $tag->name }}
                </span>
            @endforeach
        </div>
    @endif
    
    <!-- Author Bio -->
    @if($article->author && $article->author->bio)
        <div class="mt-12 bg-gray-50 dark:bg-slate-800/50 rounded-3xl p-8 border border-gray-100 dark:border-slate-700/50 flex gap-6 items-start">
            @if($article->author->avatar_url)
                <img src="{{ $article->author->avatar_url }}" alt="{{ $article->author->name }}" class="w-20 h-20 rounded-full shrink-0">
            @endif
            <div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">About {{ $article->author->name }}</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                    {{ $article->author->bio }}
                </p>
                <div class="mt-4">
                    <a href="{{ route('editorial.policy') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Read our Editorial Policy &rarr;</a>
                </div>
            </div>
        </div>
    @endif
</article>
@endsection

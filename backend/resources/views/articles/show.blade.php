@extends('layouts.app')

@section('meta')
    <title>{{ $seoMeta['title'] }}</title>
    <meta name="description" content="{{ $seoMeta['description'] }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] }}">
@endsection

@section('content')
<div class="py-6 sm:py-10 font-sans">
    <article class="max-w-4xl mx-auto px-4 sm:px-6">
        
        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 dark:text-slate-500 mb-6 flex-wrap" aria-label="Breadcrumb">
            <a href="/" class="hover:text-red-600 dark:hover:text-red-400 transition-colors">Home</a>
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('articles.index') }}" class="hover:text-red-600 dark:hover:text-red-400 transition-colors">Buying Guides</a>
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 dark:text-white font-bold truncate max-w-xs sm:max-w-md" aria-current="page">{{ $article->title }}</span>
        </nav>

        <!-- Article Header -->
        <header class="text-center mb-10 sm:mb-12">
            @if($article->category)
                <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                    {{ $article->category->name }}
                </span>
            @endif
            
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-4">
                {{ $article->title }}
            </h1>
            
            @if($article->summary)
                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-400 font-medium leading-relaxed max-w-3xl mx-auto">
                    {{ $article->summary }}
                </p>
            @endif
            
            <div class="mt-6 flex items-center justify-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-2">
                    @if($article->author && $article->author->avatar_url)
                        <img src="{{ $article->author->avatar_url }}" alt="{{ $article->author->name }}" class="w-7 h-7 rounded-full object-cover">
                    @else
                        <div class="w-7 h-7 rounded-full bg-red-100 dark:bg-red-950/60 flex items-center justify-center text-red-600 dark:text-red-400 font-bold text-xs">
                            {{ substr($article->author->name ?? 'E', 0, 1) }}
                        </div>
                    @endif
                    <span class="font-bold text-slate-900 dark:text-slate-200">{{ $article->author?->name ?? 'LatestDeal Editorial' }}</span>
                </div>
                <span>•</span>
                <span>Published {{ $article->published_at ? $article->published_at->format('M d, Y') : date('M d, Y') }}</span>
                <span>•</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Fact-Checked</span>
            </div>
        </header>

        @if($article->featured_image)
            <div class="mb-10 rounded-3xl overflow-hidden shadow-lg border border-slate-200/80 dark:border-slate-800">
                <img src="{{ $article->featured_image }}" alt="{{ $article->title }}" class="w-full h-auto object-cover max-h-[500px]">
            </div>
        @endif

        <!-- Card Container with Content Body -->
        <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-10 md:p-12 shadow-xl shadow-slate-200/40 dark:shadow-none mb-10">
            <div class="prose prose-slate dark:prose-invert max-w-none prose-headings:font-black prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:tracking-tight prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-p:leading-relaxed prose-li:text-slate-600 dark:prose-li:text-slate-300 prose-strong:text-slate-900 dark:prose-strong:text-white prose-a:text-red-600 dark:prose-a:text-red-400 hover:prose-a:underline">
                {!! $article->content !!}
            </div>
        </div>
        
        <!-- AdSense Banner -->
        <div class="my-10">
            <x-ad-banner slot="article-bottom" />
        </div>

        <!-- Tags -->
        @if($article->tags && $article->tags->count() > 0)
            <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mr-2">Tags:</span>
                @foreach($article->tags as $tag)
                    <span class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold px-3 py-1 rounded-full">
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        @endif
        
        <!-- Author Bio Card -->
        @if($article->author && $article->author->bio)
            <div class="mt-10 bg-white dark:bg-slate-900/90 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row gap-5 items-start">
                @if($article->author->avatar_url)
                    <img src="{{ $article->author->avatar_url }}" alt="{{ $article->author->name }}" class="w-16 h-16 rounded-2xl object-cover shrink-0">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-red-100 dark:bg-red-950/60 flex items-center justify-center text-red-600 dark:text-red-400 font-bold text-xl shrink-0">
                        {{ substr($article->author->name ?? 'E', 0, 1) }}
                    </div>
                @endif
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white mb-1">Written by {{ $article->author->name }}</h3>
                    <p class="text-slate-600 dark:text-slate-300 text-xs sm:text-sm leading-relaxed">
                        {{ $article->author->bio }}
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('editorial.policy') }}" class="text-xs font-bold text-red-600 dark:text-red-400 hover:underline">Read our Editorial Policy →</a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="mt-10 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Read more from our editorial team:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('articles.index') }}" class="text-red-600 dark:text-red-400 hover:underline">All Buying Guides →</a>
                <a href="{{ route('editorial.team') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Team →</a>
                <a href="{{ route('corrections.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Corrections Policy →</a>
            </div>
        </div>

    </article>
</div>
@endsection

@extends('layouts.app')

@section('meta')
    <title>{{ $seoMeta['title'] }}</title>
    <meta name="description" content="{{ $seoMeta['description'] }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] }}">
@endsection

@section('content')
<div class="py-6 sm:py-10 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 dark:text-slate-500 mb-6" aria-label="Breadcrumb">
            <a href="/" class="hover:text-red-600 dark:hover:text-red-400 transition-colors">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-600 dark:text-slate-400">Platform</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 dark:text-white font-bold" aria-current="page">Buying Guides & Tips</span>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Shopping Intelligence & Guides
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                Buying Guides & <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">Analysis</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed">
                We don't just track price drops. Our editorial desk analyzes historical trends to tell you when to buy, what to avoid, and which deals are genuinely worth your money.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                <span>Curated by LatestDeal Editors</span>
                <span>•</span>
                <span>Updated Weekly</span>
            </div>
        </div>

        @if($articles->isEmpty())
            <div class="text-center py-16 bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm max-w-xl mx-auto">
                <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15M9 11l3 3m0 0l3-3m-3 3V8" />
                </svg>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">No guides published yet</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Our editorial team is crafting new evergreen shopping guides. Check back soon!</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                @foreach($articles as $article)
                    <article class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xl shadow-slate-200/40 dark:shadow-none hover:-translate-y-1 transition-all flex flex-col group">
                        @if($article->featured_image)
                            <a href="{{ route('articles.show', $article->slug) }}" class="block overflow-hidden h-48 bg-slate-100 dark:bg-slate-800">
                                <img src="{{ $article->featured_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </a>
                        @endif
                        <div class="p-6 sm:p-7 flex-1 flex flex-col">
                            @if($article->category)
                                <span class="text-xs font-black text-red-600 dark:text-red-400 uppercase tracking-widest mb-2 block">{{ $article->category->name }}</span>
                            @endif
                            <a href="{{ route('articles.show', $article->slug) }}" class="block">
                                <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white leading-tight group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">
                                    {{ $article->title }}
                                </h2>
                            </a>
                            <p class="mt-3 text-slate-600 dark:text-slate-300 text-xs sm:text-sm line-clamp-3 leading-relaxed flex-1">
                                {{ $article->summary }}
                            </p>
                            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    @if($article->author && $article->author->avatar_url)
                                        <img src="{{ $article->author->avatar_url }}" alt="{{ $article->author->name }}" class="w-6 h-6 rounded-full object-cover">
                                    @else
                                        <div class="w-6 h-6 rounded-full bg-red-100 dark:bg-red-950/60 flex items-center justify-center text-red-600 dark:text-red-400 font-bold text-[10px]">
                                            {{ substr($article->author->name ?? 'E', 0, 1) }}
                                        </div>
                                    @endif
                                    <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $article->author->name ?? 'Editorial Desk' }}</span>
                                </div>
                                <span class="text-slate-400">{{ $article->published_at ? $article->published_at->format('M d, Y') : date('M d, Y') }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="mt-12">
            {{ $articles->links() }}
        </div>

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="mt-12 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Discover our methodology and team:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('editorial.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Policy →</a>
                <a href="{{ route('editorial.team') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Team →</a>
                <a href="{{ route('how.it.works') }}" class="text-red-600 dark:text-red-400 hover:underline">How It Works →</a>
                <a href="{{ route('corrections.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Corrections Policy →</a>
            </div>
        </div>

    </div>
</div>
@endsection

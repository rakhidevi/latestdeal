@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Breadcrumbs -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            @foreach($breadcrumbs as $breadcrumb)
                <li class="inline-flex items-center">
                    @if(!$loop->last)
                        <a href="{{ $breadcrumb['url'] }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                            {{ $breadcrumb['name'] }}
                        </a>
                        <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                    @else
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $breadcrumb['name'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    <div class="mb-10">
        <h1 class="text-4xl font-black text-gray-900 dark:text-white mb-4 tracking-tight">
            {{ $type ? ucfirst($type) . 's' : 'Shopping Intelligence Hub' }}
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">
            Expert insights, buying guides, and deep-dives to help you shop smarter.
        </p>
    </div>

    @if(empty($articles))
        <div class="bg-gray-50 dark:bg-slate-800 rounded-3xl p-10 text-center border border-gray-100 dark:border-slate-700">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Content Yet</h3>
            <p class="text-gray-500 dark:text-gray-400">Check back soon for expert {{ $type ? $type . 's' : 'articles' }}.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($articles as $article)
                <article class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col h-full group">
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-black uppercase tracking-wider px-3 py-1 rounded-full">
                                {{ ucfirst($article->contentType) }}
                            </span>
                            @if($article->readingTime)
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                    {{ $article->readingTime }} min read
                                </span>
                            @endif
                        </div>
                        
                        <a href="{{ route($article->contentType == 'glossary' ? 'glossary.show' : 'editorial.show', ['type' => $article->contentType, 'slug' => $article->slug]) }}" class="block mb-3 flex-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            <h2 class="text-xl font-black text-gray-900 dark:text-white leading-snug">
                                {{ $article->title }}
                            </h2>
                        </a>
                        
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-6 line-clamp-3">
                            {{ $article->description }}
                        </p>
                        
                        <div class="mt-auto flex items-center justify-between pt-4 border-t border-gray-100 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold text-xs">
                                    {{ substr($article->author, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white">{{ $article->author }}</p>
                                    @if($article->publishedAt)
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">{{ date('M d, Y', strtotime($article->publishedAt)) }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection

@extends('layouts.app')

@push('styles')
<style>
    /* Editorial Markdown Styling */
    .prose h2 { font-weight: 900; font-size: 1.875rem; margin-top: 2rem; margin-bottom: 1rem; color: #111827; }
    .prose h3 { font-weight: 800; font-size: 1.5rem; margin-top: 1.5rem; margin-bottom: 0.75rem; color: #1f2937; }
    .dark .prose h2 { color: #f9fafb; }
    .dark .prose h3 { color: #f3f4f6; }
    .prose p { margin-bottom: 1.25rem; line-height: 1.75; color: #4b5563; }
    .dark .prose p { color: #9ca3af; }
    .prose ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1.25rem; color: #4b5563; }
    .dark .prose ul { color: #9ca3af; }
    .prose li { margin-bottom: 0.5rem; }
    .prose strong { font-weight: 700; color: #111827; }
    .dark .prose strong { color: #e5e7eb; }
    .prose a { color: #2563eb; text-decoration: underline; }
    .dark .prose a { color: #60a5fa; }
    .prose blockquote { border-left: 4px solid #e5e7eb; padding-left: 1rem; font-style: italic; color: #6b7280; }
    .dark .prose blockquote { border-left-color: #374151; color: #9ca3af; }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
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

    <!-- Article Header -->
    <header class="mb-10 text-center md:text-left">
        <div class="flex items-center justify-center md:justify-start gap-2 mb-6">
            <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-sm font-black uppercase tracking-wider px-3 py-1 rounded-full">
                {{ ucfirst($article->contentType) }}
            </span>
            @if($article->readingTime)
                <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    {{ $article->readingTime }} min read
                </span>
            @endif
            @if(!empty($article->tags))
                @foreach($article->tags as $tag)
                    <span class="text-sm text-gray-400 dark:text-gray-500 font-medium hidden sm:inline-block">#{{ $tag }}</span>
                @endforeach
            @endif
        </div>
        
        <h1 class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white mb-6 leading-tight tracking-tight">
            {{ $article->title }}
        </h1>
        
        <p class="text-lg md:text-xl text-gray-600 dark:text-gray-400 leading-relaxed max-w-3xl mb-8">
            {{ $article->description }}
        </p>

        <!-- Author / Metadata -->
        <div class="flex items-center justify-center md:justify-start gap-4 pb-8 border-b border-gray-100 dark:border-slate-800">
            <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                {{ substr($article->author, 0, 1) }}
            </div>
            <div class="text-left">
                <a href="{{ route('editorial.team') }}" class="text-base font-bold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">{{ $article->author }}</a>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if($article->updatedAt)
                        Updated {{ date('M d, Y', strtotime($article->updatedAt)) }}
                    @elseif($article->publishedAt)
                        Published {{ date('M d, Y', strtotime($article->publishedAt)) }}
                    @endif
                </p>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <article class="prose dark:prose-invert max-w-none mb-16">
        {!! $article->content !!}
    </article>

    <!-- Related Articles -->
    @if(count($relatedArticles) > 0)
        <div class="mt-16 pt-10 border-t border-gray-200 dark:border-slate-800">
            <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-6">Continue Reading</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @foreach($relatedArticles as $related)
                    <a href="{{ route($related->contentType == 'glossary' ? 'glossary.show' : 'editorial.show', ['type' => $related->contentType, 'slug' => $related->slug]) }}" class="block p-5 bg-gray-50 dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 hover:shadow-md transition">
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ $related->title }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $related->description }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

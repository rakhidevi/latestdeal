@extends('layouts.app')

@section('meta')
    <title>Page Not Found | LatestDeal.in</title>
    <meta name="robots" content="noindex, follow">
@endsection

@section('content')
<div class="min-h-[70vh] flex flex-col items-center justify-center text-center px-4">
    <div class="relative w-48 h-48 mb-8">
        <div class="absolute inset-0 bg-red-100 dark:bg-red-900/30 rounded-full animate-pulse filter blur-xl"></div>
        <svg class="w-full h-full text-red-500 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    </div>
    
    <h1 class="text-6xl font-black text-gray-900 dark:text-white mb-4 tracking-tighter">404</h1>
    <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-300 mb-6">Oops! We couldn't find that deal.</h2>
    <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">
        The page or deal you are looking for might have expired, been removed, or is temporarily unavailable. 
        But don't worry, there are thousands of other live deals waiting for you!
    </p>
    
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="/" class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg hover:shadow-red-500/30 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Return Home
        </a>
        <a href="/?sort=discount" class="px-8 py-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 font-bold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Browse Today's Deals
        </a>
    </div>
</div>
@endsection

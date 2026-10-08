@extends('layouts.app')

@section('meta')
    <title>Editorial Team | LatestDeal.in</title>
    <meta name="description" content="Meet the expert deal hunters, tech reviewers, and editors behind LatestDeal.in who verify and curate every recommendation.">
    <link rel="canonical" href="{{ url('/editorial-team') }}">
@endsection

@section('content')
<div class="py-6 sm:py-10 font-sans">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        
        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 dark:text-slate-500 mb-6" aria-label="Breadcrumb">
            <a href="/" class="hover:text-red-600 dark:hover:text-red-400 transition-colors">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-600 dark:text-slate-400">Platform</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 dark:text-white font-bold" aria-current="page">Editorial Team</span>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                The Humans Behind The AI
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                Meet Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">Editorial Team</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed">
                While our autonomous crawlers discover price fluctuations, our experienced editors verify every claim, analyze historical data, and ensure unbiased recommendations.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                <span>Independent Editorial Desk</span>
                <span>•</span>
                <span>Updated {{ date('F Y') }}</span>
            </div>
        </div>

        <!-- Team Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 mb-12">
            
            <!-- Editor 1 -->
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/40 dark:shadow-none text-center flex flex-col items-center group hover:-translate-y-1 transition-all">
                <div class="w-24 h-24 rounded-full bg-indigo-50 dark:bg-indigo-950/60 p-1.5 mb-5 border border-indigo-100 dark:border-indigo-900/40">
                    <img src="https://ui-avatars.com/api/?name=Arjun+M&background=4f46e5&color=ffffff&size=256" alt="Arjun M" class="w-full h-full rounded-full object-cover">
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">Arjun M.</h3>
                <span class="inline-block px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-wider text-[11px] mb-3">
                    Lead Tech Editor
                </span>
                <p class="text-slate-600 dark:text-slate-300 text-xs sm:text-sm leading-relaxed mb-6">
                    With over 8 years of experience evaluating consumer electronics, Arjun heads tech deal verification, ensuring smartphone, PC, and audio deals meet strict performance benchmarks.
                </p>
                <div class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-800 w-full">
                    <span class="text-[11px] text-slate-400 font-semibold block">Focus Areas:</span>
                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">Smartphones, Laptops, Audio Gear</span>
                </div>
            </div>

            <!-- Editor 2 -->
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/40 dark:shadow-none text-center flex flex-col items-center group hover:-translate-y-1 transition-all">
                <div class="w-24 h-24 rounded-full bg-emerald-50 dark:bg-emerald-950/60 p-1.5 mb-5 border border-emerald-100 dark:border-emerald-900/40">
                    <img src="https://ui-avatars.com/api/?name=Priya+S&background=059669&color=ffffff&size=256" alt="Priya S" class="w-full h-full rounded-full object-cover">
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">Priya S.</h3>
                <span class="inline-block px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wider text-[11px] mb-3">
                    Home & Appliances Editor
                </span>
                <p class="text-slate-600 dark:text-slate-300 text-xs sm:text-sm leading-relaxed mb-6">
                    Priya brings a critical consumer lens to kitchen appliances, smart home gadgets, and lifestyle goods, relentlessly auditing manufacturer warranties and energy ratings.
                </p>
                <div class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-800 w-full">
                    <span class="text-[11px] text-slate-400 font-semibold block">Focus Areas:</span>
                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">Kitchenware, Smart Home, Appliances</span>
                </div>
            </div>

            <!-- Editor 3 -->
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/40 dark:shadow-none text-center flex flex-col items-center group hover:-translate-y-1 transition-all">
                <div class="w-24 h-24 rounded-full bg-rose-50 dark:bg-rose-950/60 p-1.5 mb-5 border border-rose-100 dark:border-rose-900/40">
                    <img src="https://ui-avatars.com/api/?name=Rahul+K&background=e11d48&color=ffffff&size=256" alt="Rahul K" class="w-full h-full rounded-full object-cover">
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">Rahul K.</h3>
                <span class="inline-block px-3 py-1 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-bold uppercase tracking-wider text-[11px] mb-3">
                    Data & Price Intelligence Lead
                </span>
                <p class="text-slate-600 dark:text-slate-300 text-xs sm:text-sm leading-relaxed mb-6">
                    Rahul oversees our autonomous price tracking pipelines and SERP intelligence services. He tunes algorithmic price thresholds to ensure only genuine price drops reach publication.
                </p>
                <div class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-800 w-full">
                    <span class="text-[11px] text-slate-400 font-semibold block">Focus Areas:</span>
                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">Price Analytics, Retail Scrapers, Algorithms</span>
                </div>
            </div>

        </div>

        <!-- Editorial Charter Card -->
        <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-10 shadow-xl shadow-slate-200/40 dark:shadow-none">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-4">Our Editorial Pledge</h2>
            <div class="prose prose-slate dark:prose-invert max-w-none text-sm sm:text-base prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-p:leading-relaxed">
                <p>
                    Every guide, review, and deal recommendation published on LatestDeal follows our public <a href="{{ route('editorial.policy') }}" class="text-red-600 dark:text-red-400 font-bold hover:underline">Editorial Policy</a>. We maintain strict separation between editorial evaluations and business partnerships. Our team does not accept sponsored compensation to endorse or elevate specific products over superior alternatives.
                </p>
            </div>
        </div>

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="mt-10 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Learn more about our editorial processes:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('editorial.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Policy →</a>
                <a href="{{ route('corrections.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Corrections Policy →</a>
                <a href="{{ route('how.it.works') }}" class="text-red-600 dark:text-red-400 hover:underline">How It Works →</a>
                <a href="{{ route('contact') }}" class="text-red-600 dark:text-red-400 hover:underline">Contact Editorial Desk →</a>
            </div>
        </div>

    </div>
</div>
@endsection

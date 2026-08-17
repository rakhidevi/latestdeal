@extends('layouts.app')

@section('meta')
    <title>Editorial Team | LatestDeal.in</title>
    <meta name="description" content="Meet the expert deal hunters, tech enthusiasts, and editors behind LatestDeal.in who verify and curate our deals.">
    <link rel="canonical" href="{{ url('/editorial-team') }}">
@endsection

@section('content')
<div class="relative min-h-screen pt-24 pb-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <div class="text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-sm font-bold tracking-widest uppercase mb-4 shadow-sm">
                The Humans Behind The AI
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-slate-800 tracking-tight mb-4">
                Meet Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600">Editorial Team</span>
            </h1>
            <p class="text-lg text-slate-500 font-medium max-w-2xl mx-auto">
                While our autonomous bots discover price drops, our expert team verifies them, analyzes historical trends, and writes the buying guides you trust.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Editor 1 -->
            <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 shadow-xl shadow-slate-200/50 text-center flex flex-col items-center group hover:-translate-y-2 transition-transform">
                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 p-2 mb-6">
                    <img src="https://ui-avatars.com/api/?name=Arjun+M&background=c7d2fe&color=3730a3&size=256" alt="Arjun M" class="w-full h-full rounded-full object-cover">
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-1">Arjun M.</h3>
                <p class="text-indigo-600 font-bold uppercase tracking-wider text-xs mb-4">Lead Tech Editor</p>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    With over 8 years of experience reviewing consumer electronics, Arjun leads our tech deal verification. He ensures that every smartphone or laptop deal featured is genuinely worth your money.
                </p>
                <div class="mt-auto pt-6 border-t border-slate-100 w-full">
                    <span class="text-xs text-slate-400 font-medium">Expertise: Smartphones, Laptops, Audio</span>
                </div>
            </div>

            <!-- Editor 2 -->
            <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 shadow-xl shadow-slate-200/50 text-center flex flex-col items-center group hover:-translate-y-2 transition-transform">
                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-emerald-100 to-teal-100 p-2 mb-6">
                    <img src="https://ui-avatars.com/api/?name=Priya+S&background=a7f3d0&color=065f46&size=256" alt="Priya S" class="w-full h-full rounded-full object-cover">
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-1">Priya S.</h3>
                <p class="text-emerald-600 font-bold uppercase tracking-wider text-xs mb-4">Home & Lifestyle Editor</p>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    Priya brings a critical eye to home appliances and lifestyle products. She meticulously checks historical pricing during major sale events to filter out artificially inflated MRPs.
                </p>
                <div class="mt-auto pt-6 border-t border-slate-100 w-full">
                    <span class="text-xs text-slate-400 font-medium">Expertise: Home Appliances, Kitchen, Fitness</span>
                </div>
            </div>

            <!-- Editor 3 -->
            <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 shadow-xl shadow-slate-200/50 text-center flex flex-col items-center group hover:-translate-y-2 transition-transform">
                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-orange-100 to-red-100 p-2 mb-6">
                    <img src="https://ui-avatars.com/api/?name=Rahul+K&background=fed7aa&color=9a3412&size=256" alt="Rahul K" class="w-full h-full rounded-full object-cover">
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-1">Rahul K.</h3>
                <p class="text-orange-600 font-bold uppercase tracking-wider text-xs mb-4">AI Deal Analyst</p>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    Rahul sits at the intersection of our Python scraping bots and our editorial guidelines. He tunes our AI Trust Scoring algorithm to ensure it aligns with strict quality standards.
                </p>
                <div class="mt-auto pt-6 border-t border-slate-100 w-full">
                    <span class="text-xs text-slate-400 font-medium">Expertise: Data Science, Price Volatility, Algorithms</span>
                </div>
            </div>

        </div>
        
        <div class="mt-20 bg-slate-900 rounded-3xl p-10 md:p-12 text-center text-white shadow-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            <div class="relative z-10">
                <h2 class="text-3xl font-black mb-4">Our AI-Assisted Workflow</h2>
                <p class="text-slate-400 text-lg max-w-3xl mx-auto mb-8">
                    We process over 100,000 price changes daily. Our bots filter this down to the top 1% of genuine price drops. Our AI then analyzes the seller and product history, giving it a Trust Score. Finally, our human editors step in to write the buying advice and publish the deal.
                </p>
                <a href="{{ route('editorial.policy') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-slate-900 hover:bg-gray-100 font-bold rounded-xl shadow-lg transition-all">
                    Read Our Editorial Policy
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('meta')
    <title>Contact Us | LatestDeal.in</title>
    <meta name="description" content="Get in touch with the LatestDeal team for editorial support, deal corrections, business inquiries, or general questions.">
    <link rel="canonical" href="{{ url('/contact') }}">
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
            <span class="text-slate-900 dark:text-white font-bold" aria-current="page">Contact Us</span>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Direct Support & Editorial Desk
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                Get in <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">Touch</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed">
                Have a question, feedback, deal correction, or partnership inquiry? Our team is here to assist you.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                <span>Typical response time: Within 24 business hours</span>
                <span>•</span>
                <span>Operating IST</span>
            </div>
        </div>

        <!-- Contact Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 mb-12">
            
            <!-- Support & Editorial Channels Card -->
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/40 dark:shadow-none flex flex-col justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mb-6">Contact Channels</h2>
                    
                    <div class="space-y-6">
                        <!-- General Support -->
                        <div class="flex items-start gap-4">
                            <div class="p-3 rounded-2xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-sm">General Support & Deal Inquiries</h4>
                                <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5 leading-relaxed">
                                    For inquiries regarding deal links, price updates, or account support:
                                </p>
                                <a href="mailto:support@latestdeal.in" class="text-sm font-bold text-red-600 dark:text-red-400 hover:underline mt-1 inline-block">support@latestdeal.in</a>
                            </div>
                        </div>

                        <!-- Editorial Desk -->
                        <div class="flex items-start gap-4">
                            <div class="p-3 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-sm">Editorial & Corrections Desk</h4>
                                <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5 leading-relaxed">
                                    To submit factual revisions, expired deal alerts, or review suggestions:
                                </p>
                                <a href="mailto:support@latestdeal.in" class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline mt-1 inline-block">support@latestdeal.in</a>
                            </div>
                        </div>

                        <!-- Telegram Alerts -->
                        <div class="flex items-start gap-4">
                            <div class="p-3 rounded-2xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 shrink-0">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.415-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.254-.241-1.868-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.892-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-sm">Community Telegram Channel</h4>
                                <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5 leading-relaxed">
                                    Join thousands of shoppers for real-time loot alerts and price drops:
                                </p>
                                <a href="https://t.me/latestdealin" target="_blank" rel="noopener noreferrer" class="text-sm font-bold text-sky-600 dark:text-sky-400 hover:underline mt-1 inline-block">@latestdealin on Telegram</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400">
                    <span>Inquiries are logged in our internal ticket system and answered in order received.</span>
                </div>
            </div>

            <!-- Operating Schedule & Guidelines Card -->
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/40 dark:shadow-none flex flex-col justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mb-6">Operating Schedule</h2>
                    <p class="text-slate-600 dark:text-slate-300 text-xs sm:text-sm leading-relaxed mb-6">
                        Our autonomous price scanning daemons run continuously 24/7/365 across Indian retailers. Our human editorial and support team operates during the following schedule:
                    </p>

                    <ul class="space-y-3.5 text-xs sm:text-sm">
                        <li class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Monday – Friday</span>
                            <span class="font-bold text-slate-900 dark:text-white">9:30 AM – 6:30 PM IST</span>
                        </li>
                        <li class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Saturday</span>
                            <span class="font-bold text-slate-900 dark:text-white">10:00 AM – 2:00 PM IST</span>
                        </li>
                        <li class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Sunday</span>
                            <span class="font-semibold text-slate-400">Automated Monitoring Only</span>
                        </li>
                    </ul>

                    <div class="mt-8 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <h4 class="font-bold text-slate-900 dark:text-white text-xs mb-1">Found an Expired Deal?</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Click <strong>"Verify Live Price"</strong> directly on the product's page. Our system will immediately ping the live retailer listing and mark it expired if stock is gone.
                        </p>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400">
                    <span>Registered in India. Dedicated to fair consumer e-commerce.</span>
                </div>
            </div>

        </div>

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Learn more about our standards and team:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('about') }}" class="text-red-600 dark:text-red-400 hover:underline">About Us →</a>
                <a href="{{ route('editorial.team') }}" class="text-red-600 dark:text-red-400 hover:underline">Editorial Team →</a>
                <a href="{{ route('corrections.policy') }}" class="text-red-600 dark:text-red-400 hover:underline">Corrections Policy →</a>
                <a href="{{ route('privacy') }}" class="text-red-600 dark:text-red-400 hover:underline">Privacy Policy →</a>
            </div>
        </div>

    </div>
</div>
@endsection

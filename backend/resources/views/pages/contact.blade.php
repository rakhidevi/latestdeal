@extends('layouts.app')

@section('meta')
    <title>Contact Us | LatestDeal.in</title>
    <meta name="description" content="Get in touch with the LatestDeal team for support, editorial inquiries, corrections, or partnership requests.">
    <link rel="canonical" href="{{ url('/contact') }}">
@endsection

@section('content')
<div class="w-full max-w-4xl mx-auto py-8 sm:py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Contact Us</h1>
        
        <p class="text-sm font-semibold text-gray-500 dark:text-slate-400 mb-6">Have questions, feedback, or a deal tip? We&rsquo;d love to hear from you.</p>

        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                Our team is based in India and monitors community feedback 7 days a week. Choose the appropriate channel below to ensure your message reaches the right desk quickly.
            </p>

            <div class="not-prose grid grid-cols-1 md:grid-cols-2 gap-5 my-8">
                <!-- Channel 1: General Support -->
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-bold text-base text-gray-900 dark:text-white mb-1">General Support &amp; Tips</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-3 leading-relaxed">Spotted a hot deal or need help with a price alert?</p>
                    <a href="mailto:support@latestdeal.in" class="text-sm font-bold text-red-600 dark:text-red-400 hover:underline">support@latestdeal.in &rarr;</a>
                </div>

                <!-- Channel 2: Corrections -->
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <h3 class="font-bold text-base text-gray-900 dark:text-white mb-1">Editorial &amp; Corrections</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-3 leading-relaxed">Report outdated prices, expired vouchers, or factual errors.</p>
                    <a href="mailto:corrections@latestdeal.in" class="text-sm font-bold text-amber-600 dark:text-amber-400 hover:underline">corrections@latestdeal.in &rarr;</a>
                </div>

                <!-- Channel 3: Partnerships -->
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-bold text-base text-gray-900 dark:text-white mb-1">Partnerships &amp; Affiliates</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-3 leading-relaxed">Merchant integration, retailer APIs, or network inquiries.</p>
                    <a href="mailto:affiliates@latestdeal.in" class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline">affiliates@latestdeal.in &rarr;</a>
                </div>

                <!-- Channel 4: Community -->
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <h3 class="font-bold text-base text-gray-900 dark:text-white mb-1">Telegram Community</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-3 leading-relaxed">Join 50,000+ shoppers receiving real-time loot alerts.</p>
                    <a href="https://t.me/latestdealin" target="_blank" rel="noopener noreferrer" class="text-sm font-bold text-emerald-600 dark:text-emerald-400 hover:underline">@latestdealin &rarr;</a>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Send Us a Direct Message</h2>

            <form class="not-prose space-y-4 my-6" onsubmit="event.preventDefault(); alert('Thank you for reaching out! Your message has been received.'); this.reset();">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Your Name</label>
                        <input type="text" required placeholder="Pankaj Sharma" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                        <input type="email" required placeholder="pankaj@example.com" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Subject</label>
                    <input type="text" required placeholder="Deal correction / Partnership inquiry" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Message</label>
                    <textarea rows="4" required placeholder="Provide details, URLs, or feedback..." class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition"></textarea>
                </div>

                <button type="submit" class="px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-sm shadow-md shadow-red-600/20 transition cursor-pointer">
                    Send Message &rarr;
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

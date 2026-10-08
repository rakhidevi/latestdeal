@extends('layouts.app')

@section('meta')
    <title>Cookie Policy | LatestDeal.in</title>
    <meta name="description" content="Learn how LatestDeal uses cookies and similar technologies for analytics, advertising, security, and affiliate tracking.">
    <link rel="canonical" href="{{ url('/cookie-policy') }}">
@endsection

@section('content')
<div class="py-6 sm:py-10 font-sans">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        
        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 dark:text-slate-500 mb-6" aria-label="Breadcrumb">
            <a href="/" class="hover:text-red-600 dark:hover:text-red-400 transition-colors">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-600 dark:text-slate-400">Legal & Trust</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 dark:text-white font-bold" aria-current="page">Cookie Policy</span>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Cookie Transparency
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                Cookie <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">Policy</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed">
                How LatestDeal uses cookies and similar technologies to enhance your shopping experience, analyze site usage, and support our free service.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                <span>Compliance & Consumer Privacy Desk</span>
                <span>•</span>
                <span>Updated {{ date('F Y') }}</span>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-10 md:p-12 shadow-xl shadow-slate-200/40 dark:shadow-none">
            <div class="prose prose-slate dark:prose-invert max-w-none prose-headings:font-black prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:tracking-tight prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-p:leading-relaxed prose-li:text-slate-600 dark:prose-li:text-slate-300 prose-strong:text-slate-900 dark:prose-strong:text-white prose-a:text-red-600 dark:prose-a:text-red-400 hover:prose-a:underline">
                
                <h2>1. What Are Cookies?</h2>
                <p>
                    Cookies are small text files that are stored on your browser or device when you visit websites. They enable platforms to remember user sessions, persist theme preferences (such as light or dark mode), and facilitate secure navigation.
                </p>

                <h2>2. How We Use Cookies</h2>
                <p>LatestDeal uses cookies strictly for legitimate, transparent operational requirements:</p>
                <ul>
                    <li><strong>Essential & Functional Cookies:</strong> Required to keep you signed in, remember your saved deal bookmarks, and persist your interface preferences (e.g. theme color and dark mode).</li>
                    <li><strong>Analytics & Performance Cookies:</strong> Help us measure aggregate page visits, identify slow-loading routes, and understand which product categories are most useful to shoppers.</li>
                    <li><strong>Affiliate Tracking Cookies:</strong> When you click a deal link to a retailer (e.g. Amazon, Flipkart), a referral cookie is stored by that merchant to credit LatestDeal if you make a qualifying purchase, at <em>zero additional cost to you</em>.</li>
                    <li><strong>Third-Party Advertising Cookies:</strong> We work with certified partners, including Google AdSense, to display non-intrusive advertisements. These vendors may use cookies to serve ads based on your visits to our site and other destinations across the web.</li>
                </ul>

                <h2>3. Third-Party Cookie Providers</h2>
                <p>
                    Third-party cookies placed through our site are governed by the respective providers' privacy policies:
                </p>
                <ul>
                    <li><strong>Google:</strong> Used for AdSense and analytics. You can manage personalized advertising preferences via <a href="https://myadcenter.google.com/" target="_blank" rel="noopener noreferrer">Google Ad Center</a>.</li>
                    <li><strong>Amazon Associates:</strong> Used strictly for referral tracking upon checkout redirection.</li>
                </ul>

                <h2>4. Managing and Disabling Cookies</h2>
                <p>
                    You have complete control over cookies. Most web browsers allow you to block, inspect, or delete cookies via their security settings:
                </p>
                <ul>
                    <li>Chrome: <em>Settings → Privacy and security → Third-party cookies</em></li>
                    <li>Firefox: <em>Settings → Privacy & Security → Enhanced Tracking Protection</em></li>
                    <li>Safari: <em>Settings → Safari → Advanced → Privacy</em></li>
                </ul>
                <p>
                    Please note that disabling essential cookies may impact certain functionality, such as saved deals or automated price alert notifications.
                </p>

                <h2>5. Contact Concerning Cookies</h2>
                <p>
                    If you have questions about our cookie implementation or data privacy, please reach out to our privacy officer at <a href="mailto:support@latestdeal.in">support@latestdeal.in</a>.
                </p>
            </div>
        </div>

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="mt-10 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Related legal policies:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('privacy') }}" class="text-red-600 dark:text-red-400 hover:underline">Privacy Policy →</a>
                <a href="{{ route('terms') }}" class="text-red-600 dark:text-red-400 hover:underline">Terms of Service →</a>
                <a href="{{ route('affiliate.disclosure') }}" class="text-red-600 dark:text-red-400 hover:underline">Affiliate Disclosure →</a>
                <a href="{{ route('contact') }}" class="text-red-600 dark:text-red-400 hover:underline">Contact Us →</a>
            </div>
        </div>

    </div>
</div>
@endsection

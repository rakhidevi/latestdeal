@extends('layouts.app')

@section('meta')
    <title>Privacy Policy | LatestDeal.in</title>
    <meta name="description" content="Read LatestDeal's Privacy Policy to understand how we collect, handle, and protect your personal information with full transparency.">
    <link rel="canonical" href="{{ url('/privacy') }}">
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
            <span class="text-slate-900 dark:text-white font-bold" aria-current="page">Privacy Policy</span>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Data Protection & Privacy
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                Privacy <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">Policy</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed">
                How we collect, use, and protect your data. We respect your digital privacy and adhere to modern data protection principles.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                <span>Compliance & Data Protection Office</span>
                <span>•</span>
                <span>Updated {{ date('F Y') }}</span>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-10 md:p-12 shadow-xl shadow-slate-200/40 dark:shadow-none">
            <div class="prose prose-slate dark:prose-invert max-w-none prose-headings:font-black prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:tracking-tight prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-p:leading-relaxed prose-li:text-slate-600 dark:prose-li:text-slate-300 prose-strong:text-slate-900 dark:prose-strong:text-white prose-a:text-red-600 dark:prose-a:text-red-400 hover:prose-a:underline">
                
                <h2>1. Information We Collect</h2>
                <p>
                    We collect only the minimum information necessary to deliver a personalized, functional deal discovery experience:
                </p>
                <ul>
                    <li><strong>Account Information:</strong> If you voluntarily register an account, we store your name, email address, and encrypted credentials.</li>
                    <li><strong>Deal Preferences & Bookmarks:</strong> Deals you save, price alerts you subscribe to, and customized notification preferences.</li>
                    <li><strong>Technical & Log Data:</strong> Standard IP address, browser user-agent, operating system, and referral source used strictly for diagnostics, rate-limiting, and DDoS defense.</li>
                </ul>

                <h2>2. How We Use Your Information</h2>
                <p>We use your information exclusively for the following purposes:</p>
                <ul>
                    <li>To dispatch requested deal alerts and instant price drop notifications.</li>
                    <li>To authenticate your session and preserve interface preferences.</li>
                    <li>To prevent automated scrapers from overwhelming our search infrastructure.</li>
                    <li>To optimize site performance and troubleshoot server exceptions.</li>
                </ul>

                <!-- Zero Data Sale Callout -->
                <div class="not-prose my-6 p-6 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="p-1 rounded-lg bg-emerald-200 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <h4 class="font-black text-emerald-900 dark:text-emerald-300 text-sm">Zero Data Selling Guarantee</h4>
                    </div>
                    <p class="text-xs text-emerald-800 dark:text-emerald-400 leading-relaxed">
                        We never sell, rent, or trade your personal information or browsing records to data brokers, advertising aggregators, or external commercial third parties.
                    </p>
                </div>

                <h2>3. Third-Party Services & Links</h2>
                <p>
                    LatestDeal contains links to external retail platforms (including Amazon.in, Flipkart, Croma, and Reliance Digital). When you click an external link, you leave our site and become subject to the privacy practices of that specific retailer. We recommend reviewing their privacy statements before completing transactions.
                </p>

                <h2>4. Data Retention & Security</h2>
                <p>
                    We apply enterprise security measures—including TLS 1.3 encryption in transit, strict database access controls, and salted password hashing—to safeguard your data. You may request account deletion and complete erasure of your saved deals at any time.
                </p>

                <h2>5. Your Privacy Rights</h2>
                <p>
                    Under applicable data protection frameworks, you maintain the right to access, rectify, or request deletion of your personal data stored on our servers. To exercise these rights, please contact our Data Protection Officer at <a href="mailto:support@latestdeal.in">support@latestdeal.in</a>.
                </p>
            </div>
        </div>

        <!-- Quick Navigation Footer Bar for Trust & Legal Pages -->
        <div class="mt-10 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Related legal policies:</span>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('terms') }}" class="text-red-600 dark:text-red-400 hover:underline">Terms of Service →</a>
                <a href="{{ route('cookie') }}" class="text-red-600 dark:text-red-400 hover:underline">Cookie Policy →</a>
                <a href="{{ route('affiliate.disclosure') }}" class="text-red-600 dark:text-red-400 hover:underline">Affiliate Disclosure →</a>
                <a href="{{ route('contact') }}" class="text-red-600 dark:text-red-400 hover:underline">Contact Desk →</a>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('meta')
    <title>Privacy Policy | LatestDeal.in</title>
    <meta name="description" content="Read LatestDeal's transparent Privacy Policy covering account handling, newsletter dispatch, first-party search telemetry, and affiliate tracking.">
    <link rel="canonical" href="{{ url('/privacy') }}">
@endsection

@section('content')
<div class="w-full max-w-4xl mx-auto py-8 sm:py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Privacy Policy</h1>
        
        <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 mb-8 uppercase tracking-wider">Effective & Last Updated: October 2026</p>

        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300 space-y-6">
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                At <strong>LatestDeal.in</strong>, we take a privacy-conscious, transparent approach to shopping intelligence. This Privacy Policy details exactly what information we collect, why we collect it, how it is secured, and your rights regarding your data.
            </p>

            <div class="border-t border-gray-100 dark:border-slate-800 pt-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">1. Information We Collect and How We Use It</h2>
                
                <h3 class="text-base font-bold text-gray-800 dark:text-slate-200 mt-4 mb-2">A. User Accounts & Authentication</h3>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    When you voluntarily register an account on LatestDeal, we collect your <strong>name</strong> and <strong>email address</strong>. Passwords are cryptographically hashed using standard Bcrypt algorithms before storage; plaintext passwords are never stored or accessible by our team. Account data is used strictly to authenticate you, maintain your saved deal bookmarks (wishlist), and manage your notification preferences.
                </p>

                <h3 class="text-base font-bold text-gray-800 dark:text-slate-200 mt-4 mb-2">B. Newsletter & Price Drop Alerts</h3>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    If you subscribe to our newsletter or request a price drop alert for a specific product, we store your email address and requested price threshold. We use this data solely to send deal digests and triggered threshold alerts. Every marketing or alert email includes a one-click unsubscribe mechanism.
                </p>

                <h3 class="text-base font-bold text-gray-800 dark:text-slate-200 mt-4 mb-2">C. Web Push Notifications</h3>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    If you grant browser push notification permissions, our system stores an anonymous browser endpoint token. You may revoke push notification permissions at any time directly in your browser's site settings.
                </p>

                <h3 class="text-base font-bold text-gray-800 dark:text-slate-200 mt-4 mb-2">D. Search Intelligence & Telemetry (First-Party Cookies)</h3>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    To deliver accurate AI shopping recommendations and monitor catalog health, our first-party session engine assigns pseudonymous identifiers (<code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">uic_vid</code> for visitor identity and <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">uic_sid</code> for session state). These cookies record search queries, product intent, and aggregated deal views. We do not sell this telemetry or share it with data brokers.
                </p>
            </div>

            <div class="border-t border-gray-100 dark:border-slate-800 pt-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">2. Affiliate Links and Merchant Tracking</h2>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    LatestDeal is a participant in retail affiliate marketing programs, including Amazon Associates, Flipkart Affiliate Program, Croma, Reliance Digital, and Myntra. When you click an outbound link to view an offer on a retailer's website, an affiliate tracking tag is appended to the outbound URL.
                </p>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    The destination merchant may place a standard affiliate tracking cookie on your device to attribute any qualifying purchase to LatestDeal. We do not receive, store, or process your payment details, shipping addresses, or credit card information; all purchase transactions occur entirely on the merchant's secure platform under their respective privacy policies.
                </p>
            </div>

            <div class="border-t border-gray-100 dark:border-slate-800 pt-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">3. Cookies and Browser Controls</h2>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    We use strictly necessary cookies for session authentication, security (CSRF defense), and dark mode appearance preferences. You can configure your browser to reject cookies or notify you when cookies are set. Please note that disabling essential cookies may impact account login functionality.
                </p>
            </div>

            <div class="border-t border-gray-100 dark:border-slate-800 pt-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">4. Data Retention and Your Rights</h2>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    We retain personal information only for as long as your account remains active or as needed to deliver requested alerts. You have the right to:
                </p>
                <ul class="list-disc pl-6 space-y-1.5 text-sm text-gray-600 dark:text-slate-300 mb-4">
                    <li>Access the personal information associated with your account.</li>
                    <li>Unsubscribe from email newsletters and price alerts instantly using the footer link in any message.</li>
                    <li>Request full erasure of your account, saved bookmarks, and associated personal data.</li>
                </ul>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">
                    To exercise any of these rights, contact our privacy team at <a href="mailto:contact@latestdeal.in" class="text-red-600 hover:underline font-semibold">contact@latestdeal.in</a>.
                </p>
            </div>

            <div class="border-t border-gray-100 dark:border-slate-800 pt-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">5. Contact Information</h2>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">
                    For questions, comments, or data deletion inquiries regarding this Privacy Policy, please reach out to us at <a href="mailto:contact@latestdeal.in" class="text-red-600 hover:underline font-semibold">contact@latestdeal.in</a>.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

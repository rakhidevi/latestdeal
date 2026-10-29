@extends('layouts.app')

@section('meta')
    <title>Privacy Policy | LatestDeal.in</title>
    <meta name="description" content="Read LatestDeal's transparent Privacy Policy covering Google AdSense disclosures, third-party advertising cookies, personalized ad opt-outs, and affiliate tracking.">
    <link rel="canonical" href="{{ url('/privacy') }}">
@endsection

@section('content')
<div class="w-full max-w-4xl mx-auto py-8 sm:py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Privacy Policy</h1>
        
        <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 mb-8 uppercase tracking-wider">Effective & Last Updated: October 2026</p>

        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300 space-y-6">
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                At <strong>LatestDeal.in</strong>, we take a privacy-conscious, transparent approach to shopping intelligence, advertising, and user data protection. This Privacy Policy details the types of information we collect, how third-party advertising vendors (including Google AdSense) use cookies, how retail affiliate relationships operate, and your rights to manage or opt out of personalized data collection.
            </p>

            <!-- Section 1 -->
            <div class="border-t border-gray-100 dark:border-slate-800 pt-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">1. Information We Collect and How We Use It</h2>
                
                <h3 class="text-base font-bold text-gray-800 dark:text-slate-200 mt-4 mb-2">A. User Accounts &amp; Authentication</h3>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    When you voluntarily register an account on LatestDeal, we collect your <strong>name</strong> and <strong>email address</strong>. Passwords are cryptographically hashed using standard Bcrypt algorithms before storage; plaintext passwords are never stored or accessible by our team. Account data is used strictly to authenticate you, maintain your saved deal bookmarks (wishlist), and manage your notification preferences.
                </p>

                <h3 class="text-base font-bold text-gray-800 dark:text-slate-200 mt-4 mb-2">B. Newsletter &amp; Price Drop Alerts</h3>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    If you subscribe to our newsletter or request a price drop alert for a specific product, we store your email address and requested price threshold. We use this data solely to send deal digests and triggered threshold alerts. Every marketing or alert email includes a one-click unsubscribe mechanism.
                </p>

                <h3 class="text-base font-bold text-gray-800 dark:text-slate-200 mt-4 mb-2">C. Web Push Notifications</h3>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    If you grant browser push notification permissions, our system stores an anonymous browser endpoint token. You may revoke push notification permissions at any time directly in your browser's site settings.
                </p>

                <h3 class="text-base font-bold text-gray-800 dark:text-slate-200 mt-4 mb-2">D. First-Party Telemetry &amp; Anonymous Analytics</h3>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    To deliver accurate shopping recommendations, diagnose catalog health, and assess traffic volume, our first-party session engine assigns pseudonymous identifiers (<code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">uic_vid</code> for visitor identity and <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">uic_sid</code> for session state). These cookies record search queries, product intent, and aggregated deal views. We do not sell this telemetry or share it with data brokers.
                </p>
            </div>

            <!-- Section 2: Google AdSense and Third-Party Advertising (Mandatory AdSense Compliance) -->
            <div class="border-t border-gray-100 dark:border-slate-800 pt-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">2. Google AdSense &amp; Third-Party Advertising Disclosures</h2>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    We may display advertisements served by Google AdSense and other reputable third-party advertising networks. In accordance with Google's Required Privacy Disclosures for Publishers:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-sm text-gray-600 dark:text-slate-300 mb-4">
                    <li>
                        <strong>Third-Party Vendors and Cookies:</strong> Third-party vendors, including Google, use cookies (such as the Google DoubleClick cookie) to serve ads based on a user's prior visits to LatestDeal.in or other websites on the Internet.
                    </li>
                    <li>
                        <strong>Personalized Advertising:</strong> Google's use of advertising cookies enables it and its partners to serve ads to our visitors based on their browsing history across our site and/or other sites on the Internet.
                    </li>
                    <li>
                        <strong>Opting Out of Google Personalized Ads:</strong> You may opt out of personalized advertising by visiting Google's <a href="https://adssettings.google.com" target="_blank" rel="noopener noreferrer" class="text-red-600 hover:underline font-semibold">Ads Settings</a> (<a href="https://www.google.com/settings/ads" target="_blank" rel="noopener noreferrer" class="text-red-600 hover:underline font-semibold">https://www.google.com/settings/ads</a>).
                    </li>
                    <li>
                        <strong>Opting Out of Third-Party Advertising Networks:</strong> Alternatively, you can opt out of a third-party vendor's use of cookies for interest-based advertising by visiting the Digital Advertising Alliance Consumer Choice page at <a href="https://www.aboutads.info/choices/" target="_blank" rel="noopener noreferrer" class="text-red-600 hover:underline font-semibold">www.aboutads.info/choices</a> or the Network Advertising Initiative opt-out page at <a href="https://optout.networkadvertising.org" target="_blank" rel="noopener noreferrer" class="text-red-600 hover:underline font-semibold">optout.networkadvertising.org</a>.
                    </li>
                </ul>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">
                    If you have opted out of third-party ad serving, non-personalized contextual ads may still be shown based on the content of the page you are viewing rather than your past browsing history.
                </p>
            </div>

            <!-- Section 3: Affiliate Marketing -->
            <div class="border-t border-gray-100 dark:border-slate-800 pt-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">3. Affiliate Marketing Disclosures &amp; Merchant Tracking</h2>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    LatestDeal is reader-supported and participates in several retail affiliate marketing programs, including Amazon Associates, Flipkart Affiliate Program, Croma, Reliance Digital, and Myntra. When you click an outbound link to view an offer on a merchant's store, an affiliate referral identifier is appended to the destination URL.
                </p>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    The destination retailer may place a standard affiliate tracking cookie on your device to attribute any qualifying transaction to LatestDeal. <strong>We never receive, store, or process your billing details, credit/debit card numbers, or postal addresses.</strong> All transactions take place entirely on the retailer's secure website under their own Terms of Service and Privacy Policies.
                </p>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">
                    For complete details on our commercial relationships, please read our dedicated <a href="{{ route('affiliate.disclosure') }}" class="text-red-600 hover:underline font-semibold">Affiliate Disclosure</a>.
                </p>
            </div>

            <!-- Section 4: Cookie Management -->
            <div class="border-t border-gray-100 dark:border-slate-800 pt-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">4. Cookie Management and Browser Controls</h2>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    You can instruct your browser to refuse all cookies, prompt you before accepting a cookie, or delete stored cookies at any time. Major browsers provide straightforward cookie management controls:
                </p>
                <ul class="list-disc pl-6 space-y-1 text-sm text-gray-600 dark:text-slate-300 mb-3">
                    <li><strong>Google Chrome:</strong> Settings &rarr; Privacy and security &rarr; Third-party cookies</li>
                    <li><strong>Mozilla Firefox:</strong> Settings &rarr; Privacy &amp; Security &rarr; Enhanced Tracking Protection</li>
                    <li><strong>Apple Safari:</strong> Preferences &rarr; Privacy &rarr; Prevent cross-site tracking</li>
                    <li><strong>Microsoft Edge:</strong> Settings &rarr; Cookies and site permissions</li>
                </ul>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">
                    Please refer to our <a href="{{ url('/cookie-policy') }}" class="text-red-600 hover:underline font-semibold">Cookie Policy</a> for a comprehensive inventory of all first-party and third-party cookies utilized on LatestDeal.
                </p>
            </div>

            <!-- Section 5: Rights -->
            <div class="border-t border-gray-100 dark:border-slate-800 pt-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">5. Data Retention and Your Rights (GDPR &amp; Global Privacy)</h2>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed mb-3">
                    We retain personal account data only for as long as your account remains active or as needed to provide requested services. Under applicable data privacy laws, you have the right to:
                </p>
                <ul class="list-disc pl-6 space-y-1.5 text-sm text-gray-600 dark:text-slate-300 mb-4">
                    <li>Access the personal information associated with your account.</li>
                    <li>Rectify any inaccurate or incomplete personal details.</li>
                    <li>Request permanent erasure of your account, bookmarks, and alert histories.</li>
                    <li>Unsubscribe instantly from any marketing or alert email via the footer unsubscribe link.</li>
                    <li>Object to or restrict the processing of your personal data.</li>
                </ul>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">
                    To exercise any of these rights, contact our Data Protection Officer directly at <a href="mailto:privacy@latestdeal.in" class="text-red-600 hover:underline font-semibold">privacy@latestdeal.in</a> or <a href="mailto:contact@latestdeal.in" class="text-red-600 hover:underline font-semibold">contact@latestdeal.in</a>.
                </p>
            </div>

            <!-- Section 6: Contact -->
            <div class="border-t border-gray-100 dark:border-slate-800 pt-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">6. Contact Information</h2>
                <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">
                    For questions, comments, or data deletion inquiries regarding this Privacy Policy, please contact us at:<br>
                    <strong>Email:</strong> <a href="mailto:privacy@latestdeal.in" class="text-red-600 hover:underline font-semibold">privacy@latestdeal.in</a> / <a href="mailto:contact@latestdeal.in" class="text-red-600 hover:underline font-semibold">contact@latestdeal.in</a><br>
                    <strong>Platform:</strong> LatestDeal.in, India
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

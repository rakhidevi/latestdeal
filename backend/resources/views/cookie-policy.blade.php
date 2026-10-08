@extends('layouts.app')

@section('meta')
    <title>Cookie Policy | LatestDeal.in</title>
    <meta name="description" content="Read LatestDeal's Cookie Policy to understand how we use cookies, local storage, and tracking technologies to optimize your experience.">
    <link rel="canonical" href="{{ url('/cookie-policy') }}">
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Cookie Policy</h1>
        
        <p class="text-sm font-semibold text-gray-500 dark:text-slate-400 mb-6">Last Updated: October 2026</p>

        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                This Cookie Policy explains how LatestDeal (&ldquo;we&rdquo;, &ldquo;us&rdquo;, or &ldquo;our&rdquo;) uses cookies and similar tracking technologies when you visit our website.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">1. What Are Cookies?</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                Cookies are small text files placed on your device by websites you visit. They are widely used to make websites function efficiently, remember your preferences (such as dark mode settings or pinned categories), and provide reporting insights to site owners.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">2. Types of Cookies We Use</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                We categorize the cookies used on LatestDeal into four functional pillars:
            </p>
            <ul class="list-disc pl-6 space-y-2 mt-4 mb-6 text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                <li><strong class="text-gray-900 dark:text-white font-semibold">Strictly Necessary Cookies:</strong> Essential for core operations like user authentication, CSRF security verification, and theme preference persistence. These cannot be disabled.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Preference Cookies:</strong> Remember your theme choices (light/dark mode), deal view layouts (grid/compact), and alert filters.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Performance &amp; Analytics Cookies:</strong> Help us measure anonymous traffic volume, detect slow page loads, and diagnose interface bottlenecks to improve user experience.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Affiliate Tracking Cookies:</strong> Placed when you click outbound links to partner retailer stores (e.g., Amazon, Flipkart) to confirm referral attribution. These cookies do not store personally identifiable data.</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">3. Managing Your Cookie Preferences</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                You can control and manage cookies through your browser settings. Most browsers allow you to block or delete cookies. Please note that disabling essential cookies may impact certain platform features such as saving deal bookmarks or dark mode persistence.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">4. Updates to This Policy</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                We may update our Cookie Policy periodically to reflect technological, operational, or regulatory modifications. Any changes will be published here with an updated revision date.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">5. Questions &amp; Support</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                For questions regarding our use of cookies or privacy practices, reach us at <a href="mailto:privacy@latestdeal.in" class="text-red-600 hover:underline font-semibold">privacy@latestdeal.in</a>.
            </p>
        </div>
    </div>
</div>
@endsection

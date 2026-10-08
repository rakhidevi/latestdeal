@extends('layouts.app')

@section('meta')
    <title>Privacy Policy | LatestDeal.in</title>
    <meta name="description" content="Read LatestDeal's Privacy Policy to understand how we collect, handle, and protect your personal information with full transparency.">
    <link rel="canonical" href="{{ url('/privacy') }}">
@endsection

@section('content')
<div class="w-full max-w-4xl mx-auto py-8 sm:py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Privacy Policy</h1>
        
        <p class="text-sm font-semibold text-gray-500 dark:text-slate-400 mb-6">Last Updated: September 2026</p>

        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                At LatestDeal, your privacy is important to us. This Privacy Policy explains how we collect, use, and protect your information when you visit our website.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">1. Information We Collect</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                <strong class="text-gray-900 dark:text-white font-semibold">Information You Provide to Us:</strong> We collect information you provide directly to us when you create an account, subscribe to our newsletter, or contact us. This may include your name, email address, and account preferences.
            </p>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                <strong class="text-gray-900 dark:text-white font-semibold">Information Collected Automatically:</strong> We automatically collect certain information when you visit the site. This includes your IP address, browser type, operating system, referring URLs, and information about your interaction with the site.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">2. Cookies and Tracking Technologies</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                We use cookies and similar tracking technologies to track the activity on our website and hold certain information. Cookies are files with a small amount of data which may include an anonymous unique identifier. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">3. Affiliate Links and Third-Party Tracking</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                LatestDeal contains affiliate links. When you click on an outbound link to a merchant (such as Amazon), that merchant may place a cookie on your browser to track that we referred you. This allows us to earn a commission on qualifying purchases. These third-party merchants have their own privacy policies and tracking mechanisms, which we do not control.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">4. How We Use Your Information</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                We use the collected information for various purposes:
            </p>
            <ul class="list-disc pl-6 space-y-2 mt-4 mb-6 text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                <li>To provide and maintain our website</li>
                <li>To notify you about changes to our website</li>
                <li>To allow you to participate in interactive features of our website when you choose to do so</li>
                <li>To provide customer support</li>
                <li>To gather analysis or valuable information so that we can improve our website</li>
                <li>To monitor the usage of our website</li>
                <li>To detect, prevent and address technical issues</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">5. Contact Us</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                If you have any questions about this Privacy Policy, please contact us at <a href="mailto:contact@latestdeal.in" class="text-red-600 hover:underline font-semibold">contact@latestdeal.in</a>.
            </p>
        </div>
    </div>
</div>
@endsection

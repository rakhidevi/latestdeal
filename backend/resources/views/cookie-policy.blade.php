@extends('layouts.app')

@section('meta')
    <title>Cookie Policy | LatestDeal.in</title>
    <meta name="description" content="Learn how LatestDeal uses cookies and similar technologies for analytics, advertising, and affiliate tracking.">
    <link rel="canonical" href="{{ url('/cookie-policy') }}">
@endsection

@section('content')
<div class="relative min-h-screen pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-amber-50 border border-amber-100 text-amber-600 text-sm font-bold tracking-widest uppercase mb-4 shadow-sm">
                Cookie Policy
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-slate-800 tracking-tight mb-4">
                How We Use <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-500">Cookies</span>
            </h1>
        </div>

        <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 md:p-12 shadow-2xl shadow-slate-200/50">
            <div class="prose prose-lg prose-slate max-w-none prose-headings:font-black prose-headings:text-slate-800">
                <h2>What are Cookies?</h2>
                <p>Cookies are small text files that are stored on your computer or mobile device when you visit a website. They allow the website to recognize your device and remember if you have been to the website before.</p>

                <h2>How We Use Cookies</h2>
                <p>LatestDeal uses cookies for several purposes:</p>
                <ul>
                    <li><strong>Essential Cookies:</strong> Required to enable core site functionality, like keeping you logged into your account.</li>
                    <li><strong>Analytics Cookies:</strong> Help us understand how visitors interact with our platform so we can improve the user experience.</li>
                    <li><strong>Advertising & Third-Party Cookies:</strong> We use third-party vendors, including Google AdSense, to serve ads. Google uses cookies to serve ads based on your prior visits to our website or other websites.</li>
                    <li><strong>Affiliate Tracking Cookies:</strong> When you click on a deal and are redirected to a merchant (like Amazon), a cookie is placed to track that referral so we can earn a commission if you make a purchase.</li>
                </ul>

                <h2>Managing Your Cookie Preferences</h2>
                <p>You have the right to choose whether or not to accept cookies. However, they are an important part of how our services work, so you should be aware that if you choose to refuse or remove cookies, this could affect the availability and functionality of LatestDeal.</p>
                <p>You may opt out of personalized advertising by visiting <a href="https://myadcenter.google.com/" target="_blank" rel="noopener">Google Ads Settings</a> or by visiting <a href="https://www.aboutads.info/choices/" target="_blank" rel="noopener">www.aboutads.info</a>.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('meta')
    <title>Cookie Policy | LatestDeal</title>
    <meta name="description" content="Learn how LatestDeal uses cookies and similar technologies for analytics, advertising, and affiliate tracking.">
    <link rel="canonical" href="{{ url('/cookie-policy') }}">
@endsection

@section('content')
<x-info.page-container>
    <x-info.page-header title="Cookie Policy" label="Data Protection">
        How we use cookies and tracking technologies.
    </x-info.page-header>
    
    <x-info.last-updated date="{{ date('F d, Y') }}" />

    <x-info.toc>
        <x-info.toc-item href="#what-are-cookies" number="01">What are Cookies?</x-info.toc-item>
        <x-info.toc-item href="#how-we-use-cookies" number="02">How We Use Cookies</x-info.toc-item>
        <x-info.toc-item href="#managing-preferences" number="03">Managing Your Cookie Preferences</x-info.toc-item>
    </x-info.toc>

    <x-info.section id="what-are-cookies" number="01" title="What are Cookies?">
        <p>Cookies are small text files that are stored on your computer or mobile device when you visit a website. They allow the website to recognize your device and remember if you have been to the website before.</p>
    </x-info.section>

    <x-info.section id="how-we-use-cookies" number="02" title="How We Use Cookies">
        <p>LatestDeal uses cookies for several purposes:</p>
        <ul>
            <li><strong>Essential Cookies:</strong> Required to enable core site functionality, like keeping you logged into your account.</li>
            <li><strong>Analytics Cookies:</strong> Help us understand how visitors interact with our platform so we can improve the user experience.</li>
            <li><strong>Advertising & Third-Party Cookies:</strong> We use third-party vendors, including Google AdSense, to serve ads. Google uses cookies to serve ads based on your prior visits to our website or other websites.</li>
            <li><strong>Affiliate Tracking Cookies:</strong> When you click on a deal and are redirected to a merchant (like Amazon), a cookie is placed to track that referral so we can earn a commission if you make a purchase.</li>
        </ul>
    </x-info.section>

    <x-info.section id="managing-preferences" number="03" title="Managing Your Cookie Preferences">
        <p>You have the right to choose whether or not to accept cookies. However, they are an important part of how our services work, so you should be aware that if you choose to refuse or remove cookies, this could affect the availability and functionality of LatestDeal.</p>
        <p>You may opt out of personalized advertising by visiting <a href="https://myadcenter.google.com/" target="_blank" rel="noopener" class="text-red-600 hover:underline">Google Ads Settings</a> or by visiting <a href="https://www.aboutads.info/choices/" target="_blank" rel="noopener" class="text-red-600 hover:underline">www.aboutads.info</a>.</p>
    </x-info.section>

</x-info.page-container>
@endsection

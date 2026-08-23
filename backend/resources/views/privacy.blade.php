@extends('layouts.app')

@section('meta')
    <title>Privacy Policy - LatestDeal</title>
@endsection

@section('content')
<x-info.page-container>
    <x-info.page-header title="Privacy Policy" label="Data Protection">
        How we collect, use, and protect your data.
    </x-info.page-header>
    
    <x-info.last-updated date="{{ date('F d, Y') }}" />

    <x-info.toc>
        <x-info.toc-item href="#information-we-collect" number="01">Information We Collect</x-info.toc-item>
        <x-info.toc-item href="#how-we-use-information" number="02">How We Use Information</x-info.toc-item>
        <x-info.toc-item href="#sharing-of-information" number="03">Sharing of Information</x-info.toc-item>
        <x-info.toc-item href="#cookies-tracking" number="04">Cookies, Tracking, and Advertising</x-info.toc-item>
        <x-info.toc-item href="#your-rights" number="05">Your Rights & Data Deletion</x-info.toc-item>
    </x-info.toc>

    <x-info.section id="information-we-collect" number="01" title="Information We Collect">
        <p>We collect information you provide directly to us, such as when you create or modify your account, subscribe to price alerts, contact customer support, or otherwise communicate with us. This information may include your name, email address, and saved deal preferences.</p>
    </x-info.section>

    <x-info.section id="how-we-use-information" number="02" title="How We Use Information">
        <p>We use the information we collect about you to provide, maintain, and improve our services, including to facilitate notifications, send price drop alerts, provide deals you request, develop new features, authenticate users, and send product updates.</p>
        
        <x-info.callout title="Privacy Commitment" icon="shield">
            We never sell your personal data to third parties or data brokers. Your information is strictly used to improve your shopping experience.
        </x-info.callout>
    </x-info.section>

    <x-info.section id="sharing-of-information" number="03" title="Sharing of Information">
        <p>We may share the information we collect about you as described in this Statement or as described at the time of collection or sharing, including with third parties to provide you a service you requested through a partnership or promotional offering made by a third party or us.</p>
    </x-info.section>

    <x-info.section id="cookies-tracking" number="04" title="Cookies, Tracking, and Advertising">
        <p>We use cookies and similar technologies for purposes such as authenticating users, remembering user preferences, determining the popularity of content, and analyzing site traffic.</p>
        <p><strong>Third-Party Advertising & AdSense:</strong> We use third-party advertising companies, including Google AdSense, to serve ads when you visit our website. These companies may use information (not including your name, address, email address, or telephone number) about your visits to this and other websites in order to provide advertisements about goods and services of interest to you.</p>
        <ul>
            <li>Third party vendors, including Google, use cookies to serve ads based on your prior visits to our website or other websites.</li>
            <li>Google's use of advertising cookies enables it and its partners to serve ads to our users based on their visit to our sites and/or other sites on the Internet.</li>
            <li>You may opt out of personalized advertising by visiting <a href="https://myadcenter.google.com/" target="_blank" rel="noopener" class="text-red-600 hover:underline">Google Ads Settings</a>. Alternatively, you can opt out of a third-party vendor's use of cookies for personalized advertising by visiting <a href="https://www.aboutads.info/choices/" target="_blank" rel="noopener" class="text-red-600 hover:underline">www.aboutads.info</a>.</li>
        </ul>
        <p><strong>Affiliate Tracking:</strong> As an affiliate platform, tracking cookies are utilized by our merchant partners (like Amazon) when you click "Buy Now" to attribute the sale to LatestDeal.</p>
    </x-info.section>

    <x-info.section id="your-rights" number="05" title="Your Rights & Data Deletion">
        <p>You have the right to access, modify, or delete your personal data at any time. You can delete your account directly from your Profile settings. Upon deletion, all associated data, including saved deals and price alerts, will be permanently removed from our servers.</p>
    </x-info.section>

</x-info.page-container>
@endsection

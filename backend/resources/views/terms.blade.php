@extends('layouts.app')

@section('meta')
    <title>Terms of Service | LatestDeal</title>
    <meta name="description" content="Read the Terms of Service for using LatestDeal, a consumer shopping intelligence platform.">
    <link rel="canonical" href="{{ url('/terms') }}">
@endsection

@section('content')
<x-info.page-container>
    <x-info.page-header title="Terms of Service" label="Legal Info">
        Please read these terms carefully before using our platform.
    </x-info.page-header>
    
    <x-info.last-updated date="{{ date('F d, Y') }}" />

    <x-info.toc>
        <x-info.toc-item href="#acceptance-of-terms" number="01">Acceptance of Terms</x-info.toc-item>
        <x-info.toc-item href="#service-description" number="02">Service Description</x-info.toc-item>
        <x-info.toc-item href="#affiliate-disclosure" number="03">Affiliate Disclosure & Transparency</x-info.toc-item>
        <x-info.toc-item href="#accuracy-of-information" number="04">Accuracy of Information</x-info.toc-item>
        <x-info.toc-item href="#user-accounts" number="05">User Accounts & Data</x-info.toc-item>
        <x-info.toc-item href="#limitation-of-liability" number="06">Limitation of Liability</x-info.toc-item>
    </x-info.toc>

    <x-info.section id="acceptance-of-terms" number="01" title="Acceptance of Terms">
        <p>By accessing and using LatestDeal.in, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our platform.</p>
    </x-info.section>

    <x-info.section id="service-description" number="02" title="Service Description">
        <p>LatestDeal is a consumer shopping intelligence platform that provides product information, historical pricing data, AI-assisted summaries, and curated deal lists from third-party retailers (such as Amazon and Flipkart). We do not sell products directly.</p>
    </x-info.section>

    <x-info.section id="affiliate-disclosure" number="03" title="Affiliate Disclosure & Transparency">
        <p>We participate in affiliate marketing programs. When you click on links to various merchants on this site and make a purchase, this can result in this site earning a commission. This comes at no additional cost to you and does not compromise the integrity of our editorial recommendations.</p>
    </x-info.section>

    <x-info.section id="accuracy-of-information" number="04" title="Accuracy of Information (Prices & Availability)">
        <p>While we strive for 100% accuracy, prices, stock availability, and promotional coupons on third-party sites are subject to change without notice. The final price you pay will be the price displayed at the merchant's checkout. We are not liable for any discrepancies between the price listed on LatestDeal and the final merchant price.</p>
    </x-info.section>

    <x-info.section id="user-accounts" number="05" title="User Accounts & Data">
        <p>If you choose to create an account to save deals or set price alerts, you are responsible for maintaining the confidentiality of your account credentials. For information on how we handle your personal data, please read our <a href="/privacy" class="text-red-600 hover:underline">Privacy Policy</a>.</p>
    </x-info.section>

    <x-info.section id="limitation-of-liability" number="06" title="Limitation of Liability">
        <p>LatestDeal and its operators shall not be liable for any direct, indirect, incidental, or consequential damages resulting from your use of the site or your inability to use the site. You agree to use the site at your own risk.</p>
    </x-info.section>

</x-info.page-container>
@endsection

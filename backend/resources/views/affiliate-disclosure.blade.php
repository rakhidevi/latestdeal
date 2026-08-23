@extends('layouts.app')

@section('meta')
    <title>Affiliate Disclosure | LatestDeal</title>
    <meta name="description" content="Read LatestDeal's affiliate disclosure to understand how we earn commissions to keep the platform free.">
    <link rel="canonical" href="{{ url('/affiliate-disclosure') }}">
@endsection

@section('content')
<x-info.page-container>
    <x-info.page-header title="Affiliate Disclosure" label="Transparency First">
        Our commitment to absolute transparency in how LatestDeal funds its operations.
    </x-info.page-header>
    
    <x-info.last-updated date="{{ date('F d, Y') }}" />

    <x-info.callout title="How LatestDeal makes money" icon="info">
        <p class="mb-4">Some links on LatestDeal are affiliate links. If you purchase through one of these links, we may receive a commission at <strong>no additional cost to you</strong>.</p>
        <p class="font-bold text-red-600">Affiliate relationship ≠ editorial influence.</p>
    </x-info.callout>

    <x-info.section id="transparency" number="01" title="Our Commitment to Transparency">
        <p>LatestDeal is designed to be a free resource for consumers to find genuine discounts, track historical prices, and read unbiased buying advice. To keep our platform free and fund our operations, we participate in affiliate marketing programs.</p>
    </x-info.section>

    <x-info.section id="amazon-associates" number="02" title="Amazon Associates Disclosure">
        <p><strong>LatestDeal is a participant in the Amazon Associates Program</strong>, an affiliate advertising program designed to provide a means for sites to earn advertising fees by advertising and linking to Amazon.</p>
    </x-info.section>

    <x-info.section id="how-it-works" number="03" title="How It Works">
        <p>When you click on links to various merchants on this site and make a purchase, this can result in this site earning a commission. This commission comes at <strong>absolutely no additional cost to you</strong>. The merchant pays us a small percentage of the sale as a "thank you" for referring you to their site.</p>
    </x-info.section>

    <x-info.section id="editorial-independence" number="04" title="Does this influence our deals?">
        <p><strong>No.</strong> Our proprietary algorithms discover deals based on actual price drops and historical data, not affiliate payout rates. If a deal is terrible, our AI scores it poorly, regardless of whether we make a commission. Our editorial team prioritizes the consumer's best interest above all else.</p>
    </x-info.section>

</x-info.page-container>
@endsection

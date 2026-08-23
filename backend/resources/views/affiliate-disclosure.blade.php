@extends('layouts.app')

@section('meta')
    <title>Affiliate Disclosure | LatestDeal.in</title>
    <meta name="description" content="Read LatestDeal's affiliate disclosure to understand how we earn commissions to keep the platform free.">
    <link rel="canonical" href="{{ url('/affiliate-disclosure') }}">
@endsection

@section('content')
<div class="relative min-h-screen pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-sm font-bold tracking-widest uppercase mb-4 shadow-sm">
                Transparency First
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-slate-800 tracking-tight mb-4">
                Affiliate <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-500">Disclosure</span>
            </h1>
        </div>

        <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 md:p-12 shadow-2xl shadow-slate-200/50">
            <div class="prose prose-lg prose-slate max-w-none prose-headings:font-black prose-headings:text-slate-800">
                <h2>Our Commitment to Transparency</h2>
                <p>LatestDeal.in is designed to be a free resource for consumers to find genuine discounts, track historical prices, and read unbiased buying advice. To keep our platform free and fund our operations, we participate in affiliate marketing programs.</p>

                <h2>Amazon Associates Disclosure</h2>
                <p><strong>LatestDeal.in is a participant in the Amazon Associates Program</strong>, an affiliate advertising program designed to provide a means for sites to earn advertising fees by advertising and linking to Amazon.in.</p>
                
                <h2>How It Works</h2>
                <p>When you click on links to various merchants on this site and make a purchase, this can result in this site earning a commission. This commission comes at <strong>absolutely no additional cost to you</strong>. The merchant pays us a small percentage of the sale as a "thank you" for referring you to their site.</p>

                <h2>Does this influence our deals?</h2>
                <p><strong>No.</strong> Our proprietary algorithms discover deals based on actual price drops and historical data, not affiliate payout rates. If a deal is terrible, our AI scores it poorly, regardless of whether we make a commission. Our editorial team prioritizes the consumer's best interest above all else.</p>
            </div>
        </div>
    </div>
</div>
@endsection

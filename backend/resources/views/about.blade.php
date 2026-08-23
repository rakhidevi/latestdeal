@extends('layouts.app')

@section('meta')
    <title>About Us | LatestDeal.in</title>
    <meta name="description" content="Learn about LatestDeal.in, our mission to help you find the best verified deals, and our expert editorial team.">
    <link rel="canonical" href="{{ url('/about') }}">
@endsection

@section('content')
<div class="relative min-h-screen pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <div class="text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-red-50 border border-red-100 text-red-600 text-sm font-bold tracking-widest uppercase mb-4 shadow-sm">
                About Us
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-slate-800 tracking-tight mb-4">
                We Decode Deals So You <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">Save More</span>
            </h1>
            <p class="text-lg text-slate-500 font-medium max-w-2xl mx-auto">
                LatestDeal is a consumer shopping intelligence platform dedicated to bringing you verified discounts, historical price trends, and honest buying advice.
            </p>
        </div>

        <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 md:p-12 shadow-2xl shadow-slate-200/50">
            <div class="prose prose-lg prose-slate max-w-none prose-headings:font-black prose-headings:text-slate-800">
                <h2>Our Mission</h2>
                <p>
                    Online shopping has become a maze of fake discounts, inflated MRPs, and paid reviews. Our mission is to cut through the noise. We combine advanced data scraping, AI-assisted price analysis, and human editorial oversight to highlight only the <strong>genuine deals</strong> that are actually worth your money.
                </p>

                <h2>How We Find Deals</h2>
                <p>
                    We track millions of products across top e-commerce platforms like Amazon and Flipkart. Our proprietary algorithms monitor price drops 24/7. When a significant price drop is detected, our system verifies it against historical price data to ensure the discount is real (not just a fake drop from an artificially inflated original price).
                </p>

                <h2>Our Editorial Team</h2>
                <p>
                    While our bots discover the price drops, our Editorial Team curates the best offers. We write comprehensive buying guides, product comparisons, and deal summaries to help you make informed purchasing decisions. We never let AI invent specifications—every technical detail is sourced directly from verified manufacturer data.
                </p>
                
                <h2>Transparency & Affiliate Disclosure</h2>
                <p>
                    We believe in complete transparency. LatestDeal is completely free for users. To keep the lights on, we participate in affiliate programs, including the Amazon Associates Program. This means if you click on a deal link and make a purchase, we may earn a small commission at <strong>no extra cost to you</strong>. This does not influence our deal scoring or editorial recommendations.
                </p>

            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('meta')
    <title>Terms of Service | LatestDeal.in</title>
    <meta name="description" content="Read the Terms of Service for using LatestDeal.in, a consumer shopping intelligence platform.">
    <link rel="canonical" href="{{ url('/terms') }}">
@endsection

@section('content')
<div class="relative min-h-screen pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <div class="text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-sm font-bold tracking-widest uppercase mb-4 shadow-sm">
                Legal Info
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-slate-800 tracking-tight mb-4">
                Terms of <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Service</span>
            </h1>
            <p class="text-lg text-slate-500 font-medium max-w-2xl mx-auto">
                Please read these terms carefully before using our platform. Last updated on <span class="text-slate-700 font-bold">{{ date('F d, Y') }}</span>
            </p>
        </div>

        <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 md:p-12 shadow-2xl shadow-slate-200/50">
            <div class="prose prose-lg prose-slate max-w-none prose-headings:font-black prose-headings:text-slate-800">
                
                <h2>1. Acceptance of Terms</h2>
                <p>
                    By accessing and using LatestDeal.in, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our platform.
                </p>

                <h2>2. Service Description</h2>
                <p>
                    LatestDeal is a consumer shopping intelligence platform that provides product information, historical pricing data, AI-assisted summaries, and curated deal lists from third-party retailers (such as Amazon and Flipkart). We do not sell products directly.
                </p>

                <h2>3. Affiliate Disclosure & Transparency</h2>
                <p>
                    We participate in affiliate marketing programs. When you click on links to various merchants on this site and make a purchase, this can result in this site earning a commission. This comes at no additional cost to you and does not compromise the integrity of our editorial recommendations.
                </p>
                
                <h2>4. Accuracy of Information (Prices & Availability)</h2>
                <p>
                    While we strive for 100% accuracy, prices, stock availability, and promotional coupons on third-party sites are subject to change without notice. The final price you pay will be the price displayed at the merchant's checkout. We are not liable for any discrepancies between the price listed on LatestDeal and the final merchant price.
                </p>

                <h2>5. User Accounts & Data</h2>
                <p>
                    If you choose to create an account to save deals or set price alerts, you are responsible for maintaining the confidentiality of your account credentials. For information on how we handle your personal data, please read our <a href="/privacy">Privacy Policy</a>.
                </p>

                <h2>6. Limitation of Liability</h2>
                <p>
                    LatestDeal and its operators shall not be liable for any direct, indirect, incidental, or consequential damages resulting from your use of the site or your inability to use the site. You agree to use the site at your own risk.
                </p>
            </div>
        </div>

    </div>
</div>
@endsection

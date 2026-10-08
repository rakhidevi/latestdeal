@extends('layouts.app')

@section('meta')
    <title>Editorial Team | LatestDeal.in</title>
    <meta name="description" content="Meet the expert researchers, deal hunters, and product reviewers behind LatestDeal's shopping intelligence.">
    <link rel="canonical" href="{{ url('/editorial-team') }}">
@endsection

@section('content')
<div class="w-full max-w-4xl mx-auto py-8 sm:py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Our Editorial Team</h1>
        
        <p class="text-sm font-semibold text-gray-500 dark:text-slate-400 mb-6">Researchers &bull; Deal Analysts &bull; Consumer Advocates &bull; Updated {{ date('F Y') }}</p>

        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-6">
                Behind LatestDeal&rsquo;s algorithmic crawling sits an experienced team of consumer technology journalists, product reviewers, and price-trend analysts. We personally vet specifications, verify seller reputation, and author evergreen shopping guides.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-6">Core Editorial Desks</h2>

            <div class="not-prose grid grid-cols-1 md:grid-cols-2 gap-6 my-6">
                <!-- Editor 1 -->
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center font-bold text-lg">
                            PT
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900 dark:text-white">Pankaj Tiwari</h3>
                            <p class="text-xs text-red-600 dark:text-red-400 font-semibold">Lead Editor &bull; Consumer Tech &amp; AI</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-slate-300 leading-relaxed">
                        Specializes in consumer electronics, laptops, home automation, and pricing algorithms. Oversees our automated deal scoring engine and editorial buying guides.
                    </p>
                </div>

                <!-- Editor 2 -->
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-lg">
                            RD
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900 dark:text-white">Rakhi Devi</h3>
                            <p class="text-xs text-blue-600 dark:text-blue-400 font-semibold">Senior Deals Curator &bull; Home &amp; Kitchen</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-slate-300 leading-relaxed">
                        Focuses on appliances, home essentials, and lifestyle discounts. Conducts hands-on tests and audits seller credibility across major retail marketplaces.
                    </p>
                </div>

                <!-- Desk 3 -->
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-lg">
                            PI
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900 dark:text-white">Price Intelligence Desk</h3>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Autonomous Verification &bull; Multi-Store Engine</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-slate-300 leading-relaxed">
                        Maintains our 90-day baseline historical tracking and live multi-store comparison across Amazon, Flipkart, Croma, and Reliance Digital.
                    </p>
                </div>

                <!-- Desk 4 -->
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-lg">
                            QA
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900 dark:text-white">Quality Audit &amp; Corrections</h3>
                            <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold">Editorial Governance &bull; Fact-Checking</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-slate-300 leading-relaxed">
                        Reviews community correction submissions, verifies merchant warranties, and ensures complete adherence to our published Editorial Policy.
                    </p>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Editorial Integrity &amp; Transparency</h2>
            <p class="text-base text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                Our editorial team operates with total independence. We adhere to clear, immutable guidelines:
            </p>
            <ul class="list-disc pl-6 space-y-2 mt-4 mb-6 text-base text-gray-600 dark:text-slate-300 leading-relaxed">
                <li><strong class="text-gray-900 dark:text-white font-semibold">No Commercial Interference:</strong> Editorial recommendations and verdict badges are never sold or influenced by commissions.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Fact-Checking Priority:</strong> Specifications are cross-checked against official OEM sheets rather than reseller marketing blurbs.</li>
                <li><strong class="text-gray-900 dark:text-white font-semibold">Prompt Corrections:</strong> When errors are flagged, we remediate them within 24 hours under our <a href="/corrections-policy" class="text-red-600 hover:underline font-semibold">Corrections Policy</a>.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

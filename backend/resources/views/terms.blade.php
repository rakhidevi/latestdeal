@extends('layouts.app')

@section('meta')
    <title>Terms and Conditions - LatestDeal</title>
@endsection

@section('content')
<div class="relative min-h-screen pt-24 pb-20">
    <!-- Background Decorators -->
    <div class="absolute top-0 inset-x-0 h-96 bg-gradient-to-b from-emerald-50/50 to-transparent -z-10"></div>
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-emerald-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
    <div class="absolute top-20 -left-20 w-72 h-72 bg-sky-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <!-- Header Section -->
        <div class="text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-sm font-bold tracking-widest uppercase mb-4 shadow-sm">
                Legal Agreement
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-slate-800 tracking-tight mb-4">
                Terms and <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-sky-600">Conditions</span>
            </h1>
            <p class="text-lg text-slate-500 font-medium max-w-2xl mx-auto">
                Please read these terms carefully before using our platform. Last updated on <span class="text-slate-700 font-bold">{{ date('F d, Y') }}</span>
            </p>
        </div>

        <!-- Glassmorphic Content Card -->
        <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 md:p-12 shadow-2xl shadow-slate-200/50">
            
            <div class="prose prose-lg prose-slate max-w-none prose-headings:font-black prose-headings:text-slate-800 prose-a:text-emerald-600 hover:prose-a:text-emerald-500 prose-p:text-slate-600 prose-p:leading-relaxed">
                
                <h2 class="flex items-center gap-3">
                    <i data-lucide="shield-check" class="w-8 h-8 text-emerald-500"></i>
                    1. Acceptance of Terms
                </h2>
                <p>
                    By accessing and using LatestDeal, you accept and agree to be bound by the terms and provision of this agreement. In addition, when using LatestDeal's particular services, you shall be subject to any posted guidelines or rules applicable to such services.
                </p>

                <div class="h-px w-full bg-gradient-to-r from-transparent via-slate-200 to-transparent my-8"></div>

                <h2 class="flex items-center gap-3">
                    <i data-lucide="layers" class="w-8 h-8 text-sky-500"></i>
                    2. Description of Service
                </h2>
                <p>
                    LatestDeal provides users with access to a rich collection of resources, including various communications tools, forums, shopping services, and personalized content. You also understand and agree that the service may include advertisements and that these advertisements are necessary for LatestDeal to provide the service.
                </p>
                <div class="bg-sky-50/50 border border-sky-100 rounded-2xl p-6 my-6 flex items-start gap-4">
                    <div class="p-2 bg-sky-100 rounded-lg shrink-0">
                        <i data-lucide="info" class="w-5 h-5 text-sky-600"></i>
                    </div>
                    <p class="m-0 text-sm text-sky-800">
                        <strong>Note:</strong> We utilize advanced AI models to fetch, curate, and summarize deals. While we strive for absolute accuracy, prices and availability on third-party merchant sites may fluctuate rapidly.
                    </p>
                </div>

                <div class="h-px w-full bg-gradient-to-r from-transparent via-slate-200 to-transparent my-8"></div>

                <h2 class="flex items-center gap-3">
                    <i data-lucide="link" class="w-8 h-8 text-indigo-500"></i>
                    3. Affiliate Links Disclosure
                </h2>
                <p>
                    Some of the links on LatestDeal are affiliate links. This means that if you click on the link and purchase the item, LatestDeal will receive an affiliate commission at no extra cost to you. We only recommend products or services we believe will add value to our readers.
                </p>
                <p>
                    Our use of affiliate links does not influence our AI's scoring metric or deal selection algorithm. All deals are evaluated objectively based on price drop history, brand credibility, and feature sets.
                </p>

                <div class="h-px w-full bg-gradient-to-r from-transparent via-slate-200 to-transparent my-8"></div>

                <h2 class="flex items-center gap-3">
                    <i data-lucide="users" class="w-8 h-8 text-amber-500"></i>
                    4. User Conduct
                </h2>
                <p>
                    You understand that all information, data, text, software, music, sound, photographs, graphics, video, messages or other materials, whether publicly posted or privately transmitted, are the sole responsibility of the person from which such content originated. You agree to not use the service to post content that is unlawful, harmful, or threatening.
                </p>

                <div class="h-px w-full bg-gradient-to-r from-transparent via-slate-200 to-transparent my-8"></div>
                
                <h2 class="flex items-center gap-3">
                    <i data-lucide="refresh-cw" class="w-8 h-8 text-rose-500"></i>
                    5. Modifications to Service
                </h2>
                <p>
                    LatestDeal reserves the right at any time and from time to time to modify or discontinue, temporarily or permanently, the service (or any part thereof) with or without notice. We shall not be liable to you or to any third party for any modification, price change, suspension, or discontinuance of the service.
                </p>

            </div>
        </div>

        <!-- Call to Action -->
        <div class="mt-12 text-center">
            <p class="text-slate-500 mb-6">Have questions about our terms?</p>
            <a href="mailto:support@latestdeal.in" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                <i data-lucide="mail" class="w-4 h-4"></i>
                Contact Support
            </a>
        </div>

    </div>
</div>
@endsection

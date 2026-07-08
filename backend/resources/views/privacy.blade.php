@extends('layouts.app')

@section('meta')
    <title>Privacy Policy - LatestDeal</title>
@endsection

@section('content')
<div class="relative min-h-screen pt-24 pb-20">
    <!-- Background Decorators -->
    <div class="absolute top-0 inset-x-0 h-96 bg-gradient-to-b from-sky-50/50 to-transparent -z-10"></div>
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-sky-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
    <div class="absolute top-20 -right-20 w-72 h-72 bg-emerald-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <!-- Header Section -->
        <div class="text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-sky-50 border border-sky-100 text-sky-600 text-sm font-bold tracking-widest uppercase mb-4 shadow-sm">
                Data Protection
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-slate-800 tracking-tight mb-4">
                Privacy <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-emerald-600">Policy</span>
            </h1>
            <p class="text-lg text-slate-500 font-medium max-w-2xl mx-auto">
                How we collect, use, and protect your data. Last updated on <span class="text-slate-700 font-bold">{{ date('F d, Y') }}</span>
            </p>
        </div>

        <!-- Glassmorphic Content Card -->
        <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 md:p-12 shadow-2xl shadow-slate-200/50">
            
            <div class="prose prose-lg prose-slate max-w-none prose-headings:font-black prose-headings:text-slate-800 prose-a:text-sky-600 hover:prose-a:text-sky-500 prose-p:text-slate-600 prose-p:leading-relaxed">
                
                <h2 class="flex items-center gap-3">
                    <i data-lucide="database" class="w-8 h-8 text-sky-500"></i>
                    1. Information We Collect
                </h2>
                <p>
                    We collect information you provide directly to us, such as when you create or modify your account, subscribe to price alerts, contact customer support, or otherwise communicate with us. This information may include your name, email address, and saved deal preferences.
                </p>

                <div class="h-px w-full bg-gradient-to-r from-transparent via-slate-200 to-transparent my-8"></div>

                <h2 class="flex items-center gap-3">
                    <i data-lucide="cpu" class="w-8 h-8 text-emerald-500"></i>
                    2. How We Use Information
                </h2>
                <p>
                    We use the information we collect about you to provide, maintain, and improve our services, including to facilitate notifications, send price drop alerts, provide deals you request, develop new features, authenticate users, and send product updates.
                </p>
                <div class="bg-emerald-50/50 border border-emerald-100 rounded-2xl p-6 my-6 flex items-start gap-4">
                    <div class="p-2 bg-emerald-100 rounded-lg shrink-0">
                        <i data-lucide="shield" class="w-5 h-5 text-emerald-600"></i>
                    </div>
                    <p class="m-0 text-sm text-emerald-800">
                        <strong>Privacy Commitment:</strong> We never sell your personal data to third parties or data brokers. Your information is strictly used to improve your shopping experience.
                    </p>
                </div>

                <div class="h-px w-full bg-gradient-to-r from-transparent via-slate-200 to-transparent my-8"></div>

                <h2 class="flex items-center gap-3">
                    <i data-lucide="share-2" class="w-8 h-8 text-indigo-500"></i>
                    3. Sharing of Information
                </h2>
                <p>
                    We may share the information we collect about you as described in this Statement or as described at the time of collection or sharing, including with third parties to provide you a service you requested through a partnership or promotional offering made by a third party or us.
                </p>

                <div class="h-px w-full bg-gradient-to-r from-transparent via-slate-200 to-transparent my-8"></div>

                <h2 class="flex items-center gap-3">
                    <i data-lucide="cookie" class="w-8 h-8 text-amber-500"></i>
                    4. Cookies and Tracking
                </h2>
                <p>
                    We use cookies and similar technologies for purposes such as authenticating users, remembering user preferences, determining the popularity of content, and analyzing site traffic. As an affiliate platform, tracking cookies are utilized by our merchant partners (like Amazon) when you click "Buy Now" to attribute the sale to LatestDeal.
                </p>

                <div class="h-px w-full bg-gradient-to-r from-transparent via-slate-200 to-transparent my-8"></div>
                
                <h2 class="flex items-center gap-3">
                    <i data-lucide="user-x" class="w-8 h-8 text-rose-500"></i>
                    5. Your Rights & Data Deletion
                </h2>
                <p>
                    You have the right to access, modify, or delete your personal data at any time. You can delete your account directly from your Profile settings. Upon deletion, all associated data, including saved deals and price alerts, will be permanently removed from our servers.
                </p>

            </div>
        </div>

        <!-- Call to Action -->
        <div class="mt-12 text-center">
            <p class="text-slate-500 mb-6">Have questions about our privacy practices?</p>
            <a href="mailto:privacy@latestdeal.in" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                <i data-lucide="mail" class="w-4 h-4"></i>
                Contact Privacy Team
            </a>
        </div>

    </div>
</div>
@endsection

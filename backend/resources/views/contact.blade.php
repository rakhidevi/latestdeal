@extends('layouts.app')

@section('meta')
    <title>Contact Us | LatestDeal.in</title>
    <meta name="description" content="Get in touch with the LatestDeal team for support, business inquiries, or to report an expired deal.">
    <link rel="canonical" href="{{ url('/contact') }}">
@endsection

@section('content')
<div class="relative min-h-screen pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <div class="text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-bold tracking-widest uppercase mb-4 shadow-sm">
                Get in Touch
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-slate-800 tracking-tight mb-4">
                Contact <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-600">Us</span>
            </h1>
            <p class="text-lg text-slate-500 font-medium max-w-2xl mx-auto">
                Have a question, suggestion, or found a bug? We'd love to hear from you.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Contact Info -->
            <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-8 shadow-xl shadow-slate-200/50">
                <h3 class="text-2xl font-bold text-slate-800 mb-6">Contact Information</h3>
                
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800">Email Support</h4>
                            <p class="text-slate-500 text-sm mt-1">For general queries and deal reporting:<br>
                            <a href="mailto:support@latestdeal.in" class="text-blue-600 hover:underline">support@latestdeal.in</a></p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800">Business & Partnerships</h4>
                            <p class="text-slate-500 text-sm mt-1">For affiliate partnerships and PR:<br>
                            <a href="mailto:business@latestdeal.in" class="text-emerald-600 hover:underline">business@latestdeal.in</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operating Hours -->
            <div class="bg-slate-900 rounded-3xl p-8 shadow-xl text-white">
                <h3 class="text-2xl font-bold mb-6">Operating Hours</h3>
                <p class="text-slate-400 mb-6">Our automated systems scan for deals 24/7. Our editorial and support team operates during standard business hours in India (IST).</p>
                
                <ul class="space-y-3 border-t border-slate-700 pt-6">
                    <li class="flex justify-between">
                        <span class="text-slate-300">Monday - Friday</span>
                        <span class="font-semibold text-white">9:00 AM - 6:00 PM</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-slate-300">Saturday</span>
                        <span class="font-semibold text-white">10:00 AM - 2:00 PM</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-slate-400">Sunday</span>
                        <span class="font-semibold text-slate-500">Closed</span>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>
@endsection

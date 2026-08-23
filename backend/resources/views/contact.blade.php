@extends('layouts.app')

@section('meta')
    <title>Contact Us | LatestDeal</title>
    <meta name="description" content="Get in touch with the LatestDeal team for support, business inquiries, or to report an expired deal.">
    <link rel="canonical" href="{{ url('/contact') }}">
@endsection

@section('content')
<x-info.page-container>
    <x-info.page-header title="Contact Us" label="We're here to help">
        Have a question, suggestion, or found a bug? We'd love to hear from you.
    </x-info.page-header>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-16">
        <x-info.contact-card 
            title="General Support" 
            description="Questions about LatestDeal or your account." 
            email="support@latestdeal.in" 
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.36 6.64a9 9 0 11-12.73 0"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v10"></path></svg>'
        />
        <x-info.contact-card 
            title="Deal Issue" 
            description="Report incorrect pricing or deal information." 
            email="report@latestdeal.in" 
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>'
        />
        <x-info.contact-card 
            title="Business / Partnership" 
            description="Merchant and partnership enquiries." 
            email="business@latestdeal.in" 
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>'
        />
    </div>

    <!-- Contact Form -->
    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm p-8 md:p-12">
        <h2 class="text-2xl font-black text-slate-900 mb-6">Send us a message</h2>
        <form action="#" method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-bold text-slate-700 mb-2">Your Name</label>
                    <input type="text" id="name" name="name" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" placeholder="John Doe">
                </div>
                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                    <input type="email" id="email" name="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" placeholder="john@example.com">
                </div>
            </div>
            
            <div>
                <label for="topic" class="block text-sm font-bold text-slate-700 mb-2">Topic</label>
                <select id="topic" name="topic" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all appearance-none">
                    <option value="general">General Support</option>
                    <option value="issue">Report a Deal Issue</option>
                    <option value="business">Business Partnership</option>
                </select>
            </div>

            <div>
                <label for="message" class="block text-sm font-bold text-slate-700 mb-2">Message</label>
                <textarea id="message" name="message" rows="5" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" placeholder="How can we help you?"></textarea>
            </div>

            <button type="button" class="bg-slate-900 text-white font-bold px-8 py-4 rounded-xl shadow-md hover:bg-slate-800 transition-colors w-full md:w-auto">
                Send Message
            </button>
        </form>
    </div>

</x-info.page-container>
@endsection

@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Contact Us</h1>
        
        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
            <p>We're always here to help. Whether you have a question about a deal, need to report an expired offer, or want to explore partnership opportunities, we'd love to hear from you.</p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">How to Reach Us</h2>
            <p>For all inquiries, please email our team at:</p>
            
            <div class="bg-slate-50 dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 my-6 inline-block">
                <a href="mailto:contact@latestdeal.in" class="text-red-600 dark:text-red-400 font-bold text-xl no-underline hover:underline">contact@latestdeal.in</a>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Reporting a Deal</h2>
            <p>If you found a deal on our site that is no longer active, or if the price at the retailer has changed from what we listed, please email us with the link to the deal page. Our editorial team will review and update it immediately.</p>
            
            <p class="mt-8">We aim to respond to all inquiries within 24-48 hours during regular business days.</p>
        </div>
    </div>
</div>
@endsection

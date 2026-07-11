@extends('admin.layout')

@section('title', 'AI Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative mb-6">
        {{ session('success') }}
    </div>
    @endif

    <div class="glass-panel rounded-3xl p-8 relative overflow-hidden shadow-lg bg-white">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                <i data-lucide="cpu" class="w-6 h-6"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">AI & Ollama Configuration</h2>
                <p class="text-slate-500 text-sm mt-1">Configure your local AI backend settings here without editing code.</p>
            </div>
        </div>

        <form action="{{ route('admin.settings.save') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Ollama Base URL -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Ollama Base URL / Tunnel</label>
                    <input type="url" name="ollama_base_url" 
                           value="{{ $settings['ollama_base_url'] ?? 'https://ai.latestdeal.in' }}"
                           placeholder="https://ai.latestdeal.in"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    <p class="text-xs text-slate-500 mt-2">The Cloudflare Zero Trust tunnel pointing to your desktop.</p>
                </div>

                <!-- Ollama Model -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">AI Model Name</label>
                    <input type="text" name="ollama_model" 
                           value="{{ $settings['ollama_model'] ?? 'llama3' }}"
                           placeholder="llama3, qwen2.5-coder:7b"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    <p class="text-xs text-slate-500 mt-2">Make sure you have pulled this model locally via <code>ollama pull</code>.</p>
                </div>
            </div>

            <hr class="border-slate-100 my-6">
            
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Auto-Summarize Deals</h3>
                    <p class="text-sm text-slate-500 mt-1">Automatically use AI to generate Pros & Cons for new deals in the background.</p>
                </div>
                <select name="ai_auto_summarize" class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:ring-2 focus:ring-indigo-500">
                    <option value="enabled" {{ ($settings['ai_auto_summarize'] ?? 'enabled') == 'enabled' ? 'selected' : '' }}>Enabled</option>
                    <option value="disabled" {{ ($settings['ai_auto_summarize'] ?? 'enabled') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <hr class="border-slate-100 my-8">

            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                    <i data-lucide="bot" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Crawler Settings</h2>
                    <p class="text-slate-500 text-sm mt-1">Enable or disable background data ingestion engines.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div class="flex items-center justify-between bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Automated Crawler (Telegram)</h3>
                        <p class="text-sm text-slate-500 mt-1">Listens to Telegram channels for real-time deals using Telethon.</p>
                    </div>
                    <select name="crawler_automated" class="px-4 py-2 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="enabled" {{ ($settings['crawler_automated'] ?? 'enabled') == 'enabled' ? 'selected' : '' }}>Enabled</option>
                        <option value="disabled" {{ ($settings['crawler_automated'] ?? 'enabled') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>

                <div class="flex items-center justify-between bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Manual Crawler (Playwright)</h3>
                        <p class="text-sm text-slate-500 mt-1">Classic DOM scraper that visits amazon links manually via Chromium.</p>
                    </div>
                    <select name="crawler_manual" class="px-4 py-2 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="enabled" {{ ($settings['crawler_manual'] ?? 'enabled') == 'enabled' ? 'selected' : '' }}>Enabled</option>
                        <option value="disabled" {{ ($settings['crawler_manual'] ?? 'enabled') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5">
                    Save AI Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

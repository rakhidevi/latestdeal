@extends('admin.layout')

@section('title', 'System Settings')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ activeTab: 'general' }">
    <!-- Header -->
    <div class="pb-2 border-b border-slate-200">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">System Settings</h1>
        <p class="text-sm text-slate-500">Configure global website branding, affiliate tags, mail delivery, and AI automation engines.</p>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-2 font-medium">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center gap-2 font-medium">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-2xl border border-slate-200 overflow-x-auto">
        <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all whitespace-nowrap">
            <i data-lucide="globe" class="w-4 h-4 text-blue-500"></i> General & Branding
        </button>
        <button @click="activeTab = 'affiliate'" :class="activeTab === 'affiliate' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all whitespace-nowrap">
            <i data-lucide="coins" class="w-4 h-4 text-amber-500"></i> Affiliate Networks
        </button>
        <button @click="activeTab = 'mail'" :class="activeTab === 'mail' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all whitespace-nowrap">
            <i data-lucide="mail" class="w-4 h-4 text-emerald-500"></i> Mail & SMTP
        </button>
        <button @click="activeTab = 'ai'" :class="activeTab === 'ai' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all whitespace-nowrap">
            <i data-lucide="cpu" class="w-4 h-4 text-purple-500"></i> AI & Scrapers
        </button>
        <button @click="activeTab = 'social'" :class="activeTab === 'social' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all whitespace-nowrap">
            <i data-lucide="share-2" class="w-4 h-4 text-cyan-500"></i> Social & Community
        </button>
    </div>

    <!-- Master Form -->
    <form action="{{ route('admin.settings.save') }}" method="POST">
        @csrf

        <!-- 1. General & Branding -->
        <div x-show="activeTab === 'general'" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-6">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="globe" class="w-4 h-4 text-blue-500"></i> General Site Configuration
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Website Name</label>
                    <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'LatestDeal.in' }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? '₹' }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-bold">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Site Tagline / Subtitle</label>
                    <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] ?? 'Curated Deals, Real Savings, Verified Daily' }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Public Support Email</label>
                    <input type="email" name="site_email" value="{{ $settings['site_email'] ?? 'support@latestdeal.in' }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Logo Image URL</label>
                    <input type="url" name="logo_url" value="{{ $settings['logo_url'] ?? '' }}" placeholder="https://latestdeal.in/images/logo.png" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>
            </div>
        </div>

        <!-- 2. Affiliate Networks -->
        <div x-show="activeTab === 'affiliate'" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-6">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="coins" class="w-4 h-4 text-amber-500"></i> Affiliate Tags & Network Credentials
            </h3>
            <p class="text-xs text-slate-500">These tags are automatically appended to destination store URLs during user redirection.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Amazon India Associate Tag</label>
                    <input type="text" name="amazon_associate_tag" value="{{ $settings['amazon_associate_tag'] ?? 'latestdeal03-21' }}" placeholder="e.g. yourtag-21" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                    <span class="text-[11px] text-slate-400">Injected as `tag=yourtag-21` into amazon.in links</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Flipkart Affiliate Tracking ID</label>
                    <input type="text" name="flipkart_affiliate_id" value="{{ $settings['flipkart_affiliate_id'] ?? 'latestdeal' }}" placeholder="e.g. aff_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                    <span class="text-[11px] text-slate-400">Injected as `affid=aff_id` into flipkart.com links</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Cuelinks Campaign ID (Optional)</label>
                    <input type="text" name="cuelinks_campaign_id" value="{{ $settings['cuelinks_campaign_id'] ?? '' }}" placeholder="e.g. 12345" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">EarnKaro Affiliate ID (Optional)</label>
                    <input type="text" name="earnkaro_affiliate_id" value="{{ $settings['earnkaro_affiliate_id'] ?? '' }}" placeholder="e.g. EK999" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>
            </div>
        </div>

        <!-- 3. Mail & SMTP -->
        <div x-show="activeTab === 'mail'" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="mail" class="w-4 h-4 text-emerald-500"></i> Mail & SMTP Credentials
                </h3>
                <span class="text-xs text-slate-400">Controls email deliverability for newsletters & alerts</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mail Driver</label>
                    <select name="mail_mailer" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                        <option value="smtp" {{ ($settings['mail_mailer'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP (Default)</option>
                        <option value="log" {{ ($settings['mail_mailer'] ?? '') === 'log' ? 'selected' : '' }}>Log (Local Debugging)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ $settings['mail_host'] ?? env('MAIL_HOST', 'mail.latestdeal.in') }}" placeholder="mail.latestdeal.in or smtp.gmail.com" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">SMTP Port</label>
                    <input type="number" name="mail_port" value="{{ $settings['mail_port'] ?? env('MAIL_PORT', 465) }}" placeholder="465 or 587" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">SMTP Username</label>
                    <input type="text" name="mail_username" value="{{ $settings['mail_username'] ?? env('MAIL_USERNAME', '') }}" placeholder="info@latestdeal.in" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">SMTP Password</label>
                    <input type="password" name="mail_password" value="{{ $settings['mail_password'] ?? '' }}" placeholder="••••••••••••" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Encryption</label>
                    <select name="mail_encryption" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                        <option value="ssl" {{ ($settings['mail_encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                        <option value="tls" {{ ($settings['mail_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Sender "From" Email Address</label>
                    <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] ?? 'deals@latestdeal.in' }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Sender "From" Display Name</label>
                    <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] ?? 'LatestDeal.in' }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>
            </div>
        </div>

        <!-- 4. AI & Scraper -->
        <div x-show="activeTab === 'ai'" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-6">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="cpu" class="w-4 h-4 text-purple-500"></i> AI Enrichment & Scraper Engine
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ollama / LLM Base URL</label>
                    <input type="url" name="ollama_base_url" value="{{ $settings['ollama_base_url'] ?? 'http://127.0.0.1:11434' }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Model Name</label>
                    <input type="text" name="ollama_model" value="{{ $settings['ollama_model'] ?? 'llama3.2:latest' }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>
            </div>

            <div class="space-y-3 pt-2">
                <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer">
                    <input type="checkbox" name="ai_auto_summarize" value="enabled" {{ ($settings['ai_auto_summarize'] ?? 'enabled') === 'enabled' ? 'checked' : '' }} class="w-5 h-5 rounded text-red-600 focus:ring-red-500 border-slate-300">
                    <div>
                        <span class="text-sm font-bold text-slate-800">Auto-Generate AI Editorial Reviews</span>
                        <p class="text-xs text-slate-500">Automatically produce pros/cons and verdict upon scraper ingestion.</p>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer">
                    <input type="checkbox" name="crawler_automated" value="enabled" {{ ($settings['crawler_automated'] ?? 'enabled') === 'enabled' ? 'checked' : '' }} class="w-5 h-5 rounded text-red-600 focus:ring-red-500 border-slate-300">
                    <div>
                        <span class="text-sm font-bold text-slate-800">Automated Hourly Ingestion Daemon</span>
                        <p class="text-xs text-slate-500">Allow worker process to poll discovery channels continuously.</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- 5. Social & Community -->
        <div x-show="activeTab === 'social'" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-6">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="share-2" class="w-4 h-4 text-cyan-500"></i> Broadcast & Social Communities
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Telegram Deals Channel URL</label>
                    <input type="url" name="telegram_channel_url" value="{{ $settings['telegram_channel_url'] ?? 'https://t.me/latestdealin' }}" placeholder="https://t.me/..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">WhatsApp Deals Community URL</label>
                    <input type="url" name="whatsapp_group_url" value="{{ $settings['whatsapp_group_url'] ?? '' }}" placeholder="https://chat.whatsapp.com/..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Master Save Button -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <button type="submit" class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-500/20 transition-all transform active:scale-95 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Save All Settings
            </button>
        </div>
    </form>

    <!-- Standalone SMTP Diagnostic Tester -->
    <div x-show="activeTab === 'mail'" class="bg-slate-50 rounded-2xl p-6 border border-dashed border-slate-300 space-y-4">
        <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <i data-lucide="activity" class="w-4 h-4 text-emerald-600"></i> Test Live SMTP Connection
        </h4>
        <p class="text-xs text-slate-500">Send an instant test ping message to verify credentials before saving.</p>

        <form action="{{ route('admin.settings.test-smtp') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <input type="email" name="test_email" required value="{{ auth()->user()->email ?? '' }}" placeholder="Enter destination email..." class="flex-1 px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center justify-center gap-1.5">
                <i data-lucide="send" class="w-3.5 h-3.5"></i> Send Diagnostic Ping
            </button>
        </form>
    </div>
</div>
@endsection

@extends('admin.layout')

@section('title', 'Crawler Engine & System Operations')

@section('content')
<div class="space-y-8" x-data="scraperOps()" x-init="init()">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Crawler Operations & System Utilities</h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                      :class="isWorkerOnline ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'">
                    <span class="w-2 h-2 rounded-full" :class="isWorkerOnline ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                    <span x-text="workerStatusText">Checking Worker...</span>
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Manage background product ingestion queues, monitor headless scraper workers, and run maintenance tasks.</p>
        </div>

        <!-- Top Action Buttons -->
        <div class="flex items-center gap-2">
            <button @click="showArchitectureModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition">
                <i data-lucide="help-circle" class="w-4 h-4 text-slate-500"></i>
                <span>How Scraping Works</span>
            </button>
            <button @click="fetchStatus()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 transition">
                <i data-lucide="refresh-cw" class="w-4 h-4" :class="isPolling ? 'animate-spin' : ''"></i>
                <span>Refresh Status</span>
            </button>
        </div>
    </div>

    <!-- Metrics Strip -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Scrape Jobs</span>
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                    <i data-lucide="cpu" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900 mt-2">{{ number_format($metrics['total_scraped'] ?? 0) }}</p>
            <p class="text-xs text-slate-500 mt-1">Ingested via worker queues</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Active Deals</span>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-emerald-600 mt-2">{{ number_format($metrics['accepted'] ?? 0) }}</p>
            <p class="text-xs text-slate-500 mt-1">Live on store & published</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Rejected / Fake</span>
                <div class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-rose-600 mt-2">{{ number_format($metrics['rejected'] ?? 0) }}</p>
            <p class="text-xs text-slate-500 mt-1">Filtered by editorial gate</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Expired Purged</span>
                <div class="p-2 bg-slate-100 text-slate-600 rounded-xl">
                    <i data-lucide="archive" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-700 mt-2">{{ number_format($metrics['expired'] ?? 0) }}</p>
            <p class="text-xs text-slate-500 mt-1">Priced out or out-of-stock</p>
        </div>
    </div>

    <!-- Architecture & Operation Banner -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl p-6 shadow-md border border-slate-800">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-medium border border-indigo-400/20">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    <span>Hybrid Scraper Architecture</span>
                </div>
                <h3 class="text-lg font-bold text-white">How LatestDeal Ingests Amazon & Flipkart Deals</h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Shared web hosting (cPanel/MilesWeb) blocks headless Chromium browsers and proxy pools. To maintain 100% uptime without IP bans, scraping runs on your <strong>Local Machine</strong> via <code class="bg-black/40 px-1.5 py-0.5 rounded text-indigo-300 font-mono text-[11px]">worker/main.py</code>, while this web dashboard manages queues and receives cleaned deals.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button @click="startScraper()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-900/40 transition">
                    <i data-lucide="play" class="w-4 h-4"></i>
                    <span>Queue Worker Wakeup</span>
                </button>
                <button @click="stopScraper()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-900/40 transition">
                    <i data-lucide="square" class="w-4 h-4"></i>
                    <span>Emergency Stop</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Command Execution Feedback -->
    @if(session('action_output'))
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-emerald-800 font-bold text-sm flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    Command Completed Successfully
                </h4>
            </div>
            <pre class="text-emerald-900 font-mono text-xs bg-emerald-100/50 p-3 rounded-xl border border-emerald-200 overflow-x-auto whitespace-pre-wrap">{{ session('action_output') }}</pre>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-rose-800 font-bold text-sm flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                    Command Execution Error
                </h4>
            </div>
            <pre class="text-rose-900 font-mono text-xs bg-rose-100/50 p-3 rounded-xl border border-rose-200 overflow-x-auto whitespace-pre-wrap">{{ session('error') }}</pre>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Quick Ingestion & Worker Heartbeat -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Ingest Single Product URL -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center gap-2 text-slate-900 font-bold">
                    <div class="p-2 bg-red-50 text-red-600 rounded-xl">
                        <i data-lucide="link" class="w-4 h-4"></i>
                    </div>
                    <span>Queue Product URL</span>
                </div>
                <p class="text-xs text-slate-500">Paste any Amazon.in or Flipkart.com product link to immediately extract price, MRP, images, and generate affiliate tags.</p>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Extraction Mode</label>
                        <select x-model="scrapeMode" class="w-full text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-red-500 focus:outline-none">
                            <option value="ingestion">Standard Bot (Background API / Fast)</option>
                            <option value="sitestripe_automation">SiteStripe Bot (Real Browser Automation)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Product Link</label>
                        <input type="url" x-model="scrapeUrlInput" placeholder="https://www.amazon.in/dp/B0..." class="w-full text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>

                    <button @click="submitScrape()" :disabled="isSubmitting || !scrapeUrlInput" class="w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4" x-show="!isSubmitting"></i>
                        <svg class="animate-spin h-4 w-4" x-show="isSubmitting" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isSubmitting ? 'Queueing URL...' : 'Add to Scraper Queue'"></span>
                    </button>

                    <div x-show="submitMessage" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700" x-text="submitMessage"></div>
                </div>
            </div>

            <!-- Worker Heartbeat Live Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-900 font-bold">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                        </div>
                        <span>Worker Heartbeat</span>
                    </div>
                    <span class="text-[11px] font-mono text-slate-400" x-text="lastPingTime ? 'Ping: ' + lastPingTime : 'Awaiting ping'"></span>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Worker Status:</span>
                        <span class="font-bold" :class="isWorkerOnline ? 'text-emerald-600' : 'text-slate-600'" x-text="workerStatusText"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Local Execution Command:</span>
                        <span class="font-mono text-[11px] font-bold text-slate-800">python worker/main.py</span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 leading-relaxed">
                    To start local background ingestion on your Windows machine, open PowerShell in the project directory and run <code class="bg-slate-100 px-1 py-0.5 rounded font-mono text-slate-700">python worker/main.py</code>. It automatically polls this table for jobs.
                </p>
            </div>
        </div>

        <!-- Right: System Artisan Utilities & Jobs Table -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- System Artisan Actions Hub -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <i data-lucide="terminal" class="w-4 h-4 text-slate-700"></i>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">System Artisan Commands</h2>
                    </div>
                    <span class="text-xs text-slate-400">Direct Server Utilities</span>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- 1. Application Cache -->
                        <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 transition bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">Clear Application Cache</span>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-600">cache:clear</span>
                            </div>
                            <p class="text-xs text-slate-500">Flushes Redis and File storage caches when deal prices appear cached or old.</p>
                            <form method="POST" action="{{ route('admin.actions.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="cache:clear">
                                <button type="submit" class="w-full py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-lg transition">
                                    Run Action
                                </button>
                            </form>
                        </div>

                        <!-- 2. Config Cache -->
                        <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 transition bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">Clear Configuration</span>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-600">config:clear</span>
                            </div>
                            <p class="text-xs text-slate-500">Reloads .env variables, SMTP credentials, and database settings immediately.</p>
                            <form method="POST" action="{{ route('admin.actions.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="config:clear">
                                <button type="submit" class="w-full py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-lg transition">
                                    Run Action
                                </button>
                            </form>
                        </div>

                        <!-- 3. View Cache -->
                        <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 transition bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">Clear Compiled Views</span>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-600">view:clear</span>
                            </div>
                            <p class="text-xs text-slate-500">Flushes compiled Blade templates. Use when recent layout modifications don't reflect.</p>
                            <form method="POST" action="{{ route('admin.actions.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="view:clear">
                                <button type="submit" class="w-full py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-lg transition">
                                    Run Action
                                </button>
                            </form>
                        </div>

                        <!-- 4. Optimize Clear -->
                        <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 transition bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">Optimize System (Clear All)</span>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-600">optimize:clear</span>
                            </div>
                            <p class="text-xs text-slate-500">Comprehensive cleanup executing cache, config, route, and view flushes in one stroke.</p>
                            <form method="POST" action="{{ route('admin.actions.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="optimize:clear">
                                <button type="submit" class="w-full py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-lg transition">
                                    Run Action
                                </button>
                            </form>
                        </div>

                        <!-- 5. Flush Failed Queue -->
                        <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 transition bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">Flush Failed Jobs</span>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-600">queue:flush</span>
                            </div>
                            <p class="text-xs text-slate-500">Purges deadlocked or expired background queue jobs from the database table.</p>
                            <form method="POST" action="{{ route('admin.actions.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="queue:flush">
                                <button type="submit" class="w-full py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-lg transition">
                                    Run Action
                                </button>
                            </form>
                        </div>

                        <!-- 6. Run Migrations -->
                        <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/30 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-amber-900">Run DB Migrations</span>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-amber-100 text-amber-800">migrate --force</span>
                            </div>
                            <p class="text-xs text-amber-700">Applies pending database schema migrations on the live production database.</p>
                            <form method="POST" action="{{ route('admin.actions.run') }}" onsubmit="return confirm('Execute pending database migrations on the live database?');">
                                @csrf
                                <input type="hidden" name="command" value="migrate">
                                <button type="submit" class="w-full py-2 px-3 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition">
                                    Run Live Migration
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Ingestion & Scraper Job Queue Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Crawler Job History & Queue</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Showing {{ $jobs->total() }} recent background scraping activities</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/50">
                                <th class="py-3.5 px-6">ID & Operation</th>
                                <th class="py-3.5 px-4">Type</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4">Started</th>
                                <th class="py-3.5 px-4">Duration</th>
                                <th class="py-3.5 px-6 text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($jobs as $job)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-6">
                                        <div class="font-bold text-slate-900">#{{ $job->id }} {{ $job->name }}</div>
                                        @if(!empty($job->payload) && is_array($job->payload) && isset($job->payload['url']))
                                            <div class="text-[11px] text-slate-400 font-mono truncate max-w-xs mt-0.5" title="{{ $job->payload['url'] }}">
                                                {{ $job->payload['url'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ $job->type }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if(in_array(strtolower($job->status), ['completed', 'success', 'done']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                                {{ strtoupper($job->status) }}
                                            </span>
                                        @elseif(in_array(strtolower($job->status), ['failed', 'failure', 'error']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                                {{ strtoupper($job->status) }}
                                            </span>
                                        @elseif(in_array(strtolower($job->status), ['processing', 'claimed', 'running']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <svg class="animate-spin w-3 h-3 text-blue-600" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                PROCESSING
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                {{ strtoupper($job->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
                                        {{ $job->started_at ? $job->started_at->diffForHumans() : $job->created_at->diffForHumans() }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 font-mono">
                                        {{ $job->duration_seconds ? $job->duration_seconds . 's' : '—' }}
                                    </td>
                                    <td class="py-3.5 px-6 text-right">
                                        <button @click="toggleLogs({{ $job->id }})" class="px-2.5 py-1 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                            View Logs
                                        </button>
                                    </td>
                                </tr>
                                <!-- Expandable Log Details -->
                                <tr id="logs-row-{{ $job->id }}" class="hidden bg-slate-900 text-slate-300">
                                    <td colspan="6" class="p-4 font-mono text-[11px] leading-relaxed">
                                        <div class="flex items-center justify-between mb-2 text-slate-400 border-b border-slate-800 pb-2">
                                            <span>Run #{{ $job->id }} Trace Logs</span>
                                            <span>Type: {{ $job->type }}</span>
                                        </div>
                                        @if(is_array($job->logs) && count($job->logs) > 0)
                                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                                @foreach($job->logs as $idx => $line)
                                                    <div class="flex gap-2">
                                                        <span class="text-slate-600 select-none">{{ $idx + 1 }}</span>
                                                        <span>{{ $line }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-slate-500 italic">No console logs recorded for this job.</p>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                        <p class="font-medium text-sm">No crawler runs recorded yet</p>
                                        <p class="text-xs mt-1">Queue a product link above to begin ingesting items.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($jobs->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $jobs->links() }}
                    </div>
                @endif
            </div>

        </div>

    </div>

    <!-- Architecture Modal -->
    <div x-show="showArchitectureModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showArchitectureModal = false" class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="info" class="w-5 h-5 text-indigo-600"></i>
                    How Crawler & Operations Work
                </h3>
                <button @click="showArchitectureModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div class="text-xs text-slate-600 space-y-3 leading-relaxed">
                <p>
                    <strong>1. Why is this separate from the web server?</strong><br>
                    E-commerce websites like Amazon and Flipkart utilize aggressive anti-bot protection (Cloudflare, CAPTCHA, IP rate limits). If your shared cPanel server makes thousands of curl requests, the shared IP quickly gets banned.
                </p>
                <p>
                    <strong>2. The Hybrid Solution:</strong><br>
                    - When you add a URL here, it goes into your database table <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">scraper_jobs</code>.<br>
                    - Your local computer runs <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">worker/main.py</code> which utilizes Playwright (a real Chrome browser) with residential IP routing.<br>
                    - The worker extracts discounts, verified prices, and high-res images, and publishes them back to LatestDeal.
                </p>
                <p>
                    <strong>3. System Commands:</strong><br>
                    Commands such as <em>Clear Cache</em> or <em>Run Migrations</em> allow you to run crucial Laravel artisan commands directly from your browser without opening an SSH terminal.
                </p>
            </div>

            <div class="pt-2 flex justify-end">
                <button @click="showArchitectureModal = false" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                    Got it!
                </button>
            </div>
        </div>
    </div>

</div>

<script>
    function toggleLogs(id) {
        const el = document.getElementById('logs-row-' + id);
        if (el) {
            el.classList.toggle('hidden');
        }
    }

    function scraperOps() {
        return {
            isWorkerOnline: false,
            workerStatusText: 'Checking...',
            lastPingTime: null,
            isPolling: false,
            showArchitectureModal: false,
            scrapeUrlInput: '',
            scrapeMode: 'ingestion',
            isSubmitting: false,
            submitMessage: '',
            
            init() {
                this.fetchStatus();
                // Check status every 15 seconds instead of spamming every second
                setInterval(() => this.fetchStatus(), 15000);
            },

            async fetchStatus() {
                this.isPolling = true;
                try {
                    const res = await fetch('{{ route("admin.scraper.status") }}');
                    if (res.ok) {
                        const data = await res.json();
                        this.isWorkerOnline = !!data.running;
                        this.workerStatusText = data.message || (data.running ? 'Worker Active' : 'Worker Offline / Standby');
                        this.lastPingTime = new Date().toLocaleTimeString();
                    }
                } catch (e) {
                    this.isWorkerOnline = false;
                    this.workerStatusText = 'Offline (Server error)';
                } finally {
                    this.isPolling = false;
                }
            },

            async submitScrape() {
                if (!this.scrapeUrlInput) return;
                this.isSubmitting = true;
                this.submitMessage = '';
                try {
                    const res = await fetch('{{ route("admin.scraper.scrape") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ url: this.scrapeUrlInput, type: this.scrapeMode })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.submitMessage = '✓ URL queued successfully! Worker will process it shortly.';
                        this.scrapeUrlInput = '';
                        setTimeout(() => this.submitMessage = '', 6000);
                    } else {
                        alert(data.error || 'Failed to queue URL');
                    }
                } catch (e) {
                    alert('Request failed. Please check network.');
                } finally {
                    this.isSubmitting = false;
                }
            },

            async startScraper() {
                try {
                    const res = await fetch('{{ route("admin.scraper.start") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                    const data = await res.json();
                    alert(data.message || 'Worker start signal sent');
                    this.fetchStatus();
                } catch (e) {
                    alert('Failed to send start signal');
                }
            },

            async stopScraper() {
                try {
                    const res = await fetch('{{ route("admin.scraper.stop") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                    const data = await res.json();
                    alert(data.message || 'Worker stop signal sent');
                    this.fetchStatus();
                } catch (e) {
                    alert('Failed to send stop signal');
                }
            }
        }
    }
</script>
@endsection

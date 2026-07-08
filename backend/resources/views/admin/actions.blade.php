@extends('layouts.app')

@section('content')
<div class="bg-[#0d1117] min-h-screen text-[#c9d1d9] font-sans">
    
    <!-- Top Stats Row -->
    <div class="border-b border-[#30363d] px-6 py-4 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-white flex items-center gap-2">
            <svg aria-hidden="true" height="24" viewBox="0 0 24 24" version="1.1" width="24" class="fill-current text-[#8b949e]">
                <path d="M11.93 8.5a4.002 4.002 0 0 1-7.78 0H2v-1h2.15a4.002 4.002 0 0 1 7.78 0H22v1h-10.07Zm-3.93-1.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5ZM20 14h2v1h-2v-1Zm0-4h2v1h-2v-1ZM2 14h2v1H2v-1Zm0-4h2v1H2v-1Zm7 4h13v1H9v-1Zm0-4h13v1H9v-1Z"></path>
            </svg>
            Actions
        </h1>
        <div class="flex gap-4">
            <div class="bg-[#161b22] border border-[#30363d] rounded-md px-4 py-2 flex flex-col items-center">
                <span class="text-xs text-[#8b949e] uppercase font-bold tracking-wider">Total Scraped</span>
                <span class="text-xl font-semibold text-white">{{ $metrics['total_scraped'] }}</span>
            </div>
            <div class="bg-[#161b22] border border-[#30363d] rounded-md px-4 py-2 flex flex-col items-center">
                <span class="text-xs text-[#238636] uppercase font-bold tracking-wider">Accepted</span>
                <span class="text-xl font-semibold text-[#3fb950]">{{ $metrics['accepted'] }}</span>
            </div>
            <div class="bg-[#161b22] border border-[#30363d] rounded-md px-4 py-2 flex flex-col items-center">
                <span class="text-xs text-[#da3633] uppercase font-bold tracking-wider">Rejected / Fake</span>
                <span class="text-xl font-semibold text-[#ff7b72]">{{ $metrics['rejected'] }}</span>
            </div>
            <div class="bg-[#161b22] border border-[#30363d] rounded-md px-4 py-2 flex flex-col items-center">
                <span class="text-xs text-[#8b949e] uppercase font-bold tracking-wider">Expired Removed</span>
                <span class="text-xl font-semibold text-white">{{ $metrics['expired'] }}</span>
            </div>
        </div>
    </div>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 border-r border-[#30363d] min-h-screen p-4 flex flex-col gap-6">
            <div>
                <a href="#" class="block px-3 py-2 text-sm font-semibold text-white bg-[#21262d] rounded-md flex items-center justify-between">
                    All workflows
                </a>
            </div>
            <div>
                <h3 class="px-3 text-xs font-semibold text-[#8b949e] uppercase tracking-wider mb-2">Ingestion</h3>
                <a href="#" class="block px-3 py-1.5 text-sm text-[#c9d1d9] hover:bg-[#161b22] rounded-md transition-colors">
                    Scrape Deal
                </a>
                <a href="#" class="block px-3 py-1.5 text-sm text-[#c9d1d9] hover:bg-[#161b22] rounded-md transition-colors">
                    Bulk Import
                </a>
            </div>
            <div>
                <h3 class="px-3 text-xs font-semibold text-[#8b949e] uppercase tracking-wider mb-2">Maintenance</h3>
                <a href="#" class="block px-3 py-1.5 text-sm text-[#c9d1d9] hover:bg-[#161b22] rounded-md transition-colors">
                    Expiry Checker
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-white">All workflows</span>
                </div>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 fill-current text-[#8b949e]" viewBox="0 0 16 16" width="16" height="16"><path d="M10.68 11.74a6 6 0 0 1-7.922-8.982 6 6 0 0 1 8.982 7.922l3.04 3.04a.749.749 0 0 1-.326 1.275.749.749 0 0 1-.734-.215ZM11.5 7a4.499 4.499 0 1 0-8.997 0A4.499 4.499 0 0 0 11.5 7Z"></path></svg>
                    <input type="text" placeholder="Filter workflow runs" class="bg-[#0d1117] border border-[#30363d] rounded-md text-sm pl-9 pr-3 py-1.5 focus:border-[#58a6ff] focus:ring-1 focus:ring-[#58a6ff] outline-none w-80 text-white placeholder-[#8b949e]">
                </div>
            </div>
            
            <!-- Live Scraper Terminal -->
            <div class="border border-[#30363d] rounded-md bg-[#0d1117] mb-6 flex flex-col" x-data="scraperTerminal()" x-init="init()">
                <div class="bg-[#161b22] px-4 py-3 border-b border-[#30363d] flex items-center justify-between rounded-t-md">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-white">Scraper Control Center</span>
                        <div class="flex items-center gap-1.5 border border-[#30363d] rounded-full px-2.5 py-0.5 bg-[#0d1117]">
                            <span class="relative flex h-2 w-2">
                                <span x-show="isRunning" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#3fb950] opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2" :class="isRunning ? 'bg-[#3fb950]' : 'bg-[#8b949e]'"></span>
                            </span>
                            <span class="text-xs font-mono" x-text="isRunning ? 'Running' : 'Idle'" :class="isRunning ? 'text-[#3fb950]' : 'text-[#8b949e]'"></span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="startScraper" x-show="!isRunning" class="text-xs bg-[#238636] hover:bg-[#2ea043] border border-[rgba(240,246,252,0.1)] text-white px-3 py-1 rounded-md font-semibold transition-colors">Start Worker</button>
                        <button @click="stopScraper" x-show="isRunning" class="text-xs bg-[#da3633] hover:bg-[#f85149] border border-[rgba(240,246,252,0.1)] text-white px-3 py-1 rounded-md font-semibold transition-colors">Stop Worker</button>
                    </div>
                </div>
                
                <div class="p-3 bg-[#0d1117] border-b border-[#30363d] flex gap-2">
                    <select x-model="scrapeMode" class="bg-[#010409] border border-[#30363d] rounded-md text-sm px-3 py-1.5 focus:border-[#58a6ff] focus:ring-1 focus:ring-[#58a6ff] outline-none text-white w-48">
                        <option value="ingestion">Standard Bot (Background)</option>
                        <option value="sitestripe_automation">SiteStripe (Real Browser)</option>
                    </select>
                    <input type="url" x-model="scrapeUrlInput" placeholder="Enter Amazon/Flipkart URL..." class="flex-1 bg-[#010409] border border-[#30363d] rounded-md text-sm px-3 py-1.5 focus:border-[#58a6ff] focus:ring-1 focus:ring-[#58a6ff] outline-none text-white placeholder-[#8b949e]">
                    <button @click="submitScrape" :disabled="!isRunning || isSubmitting" class="text-xs bg-[#21262d] hover:bg-[#30363d] border border-[#30363d] disabled:opacity-50 text-white px-4 py-1.5 rounded-md font-semibold transition-colors flex items-center">
                        <span x-show="!isSubmitting">Queue URL</span>
                        <span x-show="isSubmitting" class="flex items-center gap-2">
                            <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Sending...
                        </span>
                    </button>
                </div>
                
                <div class="bg-[#010409] text-[#c9d1d9] font-mono text-xs p-4 h-48 overflow-y-auto rounded-b-md" id="terminal-output">
                    <template x-for="(log, index) in logs" :key="index">
                        <div class="flex hover:bg-[#161b22] px-2 py-0.5">
                            <span class="text-[#8b949e] w-12 text-right select-none pr-3" x-text="index + 1"></span>
                            <span class="whitespace-pre-wrap" x-text="log" :class="log.includes('Failed') || log.includes('error') ? 'text-[#f85149]' : (log.includes('success') ? 'text-[#3fb950]' : '')"></span>
                        </div>
                    </template>
                    <div x-show="logs.length === 0" class="text-[#8b949e] italic px-2">Terminal standby. Awaiting background worker logs...</div>
                </div>
            </div>

            <!-- List Box -->
            <div class="border border-[#30363d] rounded-md bg-[#0d1117]">
                
                <div class="bg-[#161b22] px-4 py-3 border-b border-[#30363d] flex items-center justify-between rounded-t-md">
                    <span class="text-sm font-semibold text-white">{{ $jobs->total() }} workflow runs</span>
                </div>

                <div class="divide-y divide-[#30363d]">
                    @foreach($jobs as $job)
                        <div class="p-4 hover:bg-[#161b22] transition-colors flex flex-col gap-2 cursor-pointer" onclick="toggleLogs({{ $job->id }})">
                            <div class="flex items-start justify-between">
                                <div class="flex gap-3">
                                    <!-- Status Icon -->
                                    <div class="mt-0.5">
                                        @if($job->status === 'success')
                                            <svg class="fill-current text-[#3fb950]" viewBox="0 0 16 16" width="16" height="16"><path d="M8 16A8 8 0 1 1 8 0a8 8 0 0 1 0 16Zm3.78-9.72a.751.751 0 0 0-1.06-1.06L6.75 9.19 5.28 7.72a.751.751 0 0 0-1.06 1.06l2 2a.751.751 0 0 0 1.06 0l4.5-4.5Z"></path></svg>
                                        @elseif($job->status === 'failure')
                                            <svg class="fill-current text-[#f85149]" viewBox="0 0 16 16" width="16" height="16"><path d="M2.343 13.657A8 8 0 1 1 13.658 2.343 8 8 0 0 1 2.343 13.657ZM6.03 4.97a.751.751 0 0 0-1.042.018.751.751 0 0 0-.018 1.042L6.94 8 4.97 9.97a.749.749 0 0 0 .326 1.275.749.749 0 0 0 .734-.215L8 9.06l1.97 1.97a.749.749 0 0 0 1.275-.326.749.749 0 0 0-.215-.734L9.06 8l1.97-1.97a.749.749 0 0 0-.326-1.275.749.749 0 0 0-.734.215L8 6.94Z"></path></svg>
                                        @else
                                            <svg class="fill-current text-[#e3b341] animate-spin" viewBox="0 0 16 16" width="16" height="16"><path d="M8 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13ZM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8Z"></path></svg>
                                        @endif
                                    </div>

                                    <!-- Job Info -->
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-white text-base hover:text-[#58a6ff]">{{ $job->name }}</span>
                                            <span class="border border-[#30363d] text-[#8b949e] px-2 py-0.5 rounded-full text-xs font-medium bg-[#161b22]">{{ $job->type }}</span>
                                        </div>
                                        <div class="text-xs text-[#8b949e] mt-1 flex items-center gap-1">
                                            Scraper Run #{{ $job->id }}: Triggered automatically via {{ $job->type }} queue
                                        </div>
                                    </div>
                                </div>

                                <!-- Meta info -->
                                <div class="text-xs text-[#8b949e] flex flex-col items-end gap-1">
                                    <div class="flex items-center gap-1">
                                        <svg class="fill-current" viewBox="0 0 16 16" width="14" height="14"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0ZM1.5 8a6.5 6.5 0 1 0 13 0 6.5 6.5 0 0 0-13 0Zm7-3.25v2.992l2.028.812a.75.75 0 0 1-.557 1.392l-2.5-1A.751.751 0 0 1 7 8.25v-3.5a.75.75 0 0 1 1.5 0Z"></path></svg>
                                        {{ $job->started_at->diffForHumans() }}
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg class="fill-current" viewBox="0 0 16 16" width="14" height="14"><path d="M10.75 1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5a.75.75 0 0 1 .75-.75Zm-5.5 0a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5a.75.75 0 0 1 .75-.75ZM7.25 5v5.25a.75.75 0 0 0 1.5 0V5a.75.75 0 0 0-1.5 0ZM2.5 7.75A5.25 5.25 0 1 1 12.5 10a.75.75 0 0 0 1.5 0 6.75 6.75 0 1 0-12.87 2.876l-1.337 1.337A.75.75 0 0 0 .53 15.5h3.72a.75.75 0 0 0 .75-.75v-3.72a.75.75 0 0 0-1.28-.53l-1.124 1.124A5.239 5.239 0 0 1 2.5 7.75Z"></path></svg>
                                        {{ $job->duration_seconds ?? '...' }}s
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Logs Section (Hidden by default) -->
                            <div id="logs-{{ $job->id }}" class="hidden mt-4 bg-[#010409] border border-[#30363d] rounded-md p-4 font-mono text-xs overflow-x-auto text-[#c9d1d9]">
                                @if(is_array($job->logs) && count($job->logs) > 0)
                                    @foreach($job->logs as $index => $log)
                                        <div class="flex hover:bg-[#161b22] px-2 py-0.5">
                                            <span class="text-[#8b949e] w-8 text-right select-none pr-3">{{ $index + 1 }}</span>
                                            <span class="whitespace-pre-wrap">{{ $log }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-[#8b949e] px-2">No logs available for this run.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-center">
                {{ $jobs->links() }}
            </div>

        </div>
    </div>
</div>

<script>
    function toggleLogs(id) {
        const logsDiv = document.getElementById('logs-' + id);
        if (logsDiv.classList.contains('hidden')) {
            logsDiv.classList.remove('hidden');
        } else {
            logsDiv.classList.add('hidden');
        }
    }
    
    function scraperTerminal() {
        return {
            isRunning: false,
            logs: [],
            pollInterval: null,
            scrapeUrlInput: '',
            scrapeMode: 'ingestion',
            isSubmitting: false,
            init() {
                this.fetchStatus();
                this.pollInterval = setInterval(() => {
                    this.fetchStatus();
                }, 1000);
            },
            async fetchStatus() {
                try {
                    const response = await fetch('{{ route("admin.scraper.status") }}');
                    if (response.ok) {
                        const data = await response.json();
                        this.isRunning = data.running;
                        const oldLength = this.logs.length;
                        this.logs = data.logs || [];
                        if (this.logs.length !== oldLength && this.logs.length > 0) {
                            this.$nextTick(() => {
                                const el = document.getElementById('terminal-output');
                                if (el && (el.scrollHeight - el.scrollTop <= el.clientHeight + 100)) {
                                    el.scrollTop = el.scrollHeight;
                                }
                            });
                        }
                    }
                } catch (e) {
                    console.error("Failed to fetch scraper status:", e);
                }
            },
            async startScraper() {
                try {
                    await fetch('{{ route("admin.scraper.start") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                    this.fetchStatus();
                } catch (e) {
                    console.error("Failed to start scraper", e);
                }
            },
            async stopScraper() {
                try {
                    await fetch('{{ route("admin.scraper.stop") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                    this.fetchStatus();
                } catch (e) {
                    console.error("Failed to stop scraper", e);
                }
            },
            async submitScrape() {
                if (!this.scrapeUrlInput) return;
                this.isSubmitting = true;
                try {
                    await fetch('{{ route("admin.scraper.scrape") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ url: this.scrapeUrlInput, type: this.scrapeMode })
                    });
                    this.scrapeUrlInput = '';
                } catch (e) {
                    console.error("Failed to submit scrape", e);
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
</script>
@endsection

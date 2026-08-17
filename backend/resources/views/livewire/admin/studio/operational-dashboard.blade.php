<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Operational Dashboard (Continuous Shadow Mode)</h1>
        <p class="text-gray-600 mt-2">Validation Sprint 2: Real-time execution telemetry and Selector Health.</p>
    </div>

    @if(empty($runReports))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">No Signed Run Reports found. Start the Continuous Shadow Engine.</p>
                </div>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($runReports as $report)
                <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">
                            Run ID: <span class="font-mono text-sm text-blue-600">{{ substr($report['metrics']['run_id'], 0, 8) }}</span>
                        </h3>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            {{ $report['provider'] }}
                        </span>
                    </div>
                    
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-blue-50 p-3 rounded">
                                <p class="text-xs text-blue-500 uppercase font-bold tracking-wider">Extraction Success</p>
                                <p class="text-2xl font-bold text-blue-900">{{ $report['metrics']['extraction_success_pct'] }}%</p>
                            </div>
                            <div class="bg-purple-50 p-3 rounded">
                                <p class="text-xs text-purple-500 uppercase font-bold tracking-wider">Peak Memory (MB)</p>
                                <p class="text-2xl font-bold text-purple-900">{{ number_format($report['metrics']['memory_peak_mb'], 1) }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2 border-b pb-1">Operational Metrics</h4>
                            <div class="grid grid-cols-3 gap-2 text-sm text-gray-600">
                                <div><span class="font-medium">Attempted:</span> {{ $report['metrics']['targets_attempted'] }}</div>
                                <div><span class="font-medium">Succeeded:</span> {{ $report['metrics']['targets_succeeded'] }}</div>
                                <div><span class="font-medium">CAPTCHAs:</span> <span class="{{ $report['metrics']['captcha_hits'] > 0 ? 'text-red-500 font-bold' : '' }}">{{ $report['metrics']['captcha_hits'] }}</span></div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2 border-b pb-1">Selector Health Auditing</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-center text-xs">
                                @foreach($report['metrics']['selector_health'] as $selector => $health)
                                    <div class="p-2 rounded {{ $health >= 90 ? 'bg-green-50 text-green-700 border border-green-200' : ($health >= 70 ? 'bg-yellow-50 text-yellow-700 border border-yellow-200' : 'bg-red-50 text-red-700 border border-red-200') }}">
                                        <div class="font-bold uppercase">{{ $selector }}</div>
                                        <div>{{ $health }}%</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-2 border-t border-gray-200">
                        <p class="text-xs text-gray-400 font-mono break-all" title="Cryptographic Signature">
                            Sig: {{ $report['signature'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="h-[calc(100vh-100px)] flex flex-col p-4 bg-gray-50">
    <!-- Header -->
    <div class="mb-4 flex justify-between items-end">
        <div>
            <h3 class="text-2xl font-bold leading-6 text-gray-900">Strategy Analytics</h3>
            <p class="mt-2 text-sm text-gray-500">Discover which strategies, providers, and profiles are actually generating revenue.</p>
        </div>
        <div class="flex space-x-4">
            <div>
                <label class="block text-xs font-medium text-gray-700">Group By</label>
                <select wire:model.live="groupBy" class="mt-1 block w-40 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="strategy">Strategy</option>
                    <option value="provider">Provider</option>
                    <option value="profile">Discovery Profile</option>
                    <option value="brand">Brand</option>
                    <option value="category">Category</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Time Period</label>
                <select wire:model.live="timePeriod" class="mt-1 block w-40 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="today">Today</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Automated Insights (Computed Summaries) -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        @foreach($automatedInsights as $insight)
            <div class="bg-white rounded-lg shadow border border-gray-200 p-4 relative overflow-hidden group">
                <!-- Drill-down link overlay -->
                <a href="{{ route('admin.studio.universal-object-explorer', ['query' => $insight['trace_id']]) }}" class="absolute inset-0 z-10 hidden group-hover:block bg-indigo-50/10 cursor-pointer" title="Inspect source trace"></a>
                
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $insight['title'] }}</h4>
                <p class="text-lg font-bold text-gray-900 truncate">{{ $insight['value'] }}</p>
                
                <div class="mt-2 flex items-center">
                    @if($insight['trend'] === 'up')
                        <svg class="h-4 w-4 text-green-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        <span class="text-sm font-bold text-green-600">{{ $insight['metric'] }}</span>
                    @else
                        <svg class="h-4 w-4 text-red-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                        <span class="text-sm font-bold text-red-600">{{ $insight['metric'] }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Funnel Metrics Table -->
    <div class="flex-1 bg-white shadow rounded-lg border border-gray-200 overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3 sticky left-0 bg-gray-50 shadow-[1px_0_0_0_#e5e7eb] z-10">{{ ucfirst($groupBy) }}</th>
                        <!-- Discovery Funnel -->
                        <th class="px-4 py-3 text-right group relative">Generated <span class="hidden group-hover:inline absolute -top-8 left-0 bg-black text-white p-1 text-[10px] rounded">Total Targets Found</span></th>
                        <th class="px-4 py-3 text-right">Validated</th>
                        <th class="px-4 py-3 text-right text-indigo-700">Published</th>
                        <th class="px-4 py-3 text-right">Accept Rate</th>
                        <!-- Quality -->
                        <th class="px-4 py-3 text-right">Avg Opp Score</th>
                        <th class="px-4 py-3 text-right">Avg Conf</th>
                        <!-- User Funnel -->
                        <th class="px-4 py-3 text-right">CTR</th>
                        <th class="px-4 py-3 text-right">Conv. Rate</th>
                        <!-- Economics -->
                        <th class="px-4 py-3 text-right text-green-700 border-l border-gray-200">Revenue</th>
                        <th class="px-4 py-3 text-right text-green-700">ROI</th>
                        <th class="px-4 py-3 text-right">Cost/Crawl</th>
                        <th class="px-4 py-3 text-right font-bold text-indigo-700">Rev/Hour</th>
                        <!-- Ops -->
                        <th class="px-4 py-3 text-right border-l border-gray-200">Avg Run(ms)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($funnelMetrics as $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-bold text-gray-900 sticky left-0 bg-white shadow-[1px_0_0_0_#e5e7eb] group-hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <span class="truncate w-32" title="{{ $row['dimension'] }}">{{ $row['dimension'] }}</span>
                                    <a href="{{ route('admin.studio.universal-object-explorer', ['query' => $row['dimension_id']]) }}" class="text-indigo-600 hover:text-indigo-900 ml-2" title="Drill down in Object Explorer">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z" /></svg>
                                    </a>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500 font-mono">{{ number_format($row['generated']) }}</td>
                            <td class="px-4 py-3 text-right text-gray-500 font-mono">{{ number_format($row['validated']) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-indigo-600 font-mono">{{ number_format($row['published']) }}</td>
                            <td class="px-4 py-3 text-right font-mono {{ (float)$row['acceptance_rate'] < 5 ? 'text-red-500 font-bold' : 'text-gray-500' }}">{{ $row['acceptance_rate'] }}</td>
                            
                            <td class="px-4 py-3 text-right text-gray-500 font-mono">{{ $row['avg_opportunity_score'] }}</td>
                            <td class="px-4 py-3 text-right text-gray-500 font-mono">{{ $row['avg_confidence'] }}</td>
                            
                            <td class="px-4 py-3 text-right text-gray-500 font-mono">{{ $row['ctr'] }}</td>
                            <td class="px-4 py-3 text-right text-gray-500 font-mono">{{ $row['conversion_rate'] }}</td>
                            
                            <td class="px-4 py-3 text-right font-bold text-green-600 font-mono border-l border-gray-100">₹{{ number_format($row['revenue']) }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ (float)$row['roi'] > 100 ? 'text-green-600' : 'text-red-500' }} font-mono">{{ $row['roi'] }}</td>
                            <td class="px-4 py-3 text-right text-gray-500 font-mono">₹{{ number_format($row['cost_per_crawl'], 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-indigo-700 font-mono">₹{{ number_format($row['revenue_per_hour']) }}</td>
                            
                            <td class="px-4 py-3 text-right text-gray-400 font-mono border-l border-gray-100">{{ $row['avg_runtime_ms'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-4 py-8 text-center text-gray-500">No analytical data found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

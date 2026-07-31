<div>
    @section('title', 'Analytics')
    
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Analytics</h2>
            <p class="text-slate-500 mt-1">Monitor the performance of your marketing campaigns and subscriber engagement.</p>
        </div>
        <div class="flex gap-2 bg-white rounded-lg p-1 border border-slate-200 shadow-sm">
            <button wire:click="setTimeRange('7_days')" class="px-3 py-1.5 text-sm font-medium rounded-md {{ $timeRange === '7_days' ? 'bg-slate-100 text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">7 Days</button>
            <button wire:click="setTimeRange('30_days')" class="px-3 py-1.5 text-sm font-medium rounded-md {{ $timeRange === '30_days' ? 'bg-slate-100 text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">30 Days</button>
            <button wire:click="setTimeRange('all_time')" class="px-3 py-1.5 text-sm font-medium rounded-md {{ $timeRange === 'all_time' ? 'bg-slate-100 text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">All Time</button>
        </div>
    </div>

    <!-- Top Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col">
            <div class="text-sm font-medium text-slate-500 mb-1">Total Delivered</div>
            <div class="text-2xl font-bold text-slate-800">{{ number_format($metrics['delivered']) }}</div>
            <div class="mt-2 text-xs font-medium text-green-600 flex items-center gap-1">
                <i data-lucide="trending-up" class="w-3 h-3"></i> 97% delivery rate
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col">
            <div class="text-sm font-medium text-slate-500 mb-1">Open Rate</div>
            <div class="text-2xl font-bold text-slate-800">{{ round(($metrics['opened'] / $metrics['delivered']) * 100, 1) }}%</div>
            <div class="mt-2 text-xs font-medium text-green-600 flex items-center gap-1">
                <i data-lucide="trending-up" class="w-3 h-3"></i> +2.4% vs previous
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col">
            <div class="text-sm font-medium text-slate-500 mb-1">Click Rate</div>
            <div class="text-2xl font-bold text-slate-800">{{ round(($metrics['clicked'] / $metrics['opened']) * 100, 1) }}%</div>
            <div class="mt-2 text-xs font-medium text-green-600 flex items-center gap-1">
                <i data-lucide="trending-up" class="w-3 h-3"></i> +0.8% vs previous
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col">
            <div class="text-sm font-medium text-slate-500 mb-1">Unsubscribe Rate</div>
            <div class="text-2xl font-bold text-slate-800">{{ round(($metrics['unsubscribed'] / $metrics['delivered']) * 100, 2) }}%</div>
            <div class="mt-2 text-xs font-medium text-slate-500 flex items-center gap-1">
                <i data-lucide="minus" class="w-3 h-3"></i> No change
            </div>
        </div>
    </div>

    <!-- Charts Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Main Engagement Chart (Dummy) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-slate-800">Engagement Over Time</h3>
                <button class="text-slate-400 hover:text-slate-600"><i data-lucide="more-horizontal" class="w-5 h-5"></i></button>
            </div>
            <div class="h-64 flex items-end justify-between gap-2 px-2">
                <!-- Dummy Bars -->
                @for($i = 0; $i < 14; $i++)
                    <div class="w-full bg-blue-100 rounded-t-sm relative group cursor-pointer" style="height: {{ rand(30, 90) }}%;">
                        <div class="absolute bottom-0 w-full bg-blue-500 rounded-t-sm" style="height: {{ rand(10, 60) }}%;"></div>
                    </div>
                @endfor
            </div>
            <div class="flex justify-between mt-4 text-xs text-slate-400 px-2">
                <span>Start</span>
                <span>End</span>
            </div>
        </div>
        
        <!-- Deliverability Funnel (Dummy) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-bold text-slate-800 mb-6">Deliverability Funnel</h3>
            
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-700">Sent</span>
                        <span class="text-slate-500">{{ number_format($metrics['sent']) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-slate-300 h-2 rounded-full w-full"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-700">Delivered</span>
                        <span class="text-slate-500">{{ number_format($metrics['delivered']) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-blue-400 h-2 rounded-full" style="width: 97%"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-700">Opened</span>
                        <span class="text-slate-500">{{ number_format($metrics['opened']) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 48%"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-700">Clicked</span>
                        <span class="text-slate-500">{{ number_format($metrics['clicked']) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: 18%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Campaigns -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-800">Recent Campaigns Performance</h3>
            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700">View All</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Campaign</th>
                        <th class="px-6 py-3 font-semibold">Sent Date</th>
                        <th class="px-6 py-3 font-semibold text-right">Recipients</th>
                        <th class="px-6 py-3 font-semibold text-right">Open Rate</th>
                        <th class="px-6 py-3 font-semibold text-right">Click Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">Mega Deals - Weekend Special</div>
                            <div class="text-xs text-slate-500">Newsletter</div>
                        </td>
                        <td class="px-6 py-4 text-slate-500">Jul 28, 2026</td>
                        <td class="px-6 py-4 text-right font-medium text-slate-700">8,402</td>
                        <td class="px-6 py-4 text-right text-slate-600">52.4%</td>
                        <td class="px-6 py-4 text-right text-slate-600">8.1%</td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">VIP Exclusive Access</div>
                            <div class="text-xs text-slate-500">Promotional</div>
                        </td>
                        <td class="px-6 py-4 text-slate-500">Jul 25, 2026</td>
                        <td class="px-6 py-4 text-right font-medium text-slate-700">1,250</td>
                        <td class="px-6 py-4 text-right text-slate-600">68.2%</td>
                        <td class="px-6 py-4 text-right text-slate-600">14.5%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

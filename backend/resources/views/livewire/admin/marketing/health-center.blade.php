<div>
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-800">Health Center</h2>
        <p class="text-sm text-slate-500 mt-1">Platform monitoring and infrastructure health.</p>
    </div>

    <!-- Active Metrics -->
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Core Services</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Database -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-start justify-between">
                <div>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                        <i data-lucide="database" class="w-4 h-4"></i>
                    </div>
                    <h4 class="font-bold text-slate-800">Database</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Primary Connection</p>
                </div>
                <x-status-badge :status="$metrics['db_status']" />
            </div>

            <!-- Cache -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-start justify-between">
                <div>
                    <div class="w-8 h-8 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center mb-3">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                    </div>
                    <h4 class="font-bold text-slate-800">Cache</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Redis / File</p>
                </div>
                <x-status-badge :status="$metrics['cache_status']" />
            </div>

            <!-- Storage -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-start justify-between">
                <div>
                    <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center mb-3">
                        <i data-lucide="hard-drive" class="w-4 h-4"></i>
                    </div>
                    <h4 class="font-bold text-slate-800">Storage</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Permissions & Access</p>
                </div>
                <x-status-badge :status="$metrics['storage_status']" />
            </div>

        <!-- Disk Space -->
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-start justify-between">
            <div class="w-full">
                <div class="flex justify-between items-start mb-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i data-lucide="pie-chart" class="w-4 h-4"></i>
                    </div>
                    <x-status-badge :status="$metrics['disk_status']" />
                </div>
                <h4 class="font-bold text-slate-800">Disk Space</h4>
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2">
                    <div class="h-full rounded-full {{ $metrics['disk_percentage'] > 90 ? 'bg-red-500' : 'bg-purple-500' }}" style="width: {{ $metrics['disk_percentage'] }}%"></div>
                </div>
                <p class="text-xs text-slate-500 mt-1.5">{{ $metrics['disk_percentage'] }}% used</p>
            </div>
        </div>

        <!-- Queue -->
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-start justify-between">
            <div>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3">
                    <i data-lucide="list-ordered" class="w-4 h-4"></i>
                </div>
                <h4 class="font-bold text-slate-800">Queue</h4>
                <p class="text-xs text-slate-500 mt-0.5">Background Workers</p>
            </div>
            <x-status-badge :status="$metrics['queue_status']" />
        </div>

        <!-- Scheduler -->
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-start justify-between">
            <div>
                <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center mb-3">
                    <i data-lucide="calendar-clock" class="w-4 h-4"></i>
                </div>
                <h4 class="font-bold text-slate-800">Scheduler</h4>
                <p class="text-xs text-slate-500 mt-0.5">Cron Automation</p>
            </div>
            <x-status-badge :status="$metrics['scheduler_status']" />
        </div>

        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Environment Details -->
        <div>
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Environment Info</h3>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-medium text-slate-600">App Environment</td>
                            <td class="py-3 px-4 text-right">
                                <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs font-mono uppercase">{{ $metrics['environment'] }}</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-medium text-slate-600">PHP Version</td>
                            <td class="py-3 px-4 text-right font-mono text-slate-800">{{ $metrics['php_version'] }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-medium text-slate-600">Laravel Version</td>
                            <td class="py-3 px-4 text-right font-mono text-slate-800">{{ $metrics['laravel_version'] }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-medium text-slate-600">Queue Driver</td>
                            <td class="py-3 px-4 text-right font-mono text-slate-800">{{ $metrics['queue_driver'] }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-medium text-slate-600">Mail Driver</td>
                            <td class="py-3 px-4 text-right font-mono text-slate-800">{{ $metrics['mail_driver'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Advanced OS Metrics (Placeholders) -->
        <div>
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Server Metrics</h3>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative">
                
                <table class="w-full text-sm opacity-50">
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-3 px-4 font-medium text-slate-600">CPU Usage</td>
                            <td class="py-3 px-4 text-right font-mono">--</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-medium text-slate-600">RAM Usage</td>
                            <td class="py-3 px-4 text-right font-mono">--</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-medium text-slate-600">System Load (1m)</td>
                            <td class="py-3 px-4 text-right font-mono">--</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-medium text-slate-600">Network RX/TX</td>
                            <td class="py-3 px-4 text-right font-mono">--</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-medium text-slate-600">Active Processes</td>
                            <td class="py-3 px-4 text-right font-mono">--</td>
                        </tr>
                    </tbody>
                </table>

                <div class="absolute inset-0 flex flex-col items-center justify-center bg-white/60 backdrop-blur-[2px]">
                    <div class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-lg text-center">
                        <i data-lucide="lock" class="w-4 h-4 inline-block mr-1 mb-0.5"></i> Unavailable on current hosting<br>
                        <span class="text-xs text-slate-300 font-normal mt-1 block">Requires dedicated infrastructure monitoring.</span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

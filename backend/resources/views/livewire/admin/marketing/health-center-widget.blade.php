<div wire:poll.30s>
    <div class="mb-6 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800">Health Center</h3>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <tbody class="divide-y divide-gray-200 bg-white">
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 w-1/4">Worker Status</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $health->workerStatus === 'Running' ? 'text-green-600' : 'text-red-600' }}">
                        <div class="flex items-center">
                            <span class="flex h-2 w-2 rounded-full mr-2 {{ $health->workerStatus === 'Running' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ $health->workerStatus }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Database</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $health->databaseConnection === 'Connected' ? 'text-green-600' : 'text-red-600' }}">{{ $health->databaseConnection }}</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Cache</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $health->cacheConnection === 'Connected' ? 'text-green-600' : 'text-red-600' }}">{{ $health->cacheConnection }}</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Storage Writable</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $health->storageWritable ? 'text-green-600' : 'text-red-600' }}">{{ $health->storageWritable ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Scheduler</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $health->schedulerRunning ? 'text-green-600' : 'text-red-600' }}">
                        {{ $health->schedulerRunning ? 'Running' : 'Offline' }} 
                        <span class="text-gray-400 ml-2">(Last run: {{ $health->schedulerLastRun }})</span>
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">Mail Provider</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <div class="font-medium text-gray-900 mb-1">{{ $health->mailProvider }}</div>
                        <div class="text-xs text-gray-500">Last Success: {{ $health->mailLastSuccess }}</div>
                        <div class="text-xs text-gray-500">Last Failure: {{ $health->mailLastFailure }}</div>
                        <div class="text-xs mt-1 {{ $health->mailConsecutiveFailures > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                            Consecutive Failures: {{ $health->mailConsecutiveFailures }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50" colspan="2">System Information</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">PHP Version</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $health->phpVersion }}</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Laravel Version</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $health->laravelVersion }}</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Disk Usage</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $health->diskUsage }}</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Memory Usage</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $health->memoryUsage }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

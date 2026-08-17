<div>
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">Production Canary Rollout</h1>
                <p class="mt-2 text-sm text-gray-700">Monitor the current 5% rollout of the New Discovery Platform.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                    Status: {{ $canaryStatus }}
                </span>
            </div>
        </div>

        <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500">Current Rollout</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $globalPercentage }}%</dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500">Amazon Health</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $providerHealth['amazon'] }}%</dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500">Revenue Trend (vs Legacy)</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-green-600">{{ $revenueTrend }}</dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500">Rollback Events</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ count($rollbackHistory) }}</dd>
            </div>
        </dl>
    </div>
</div>

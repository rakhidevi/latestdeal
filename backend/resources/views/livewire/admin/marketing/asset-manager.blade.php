<div>
    <div class="mb-6 border-b border-gray-200 pb-5">
        <h3 class="text-2xl font-bold leading-6 text-gray-900">Asset Manager</h3>
    </div>
    <div class="bg-white shadow rounded-lg border border-gray-200 p-6">
        <ul class="divide-y divide-gray-200">
            @foreach($assets as $asset)
            <li class="py-4 flex space-x-4">
                <div class="flex-1">
                    <p class="font-bold text-gray-900">{{ $asset['name'] }}</p>
                    <p class="text-sm text-gray-500">{{ $asset['type'] }} ({{ $asset['size'] }})</p>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
</div>

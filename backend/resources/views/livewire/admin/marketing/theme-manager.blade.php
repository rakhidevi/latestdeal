<div>
    <div class="mb-6 border-b border-gray-200 pb-5">
        <h3 class="text-2xl font-bold leading-6 text-gray-900">Theme Manager</h3>
    </div>
    <div class="grid grid-cols-2 gap-4">
        @foreach($themes as $theme)
        <div class="bg-white shadow rounded-lg border border-gray-200 p-6">
            <h4 class="font-bold mb-2">{{ $theme['name'] }}</h4>
            <div class="flex space-x-2">
                <div class="w-8 h-8 rounded" style="background-color: {{ $theme['primary'] }}"></div>
                <div class="w-8 h-8 rounded border border-gray-300" style="background-color: {{ $theme['secondary'] }}"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>

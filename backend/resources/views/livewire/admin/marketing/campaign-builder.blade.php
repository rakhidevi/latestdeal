<div>
    <div class="mb-6 border-b border-gray-200 pb-5">
        <h3 class="text-2xl font-bold leading-6 text-gray-900">Campaign Builder</h3>
    </div>
    <div class="bg-white shadow rounded-lg border border-gray-200 p-6">
        @if(session()->has('success'))
            <div class="mb-4 text-green-700 bg-green-50 p-3 rounded border border-green-200">{{ session('success') }}</div>
        @endif
        <input type="text" wire:model.defer="campaignName" placeholder="Campaign Name" class="mb-4 block w-full rounded border-gray-300">
        <button wire:click="saveCampaign" class="bg-indigo-600 text-white px-4 py-2 rounded">Launch Campaign</button>
    </div>
</div>

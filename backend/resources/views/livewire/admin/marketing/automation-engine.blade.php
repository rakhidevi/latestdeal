<div>
    <div class="mb-6 border-b border-gray-200 pb-5 flex justify-between items-center">
        <div>
            <h3 class="text-2xl font-bold leading-6 text-gray-900">Automation Engine</h3>
            <p class="mt-2 text-sm text-gray-500">Configure triggers, actions, and frequency caps for automated publishing.</p>
        </div>
        <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded shadow font-bold text-sm">
            + New Workflow
        </button>
    </div>

    @if(session()->has('message'))
        <div class="mb-6 p-4 bg-blue-50 text-blue-700 rounded-md border border-blue-200 shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Workflow Name</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Trigger</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Action</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Frequency Cap</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($workflows as $wf)
                <tr>
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">{{ $wf['name'] }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $wf['trigger'] }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $wf['action'] }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $wf['frequency_cap'] }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                        <button wire:click="toggleWorkflow('{{ $wf['id'] }}')" class="{{ $wf['status'] === 'active' ? 'bg-green-500' : 'bg-gray-300' }} relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out">
                            <span aria-hidden="true" class="{{ $wf['status'] === 'active' ? 'translate-x-5' : 'translate-x-0' }} pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

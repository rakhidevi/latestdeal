@extends('admin.layout')

@section('title', 'Manage Merchants')

@section('content')
<div x-data="merchantsData()" class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Affiliate Programs</h2>
            <p class="text-sm text-gray-500">Manage your tracking parameters and store configurations.</p>
        </div>
        <button @click="openModal()" class="bg-red-600 text-white px-4 py-2 rounded-lg shadow hover:bg-red-700 transition">
            <i data-lucide="plus" class="w-4 h-4 inline-block mr-1"></i> Add Merchant
        </button>
    </div>

    <!-- Merchants Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">Merchant Name</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">Domain</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">Parameter</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($merchants as $merchant)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $merchant->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $merchant->domain }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                        <code class="bg-gray-100 text-gray-800 px-2 py-1 rounded">{{ $merchant->affiliate_param_key }}={{ $merchant->store_id }}</code>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($merchant->status)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">Enabled</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 border border-gray-200">Disabled</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button @click="openModal({{ $merchant }})" class="text-red-600 hover:text-red-900 mr-3">Edit</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No merchants configured yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="isModalOpen" x-transition class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form :action="formAction" method="POST">
                    @csrf
                    <input type="hidden" name="_method" x-model="formMethod">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title" x-text="isEdit ? 'Edit Merchant' : 'Add Merchant'"></h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Merchant Name</label>
                                <input type="text" name="name" x-model="form.name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Domain (e.g. amazon.in)</label>
                                <input type="text" name="domain" x-model="form.domain" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Param Key (e.g. tag)</label>
                                    <input type="text" name="affiliate_param_key" x-model="form.affiliate_param_key" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Store ID (e.g. mytag-21)</label>
                                    <input type="text" name="store_id" x-model="form.store_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                </div>
                            </div>
                            
                            <div class="flex items-center mt-4">
                                <input type="checkbox" name="status" id="status" x-model="form.status" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <label for="status" class="ml-2 block text-sm text-gray-900">
                                    Enable tracking for this merchant
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save
                        </button>
                        <button type="button" @click="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function merchantsData() {
        return {
            isModalOpen: false,
            isEdit: false,
            formAction: '{{ route("admin.merchants.store") }}',
            formMethod: 'POST',
            form: {
                name: '',
                domain: '',
                affiliate_param_key: '',
                store_id: '',
                status: true
            },
            openModal(merchant = null) {
                if (merchant) {
                    this.isEdit = true;
                    this.formAction = `/admin/merchants/${merchant.id}`;
                    this.formMethod = 'PUT';
                    this.form = {
                        name: merchant.name,
                        domain: merchant.domain,
                        affiliate_param_key: merchant.affiliate_param_key,
                        store_id: merchant.store_id,
                        status: merchant.status == 1
                    };
                } else {
                    this.isEdit = false;
                    this.formAction = '{{ route("admin.merchants.store") }}';
                    this.formMethod = 'POST';
                    this.form = {
                        name: '',
                        domain: '',
                        affiliate_param_key: '',
                        store_id: '',
                        status: true
                    };
                }
                this.isModalOpen = true;
            },
            closeModal() {
                this.isModalOpen = false;
            }
        }
    }
</script>
@endsection

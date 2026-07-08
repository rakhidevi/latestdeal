@extends('admin.layout')
@section('title', 'Social Accounts')
@section('content')
<div class="bg-white rounded-lg shadow-sm border p-6">
    <h3 class="text-xl font-bold mb-4">Configured Accounts</h3>
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target ID</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($accounts as $acc)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">{{ $acc->platform }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $acc->account_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $acc->target_id }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $acc->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $acc->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                    <form action="{{ route('admin.social-accounts.toggle', $acc) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="text-blue-600 hover:text-blue-900">{{ $acc->is_active ? 'Disable' : 'Enable' }}</button>
                    </form>
                    <span class="text-gray-300">|</span>
                    <form action="{{ route('admin.social-accounts.delete', $acc) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this account?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 class="text-xl font-bold mt-10 mb-4">Add New Account</h3>
    <form action="{{ route('admin.social-accounts.store') }}" method="POST" class="space-y-4 max-w-lg">
        @csrf
        <div>
            <label class="block text-sm font-medium">Platform</label>
            <select name="platform" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                <option value="telegram">Telegram</option>
                <option value="instagram">Instagram</option>
                <option value="facebook">Facebook</option>
                <option value="twitter">Twitter</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Account/Channel Name</label>
            <input type="text" name="account_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium">Access Token / Bot Token</label>
            <input type="text" name="access_token" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium">Target ID (Chat ID / IG Account ID)</label>
            <input type="text" name="target_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
        </div>
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded shadow">Add Account</button>
    </form>
</div>
@endsection

@extends('admin.layout')

@section('title', 'Manage Users & Publishers')

@section('content')
<div class="space-y-6">
    <!-- Filter Tabs -->
    <div class="flex items-center space-x-2 border-b border-slate-200 pb-4">
        <a href="{{ route('admin.users') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ empty($role) ? 'bg-red-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
            All Users
        </a>
        <a href="{{ route('admin.users', ['role' => 'admin']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ ($role ?? '') === 'admin' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
            Administrators
        </a>
        <a href="{{ route('admin.users', ['role' => 'publisher']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ ($role ?? '') === 'publisher' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
            Publishers
        </a>
        <a href="{{ route('admin.users', ['role' => 'shopper']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ ($role ?? '') === 'shopper' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
            Shoppers
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Registered</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->role === 'admin')
                            <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full bg-purple-100 text-purple-800 border border-purple-200">Admin</span>
                        @elseif($user->role === 'publisher')
                            <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-200">Publisher</span>
                        @else
                            <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Shopper</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @if($user->role !== 'admin' && $user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this user account?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold text-xs py-1 px-3 rounded-lg hover:bg-red-50 transition-colors">Remove User</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-slate-400 italic">No users found matching this filter.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

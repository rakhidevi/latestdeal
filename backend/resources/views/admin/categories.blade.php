@extends('admin.layout')

@section('title', 'Category Management')

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    editCategory: { id: null, name: '', slug: '', top_merchant_id: '' }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Categories</h1>
            <p class="text-sm text-slate-500">Manage catalog taxonomy and store mappings.</p>
        </div>
        <button @click="showAddModal = true" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-500/20 transition-all inline-flex items-center gap-2 transform active:scale-95">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Category
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-2 font-medium">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center gap-2 font-medium">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.categories') }}" class="flex gap-2">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search category by name or slug..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
            </div>
            <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-colors">
                Search
            </button>
            @if(!empty($search))
                <a href="{{ route('admin.categories') }}" class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition-colors flex items-center">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Icon & Name</th>
                        <th class="px-6 py-4">Slug</th>
                        <th class="px-6 py-4">Assigned Deals</th>
                        <th class="px-6 py-4">Top Merchant</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4 flex items-center gap-3">
                            <span class="text-xl p-2 bg-slate-100 rounded-xl">{{ $category->icon }}</span>
                            <div>
                                <div class="font-bold text-slate-800">{{ $category->name }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-500">
                            {{ $category->slug }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $category->deals_count > 0 ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $category->deals_count }} deals
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $category->topMerchant->name ?? 'None' }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button @click="editCategory = {
                                id: {{ $category->id }},
                                name: '{{ addslashes($category->name) }}',
                                slug: '{{ addslashes($category->slug) }}',
                                top_merchant_id: '{{ $category->top_merchant_id ?? '' }}'
                            }; showEditModal = true;" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                                Edit
                            </button>
                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold transition-colors">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            No categories found. Click "Add Category" to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    <!-- Add Category Modal -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div @click.away="showAddModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 animate-fade-in">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800">Add New Category</h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Smart Watches" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Slug (Optional)</label>
                    <input type="text" name="slug" placeholder="smart-watches (auto-generated if empty)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Preferred Merchant</label>
                    <select name="top_merchant_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                        <option value="">None</option>
                        @foreach($merchants as $merchant)
                            <option value="{{ $merchant->id }}">{{ $merchant->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-slate-300 rounded-xl text-sm text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-md shadow-red-500/20">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div @click.away="showEditModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 animate-fade-in">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800">Edit Category</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/categories') }}/' + editCategory.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editCategory.name" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Slug <span class="text-red-500">*</span></label>
                    <input type="text" name="slug" x-model="editCategory.slug" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Preferred Merchant</label>
                    <select name="top_merchant_id" x-model="editCategory.top_merchant_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                        <option value="">None</option>
                        @foreach($merchants as $merchant)
                            <option value="{{ $merchant->id }}">{{ $merchant->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-slate-300 rounded-xl text-sm text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-md shadow-red-500/20">
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

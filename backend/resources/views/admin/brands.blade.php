@extends('admin.layout')

@section('title', 'Brand Management')

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    editBrand: { id: null, name: '', slug: '', logo: '', is_active: true }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Brands</h1>
            <p class="text-sm text-slate-500">Manage recognized store brands, logos, and catalog filtering.</p>
        </div>
        <button @click="showAddModal = true" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-500/20 transition-all inline-flex items-center gap-2 transform active:scale-95">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Brand
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
        <form method="GET" action="{{ route('admin.brands') }}" class="flex gap-2">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search brand by name or slug..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
            </div>
            <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-colors">
                Search
            </button>
            @if(!empty($search))
                <a href="{{ route('admin.brands') }}" class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition-colors flex items-center">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Brands Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @forelse($brands as $brand)
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between group">
            <div class="text-center space-y-2">
                <div class="w-14 h-14 mx-auto rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center p-2 overflow-hidden">
                    @if($brand->logo)
                        <img src="{{ $brand->logo }}" alt="{{ $brand->name }}" class="max-h-full max-w-full object-contain" onerror="this.src='https://via.placeholder.com/60?text={{ substr($brand->name, 0, 2) }}'">
                    @else
                        <span class="font-black text-slate-400 text-lg uppercase">{{ substr($brand->name, 0, 2) }}</span>
                    @endif
                </div>

                <div>
                    <h4 class="font-bold text-slate-800 text-sm truncate" title="{{ $brand->name }}">{{ $brand->name }}</h4>
                    <p class="text-xs text-slate-400 font-mono truncate">{{ $brand->slug }}</p>
                </div>

                <div>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $brand->deals_count > 0 ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $brand->deals_count }} deals
                    </span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-1">
                <button @click="editBrand = {
                    id: {{ $brand->id }},
                    name: '{{ addslashes($brand->name) }}',
                    slug: '{{ addslashes($brand->slug) }}',
                    logo: '{{ addslashes($brand->logo ?? '') }}',
                    is_active: {{ $brand->is_active ? 'true' : 'false' }}
                }; showEditModal = true;" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium flex-1 text-center transition-colors">
                    Edit
                </button>
                <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('Delete this brand?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1 text-slate-400 hover:text-red-600 rounded-lg transition-colors">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
            No brands found. Click "Add Brand" to create one.
        </div>
        @endforelse
    </div>

    @if($brands->hasPages())
        <div class="mt-6">
            {{ $brands->links() }}
        </div>
    @endif

    <!-- Add Brand Modal -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div @click.away="showAddModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 animate-fade-in">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800">Add New Brand</h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.brands.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Brand Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Samsung, Apple, boAt" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Slug (Optional)</label>
                    <input type="text" name="slug" placeholder="samsung (auto-generated if empty)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Logo URL (Optional)</label>
                    <input type="url" name="logo" placeholder="https://logo.clearbit.com/samsung.com" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" value="1" checked id="add_active" class="w-4 h-4 rounded text-red-600 focus:ring-red-500 border-slate-300">
                    <label for="add_active" class="text-sm font-medium text-slate-700">Active in filters</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-slate-300 rounded-xl text-sm text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-md shadow-red-500/20">
                        Save Brand
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Brand Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div @click.away="showEditModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 animate-fade-in">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800">Edit Brand</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/brands') }}/' + editBrand.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Brand Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editBrand.name" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Slug <span class="text-red-500">*</span></label>
                    <input type="text" name="slug" x-model="editBrand.slug" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Logo URL</label>
                    <input type="url" name="logo" x-model="editBrand.logo" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" value="1" x-model="editBrand.is_active" id="edit_active" class="w-4 h-4 rounded text-red-600 focus:ring-red-500 border-slate-300">
                    <label for="edit_active" class="text-sm font-medium text-slate-700">Active in filters</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-slate-300 rounded-xl text-sm text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-md shadow-red-500/20">
                        Update Brand
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

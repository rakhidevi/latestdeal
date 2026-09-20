@extends('admin.layout')

@section('title', 'Add New Deal')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    originalPrice: '',
    discountedPrice: '',
    imageUrl: '',
    get discountPercent() {
        let orig = parseFloat(this.originalPrice);
        let disc = parseFloat(this.discountedPrice);
        if (orig > 0 && disc >= 0 && orig > disc) {
            return Math.round(((orig - disc) / orig) * 100);
        }
        return 0;
    },
    get savings() {
        let orig = parseFloat(this.originalPrice);
        let disc = parseFloat(this.discountedPrice);
        if (orig > 0 && disc >= 0 && orig > disc) {
            return (orig - disc).toFixed(2);
        }
        return 0;
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('admin.deals') }}" class="hover:text-slate-800 transition-colors">Deals</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Add New Deal</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Create Manual Deal</h1>
        </div>
        <a href="{{ route('admin.deals') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors inline-flex items-center gap-2 shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Deals
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
            <div class="flex items-center gap-2 font-semibold mb-1">
                <i data-lucide="alert-circle" class="w-5 h-5"></i> Please fix the following errors:
            </div>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.deals.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Core Deal Details -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-6">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="tag" class="w-4 h-4 text-red-500"></i> Primary Information
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Deal Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Apple MacBook Air M2 (8GB RAM, 256GB SSD) - Space Grey" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Merchant / Store <span class="text-red-500">*</span></label>
                        <select name="merchant_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                            <option value="">Select Merchant...</option>
                            @foreach($merchants as $merchant)
                                <option value="{{ $merchant->id }}" {{ old('merchant_id') == $merchant->id ? 'selected' : '' }}>
                                    {{ $merchant->name }} ({{ $merchant->domain }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                        <select name="category_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                            <option value="">Uncategorized</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->icon }} {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Brand</label>
                        <select name="brand_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                            <option value="">No Brand Assigned</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Target Product URL <span class="text-red-500">*</span></label>
                    <input type="url" name="url" value="{{ old('url') }}" required placeholder="https://www.amazon.in/dp/..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                    <p class="text-xs text-slate-500 mt-1">Affiliate tag will be injected automatically upon user redirection.</p>
                </div>
            </div>
        </div>

        <!-- Pricing & Discounts -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-6">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="indian-rupee" class="w-4 h-4 text-emerald-500"></i> Pricing & Savings Calculator
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Original Price (MRP)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-slate-400 font-medium">₹</span>
                        <input type="number" step="0.01" name="original_price" x-model="originalPrice" value="{{ old('original_price') }}" placeholder="999.00" class="w-full pl-8 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Discounted Price <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-slate-400 font-medium">₹</span>
                        <input type="number" step="0.01" name="discounted_price" x-model="discountedPrice" value="{{ old('discounted_price') }}" required placeholder="499.00" class="w-full pl-8 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Coupon / Promo Code</label>
                    <input type="text" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="e.g. FLAT200 or DIWALI50" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all font-mono uppercase">
                </div>
            </div>

            <!-- Calculated Live Badge Preview -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between" x-show="discountPercent > 0">
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-black text-xs shadow-sm" x-text="discountPercent + '% OFF'"></span>
                    <span class="text-sm font-medium text-slate-700">Calculated Buyer Savings: <strong class="text-emerald-600 font-bold" x-text="'₹' + savings"></strong></span>
                </div>
                <span class="text-xs text-slate-500">Auto-saved to database</span>
            </div>
        </div>

        <!-- Image & Media -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-6">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="image" class="w-4 h-4 text-blue-500"></i> Deal Image
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                <div class="md:col-span-2 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Image URL (CDN / Remote Image)</label>
                        <input type="url" name="image_path" x-model="imageUrl" value="{{ old('image_path') }}" placeholder="https://m.media-amazon.com/images/I/..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                    </div>
                    <div class="flex items-center gap-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        <span class="h-px bg-slate-200 flex-1"></span> OR <span class="h-px bg-slate-200 flex-1"></span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Upload Local Image</label>
                        <input type="file" name="image_file" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                    </div>
                </div>

                <div class="border border-dashed border-slate-300 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[160px] bg-slate-50 text-center">
                    <template x-if="imageUrl">
                        <img :src="imageUrl" alt="Preview" class="max-h-36 rounded-lg object-contain shadow-sm">
                    </template>
                    <template x-if="!imageUrl">
                        <div class="text-slate-400 flex flex-col items-center">
                            <i data-lucide="image" class="w-8 h-8 mb-1"></i>
                            <span class="text-xs">Image preview will appear here</span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Editorial & Publication Status -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-6">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-purple-500"></i> Editorial & Publication
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all font-semibold">
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>🟢 Active (Publish Live Immediately)</option>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>🟡 Pending (Keep in Draft / Review)</option>
                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>🔴 Rejected</option>
                    </select>
                </div>

                <div class="flex items-center pt-6">
                    <label class="relative flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_editor_pick" value="1" {{ old('is_editor_pick') ? 'checked' : '' }} class="w-5 h-5 rounded text-red-600 focus:ring-red-500 border-slate-300">
                        <div>
                            <span class="text-sm font-bold text-slate-800">Pin as Editor's Pick 🔥</span>
                            <p class="text-xs text-slate-500">Highlight this deal in homepage carousels and top banner.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="space-y-4 pt-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Editorial Summary</label>
                    <textarea name="editorial_summary" rows="2" placeholder="Brief 1-2 sentence hook highlighting why this is a great deal..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">{{ old('editorial_summary') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Pros (One per line)</label>
                        <textarea name="pros" rows="3" placeholder="Solid build quality&#10;Excellent battery life&#10;Lowest price in 6 months" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all font-mono text-xs">{{ old('pros') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Cons (One per line)</label>
                        <textarea name="cons" rows="3" placeholder="Only 256GB storage&#10;Limited ports" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all font-mono text-xs">{{ old('cons') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.deals') }}" class="px-6 py-2.5 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-8 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-500/20 transition-all transform active:scale-95 flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i> Create & Publish Deal
            </button>
        </div>
    </form>
</div>
@endsection

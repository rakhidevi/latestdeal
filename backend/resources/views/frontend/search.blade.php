@extends('layouts.app')

@section('meta')
    <title>{{ $rawQuery ? 'Search results for "' . $rawQuery . '" | LatestDeal' : 'Search Deals | LatestDeal' }}</title>
@endsection

@section('content')
<div class="bg-gray-50 dark:bg-slate-950 min-h-screen pb-12">
    <!-- Top Search Header Component -->
    <div class="bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800 pt-8 pb-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('search') }}" method="GET" class="relative max-w-3xl mb-4">
                <div class="relative flex items-center">
                    <svg class="absolute left-4 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="q" value="{{ $rawQuery }}" placeholder="Search products, brands or categories..." class="w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-base font-medium text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all placeholder-gray-400 shadow-sm">
                </div>
            </form>

            <div class="flex flex-wrap items-center gap-2 mb-2">
                @if($rawQuery)
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white mr-2">Search results for "{{ $rawQuery }}"</h1>
                @endif
                
                <!-- Interpreted Intent Chips -->
                <div class="flex flex-wrap gap-1.5">
                    @foreach($parsedIntent['brands'] as $brand)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-indigo-50 dark:bg-indigo-900/20 text-xs font-bold text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50">
                            {{ $brand }} <a href="{{ request()->fullUrlWithQuery(['q' => str_replace(strtolower($brand), '', strtolower($rawQuery))]) }}" class="hover:text-indigo-900">&times;</a>
                        </span>
                    @endforeach
                    @foreach($parsedIntent['categories'] as $cat)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-indigo-50 dark:bg-indigo-900/20 text-xs font-bold text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50">
                            {{ $cat }} <a href="{{ request()->fullUrlWithQuery(['q' => str_replace(strtolower($cat), '', strtolower($rawQuery))]) }}" class="hover:text-indigo-900">&times;</a>
                        </span>
                    @endforeach
                    @if($parsedIntent['maxPrice'])
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-indigo-50 dark:bg-indigo-900/20 text-xs font-bold text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50">
                            Under ₹{{ number_format($parsedIntent['maxPrice']) }} <a href="{{ request()->fullUrlWithQuery(['q' => preg_replace('/under\s*\d+/i', '', $rawQuery)]) }}" class="hover:text-indigo-900">&times;</a>
                        </span>
                    @endif
                </div>

                <!-- Explicit Constraints Chips -->
                <div class="flex flex-wrap gap-1.5 ml-2">
                    @if(request('discount'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-red-50 dark:bg-red-900/20 text-xs font-bold text-red-700 dark:text-red-400 border border-red-100 dark:border-red-800/50 shadow-sm">
                            {{ request('discount') }}%+ OFF 
                            <a href="{{ request()->fullUrlWithQuery(['discount' => null]) }}" class="hover:text-red-900 ml-1 font-black">&times;</a>
                        </span>
                    @endif
                    @if(request('max_price') && !str_contains(strtolower($rawQuery), 'under'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-red-50 dark:bg-red-900/20 text-xs font-bold text-red-700 dark:text-red-400 border border-red-100 dark:border-red-800/50 shadow-sm">
                            Under ₹{{ number_format(request('max_price')) }} 
                            <a href="{{ request()->fullUrlWithQuery(['max_price' => null]) }}" class="hover:text-red-900 ml-1 font-black">&times;</a>
                        </span>
                    @endif
                    @if(request('brand'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-red-50 dark:bg-red-900/20 text-xs font-bold text-red-700 dark:text-red-400 border border-red-100 dark:border-red-800/50 shadow-sm">
                            Brand: {{ request('brand') }} 
                            <a href="{{ request()->fullUrlWithQuery(['brand' => null]) }}" class="hover:text-red-900 ml-1 font-black">&times;</a>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        
        <!-- Mobile Filter Bar -->
        <div class="lg:hidden flex items-center justify-between mb-4 bg-white dark:bg-slate-900 p-3 rounded-xl border border-gray-200 dark:border-slate-800 shadow-sm">
            <span class="text-sm font-bold text-gray-700 dark:text-slate-300">{{ $paginatedDeals->total() }} deals found</span>
            <button onclick="document.getElementById('mobile-filter-modal').classList.remove('hidden')" class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white bg-gray-100 dark:bg-slate-800 px-4 py-2 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter & Sort
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Desktop Sidebar (Filters) -->
            <aside class="hidden lg:block lg:col-span-3 space-y-6">
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm">
                    <h3 class="text-xs font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-4">Filters</h3>
                    
                    <form action="{{ route('search') }}" method="GET" id="desktop-filter-form" class="space-y-6">
                        <input type="hidden" name="q" value="{{ $rawQuery }}">
                        
                        <!-- Discount Filter -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Discount</h4>
                            <div class="space-y-2.5">
                                @foreach([30, 50, 60, 70] as $discount)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="discount" value="{{ $discount }}" onchange="this.form.submit()" {{ request('discount') == $discount ? 'checked' : '' }} class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300 group-hover:text-red-600 transition-colors">{{ $discount }}%+ OFF</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Brand Filter (Top Brands for now) -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Brand</h4>
                            <div class="space-y-2.5">
                                @php $topBrands = ['Puma', 'Nike', 'Adidas', 'Samsung', 'LG', 'Apple', 'Sony']; @endphp
                                @foreach($topBrands as $tb)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="brand" value="{{ strtolower($tb) }}" onchange="this.form.submit()" {{ request('brand') == strtolower($tb) ? 'checked' : '' }} class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300 group-hover:text-red-600 transition-colors">{{ $tb }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Filter -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Price</h4>
                            <div class="space-y-2.5">
                                @foreach([1000 => 'Under ₹1,000', 3000 => 'Under ₹3,000', 5000 => 'Under ₹5,000', 10000 => 'Under ₹10,000'] as $val => $label)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="max_price" value="{{ $val }}" onchange="this.form.submit()" {{ request('max_price') == $val ? 'checked' : '' }} class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300 group-hover:text-red-600 transition-colors">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </form>
                </div>
            </aside>

            <!-- Results Grid -->
            <main class="lg:col-span-9">
                <div class="hidden lg:flex items-center justify-between mb-6">
                    <span class="text-sm font-bold text-gray-500 dark:text-slate-400">
                        <strong class="text-gray-900 dark:text-white">{{ $paginatedDeals->total() }} deals</strong> found
                    </span>
                    
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 font-medium">Sort:</span>
                        <select onchange="window.location.href=this.value" class="text-sm font-bold bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg py-1.5 pl-3 pr-8 focus:ring-red-500 focus:border-red-500">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'best']) }}">Best Match</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'discount']) }}">Biggest Discount</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}">Lowest Price</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}">Newest</option>
                        </select>
                    </div>
                </div>

                @if($paginatedDeals->total() > 0)
                    <div class="grid gap-4 grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
                        @foreach($paginatedDeals as $deal)
                            <x-deal-card :deal="$deal" />
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-10 flex justify-center">
                        {{ $paginatedDeals->links('pagination::tailwind') }}
                    </div>
                @else
                    <!-- Intelligent Empty State -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 p-10 text-center shadow-sm">
                        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-2">No exact matches</h2>
                        <p class="text-gray-500 dark:text-slate-400 mb-8">We couldn't find <strong class="text-gray-900 dark:text-white">{{ $rawQuery }}</strong>.</p>
                        
                        <div class="space-y-4 max-w-sm mx-auto">
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Try instead</p>
                            @if(count($parsedIntent['brands']) > 0)
                                <a href="{{ route('search', ['q' => implode(' ', $parsedIntent['brands'])]) }}" class="block w-full py-3 px-4 bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-900 dark:text-white font-bold rounded-xl transition-colors border border-gray-200 dark:border-slate-700">
                                    {{ implode(' ', $parsedIntent['brands']) }} Deals
                                </a>
                                <a href="{{ route('search', ['q' => implode(' ', $parsedIntent['brands']) . ' 50% off']) }}" class="block w-full py-3 px-4 bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-900 dark:text-white font-bold rounded-xl transition-colors border border-gray-200 dark:border-slate-700">
                                    {{ implode(' ', $parsedIntent['brands']) }} 50%+ Off
                                </a>
                            @else
                                <a href="{{ route('search', ['q' => 'electronics']) }}" class="block w-full py-3 px-4 bg-gray-50 hover:bg-gray-100 text-gray-900 font-bold rounded-xl transition-colors border border-gray-200">
                                    Top Electronics Deals
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>

<!-- Mobile Filter Bottom Sheet (Simplified) -->
<div id="mobile-filter-modal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="document.getElementById('mobile-filter-modal').classList.add('hidden')"></div>
    <div class="bg-white dark:bg-slate-900 w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl max-h-[85vh] overflow-y-auto relative z-10 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-black text-gray-900 dark:text-white">Filters & Sort</h3>
            <button onclick="document.getElementById('mobile-filter-modal').classList.add('hidden')" class="p-2 text-gray-400 hover:text-gray-900 bg-gray-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form action="{{ route('search') }}" method="GET" class="space-y-6">
            <input type="hidden" name="q" value="{{ $rawQuery }}">
            
            <!-- Mobile Sort -->
            <div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Sort By</h4>
                <select name="sort" class="w-full text-sm font-bold bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:ring-red-500 focus:border-red-500">
                    <option value="best" {{ request('sort') == 'best' ? 'selected' : '' }}>Best Match</option>
                    <option value="discount" {{ request('sort') == 'discount' ? 'selected' : '' }}>Biggest Discount</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Lowest Price</option>
                </select>
            </div>

            <!-- Discount -->
            <div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Discount</h4>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([30, 50, 60, 70] as $discount)
                    <label class="flex items-center justify-center gap-2 cursor-pointer border border-gray-200 rounded-xl p-3 {{ request('discount') == $discount ? 'bg-red-50 border-red-200 text-red-600' : 'bg-white text-gray-700' }}">
                        <input type="radio" name="discount" value="{{ $discount }}" {{ request('discount') == $discount ? 'checked' : '' }} class="hidden">
                        <span class="text-sm font-bold">{{ $discount }}%+ OFF</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex items-center gap-3">
                <a href="{{ route('search', ['q' => $rawQuery]) }}" class="flex-1 py-3.5 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">
                    Clear
                </a>
                <button type="submit" class="flex-[2] py-3.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-colors shadow-sm">
                    Show {{ $paginatedDeals->total() }} Deals
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

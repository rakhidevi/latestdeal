<div x-data="{ open: false, keyword: '', target_price: '' }" 
     x-on:open-alert-modal.window="open = true"
     x-show="open" 
     class="relative z-50" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true" 
     style="display: none;">
     
    <!-- Backdrop -->
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal Panel -->
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 @click.away="open = false"
                 class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl flex flex-col sm:flex-row border border-gray-100 dark:border-slate-800">
                 
                <!-- Left Sidebar (Branding & Info) -->
                <div class="hidden sm:flex sm:w-5/12 bg-gradient-to-br from-red-600 via-rose-600 to-orange-500 text-white p-8 lg:p-10 flex-col justify-between relative overflow-hidden">
                    <!-- Abstract geometric shapes -->
                    <div class="absolute top-0 right-0 w-48 h-48 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-yellow-400/20 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>
                    
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-6 shadow-inner border border-white/20">
                            <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                        </div>
                        <h3 class="text-3xl font-black mb-3 tracking-tight leading-tight">Never Miss<br>a Drop</h3>
                        <p class="text-red-50 text-sm opacity-90 leading-relaxed font-medium">Set your target price and our 24/7 AI engine will instantly notify you the second it falls into your budget.</p>
                    </div>

                    <div class="relative z-10 mt-12">
                        <div class="flex items-center gap-3 bg-black/10 backdrop-blur-sm p-3 rounded-xl border border-white/10">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-bold uppercase tracking-widest text-emerald-50">Engine Active</span>
                        </div>
                    </div>
                </div>

                <!-- Right Side (Form) -->
                <div class="w-full sm:w-7/12 p-6 sm:p-10 relative">
                    <!-- Mobile Icon (Visible only on small screens) -->
                    <div class="sm:hidden mb-6 flex items-center justify-center w-12 h-12 rounded-full bg-red-100 dark:bg-red-500/20 text-red-600 mx-auto">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </div>
                    
                    <div class="sm:hidden text-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modal-title">Set a Price Alert</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-slate-400">Receive an email when the price drops!</p>
                    </div>
                    
                    <div class="hidden sm:block mb-8">
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight" id="modal-title">Configure Alert</h3>
                        <p class="mt-1.5 text-sm font-medium text-gray-500 dark:text-slate-400">Track prices automatically across 50+ stores.</p>
                    </div>
                    
                    <form action="/price-alerts" method="POST" class="space-y-5">
                        @csrf
                        @if(Auth::check())
                            <input type="hidden" name="email" value="{{ Auth::user()->email }}">
                        @else
                            <div>
                                <label for="email" class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-1.5">Email Address</label>
                                <input type="email" name="email" id="email" class="block w-full rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50 px-4 py-3 text-sm text-gray-900 dark:text-white shadow-sm outline-none transition placeholder:text-gray-400 focus:border-red-400 focus:ring-2 focus:ring-red-400/20" placeholder="you@example.com" required>
                            </div>
                        @endif
                        
                        <div>
                            <label for="keyword" class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-1.5">Keyword or Product</label>
                            <input type="text" x-model="keyword" name="keyword" class="block w-full rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50 px-4 py-3 text-sm text-gray-900 dark:text-white shadow-sm outline-none transition placeholder:text-gray-400 focus:border-red-400 focus:ring-2 focus:ring-red-400/20" placeholder="e.g. iPad Pro M4" required>
                        </div>
                        
                        <div>
                            <label for="price" class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-1.5">Target Price (₹)</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="text-gray-500 font-bold">₹</span>
                                </div>
                                <input type="number" x-model="target_price" name="price" class="block w-full rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50 pl-8 pr-4 py-3 text-sm text-gray-900 dark:text-white shadow-sm outline-none transition placeholder:text-gray-400 focus:border-red-400 focus:ring-2 focus:ring-red-400/20 font-medium" placeholder="50000" required>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col sm:flex-row-reverse gap-3 pt-4 border-t border-gray-100 dark:border-slate-800">
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl bg-red-600 hover:bg-red-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-red-500/30 transition-all hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-red-500/50">
                                Start Tracking
                            </button>
                            <button type="button" @click="open = false" class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-3 text-sm font-bold text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-slate-700">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

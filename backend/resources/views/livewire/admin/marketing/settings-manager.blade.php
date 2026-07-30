<div>
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-800">Settings & Configuration</h2>
        <p class="text-sm text-slate-500 mt-1">Manage global platform configurations and integrations.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Communication -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                    <i data-lucide="send" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Communication</h3>
                    <p class="text-xs text-slate-500">Providers and Sender IDs</p>
                </div>
            </div>
            <div class="p-2">
                <a href="{{ route('admin.marketing.placeholder', ['module' => 'mail-providers']) }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">Mail Providers</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-blue-500"></i>
                </a>
                <a href="{{ route('admin.marketing.placeholder', ['module' => 'sender-identities']) }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">Sender Identities</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-blue-500"></i>
                </a>
            </div>
        </div>

        <!-- Infrastructure & Queues -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i data-lucide="server" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Infrastructure</h3>
                    <p class="text-xs text-slate-500">Queues and Rate Limits</p>
                </div>
            </div>
            <div class="p-2">
                <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">Queue Configuration</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-indigo-500"></i>
                </button>
                <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">Rate Limiting</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-indigo-500"></i>
                </button>
            </div>
        </div>

        <!-- System & Security -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center text-red-600">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Security</h3>
                    <p class="text-xs text-slate-500">Access and Compliance</p>
                </div>
            </div>
            <div class="p-2">
                <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">API Keys</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-red-500"></i>
                </button>
                <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">User Permissions</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-red-500"></i>
                </button>
            </div>
        </div>

        <!-- Tracking & Analytics -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Tracking</h3>
                    <p class="text-xs text-slate-500">Engagement Metrics</p>
                </div>
            </div>
            <div class="p-2">
                <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">Click Tracking</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-green-500"></i>
                </button>
                <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">Open Tracking (Pixels)</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-green-500"></i>
                </button>
            </div>
        </div>

        <!-- AI Configuration -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                    <i data-lucide="bot" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">AI Engine</h3>
                    <p class="text-xs text-slate-500">Models and Prompts</p>
                </div>
            </div>
            <div class="p-2">
                <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">Generative Providers</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-purple-500"></i>
                </button>
                <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">Subject Line Optimization</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-purple-500"></i>
                </button>
            </div>
        </div>

        <!-- Feature Flags -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600">
                    <i data-lucide="toggle-right" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Feature Flags</h3>
                    <p class="text-xs text-slate-500">Beta Features</p>
                </div>
            </div>
            <div class="p-2">
                <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">WhatsApp Channel <span class="ml-2 px-1.5 py-0.5 bg-blue-100 text-blue-700 text-[10px] rounded uppercase font-bold">Beta</span></span>
                    <div class="w-8 h-4 bg-slate-200 rounded-full relative">
                        <div class="w-4 h-4 bg-white border border-slate-300 rounded-full absolute left-0 shadow-sm"></div>
                    </div>
                </button>
                <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="text-sm font-medium text-slate-700">Push Notifications</span>
                    <div class="w-8 h-4 bg-green-500 rounded-full relative">
                        <div class="w-4 h-4 bg-white border border-slate-300 rounded-full absolute right-0 shadow-sm"></div>
                    </div>
                </button>
            </div>
        </div>

    </div>
</div>

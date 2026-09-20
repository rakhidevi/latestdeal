<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name='impact-site-verification' value='dcd870d6-a11b-48ec-8df2-15ba5c96630b'>
    <title>Admin Dashboard - LatestDeal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Heroicons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { DEFAULT: '#ef4444', 500: '#ef4444', 600: '#dc2626', 900: '#0f172a' },
                        surface: '#ffffff',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0', transform: 'translateY(10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .sidebar-gradient {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    @livewireStyles
</head>
<body class="h-full overflow-hidden text-slate-800">
    <div class="h-full flex">
        
        <!-- Sidebar -->
        <div class="w-72 sidebar-gradient text-white flex-shrink-0 flex flex-col shadow-2xl relative z-20">
            <div class="h-20 flex items-center px-8 bg-black/10 border-b border-white/5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-500 to-orange-400 flex items-center justify-center shadow-lg mr-4">
                    <i data-lucide="zap" class="text-white w-6 h-6"></i>
                </div>
                <span class="font-bold text-xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-slate-300">LatestDeal</span>
            </div>
            
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <!-- 1. Overview -->
                <p class="px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-2">Overview</p>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.dashboard') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Dashboard</span>
                </a>
                <a href="{{ route('admin.uic.user-intelligence') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.uic.*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="brain-circuit" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Traffic Intelligence</span>
                </a>

                <!-- 2. Deals & Catalog -->
                <p class="px-4 text-[11px] font-bold text-red-400 uppercase tracking-wider mb-2 mt-6 flex items-center gap-1.5">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                    Deals & Catalog
                </p>
                <a href="{{ route('admin.deals') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.deals') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="tag" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Deals Catalog</span>
                </a>
                <a href="{{ route('admin.deals.create') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.deals.create') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="plus-circle" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110 text-emerald-400"></i>
                    <span class="text-sm font-medium">Add New Deal</span>
                </a>
                <a href="{{ route('admin.deals.review-queue') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.deals.review-queue') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="check-square" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110 text-orange-400"></i>
                    <span class="text-sm font-medium">Review Queue</span>
                </a>
                <a href="{{ route('admin.categories') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.categories*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="folder-tree" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Categories</span>
                </a>
                <a href="{{ route('admin.brands') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.brands*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="award" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Brands</span>
                </a>

                <!-- 3. Marketing & Newsletters -->
                <p class="px-4 text-[11px] font-bold text-red-400 uppercase tracking-wider mb-2 mt-6 flex items-center gap-1.5">
                    <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                    Marketing & Newsletters
                </p>
                <a href="{{ route('admin.marketing.campaigns') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.marketing.campaigns*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="send" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Dispatch Newsletter</span>
                </a>
                <a href="{{ route('admin.marketing.templates') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.marketing.templates*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="layout-template" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Email Templates</span>
                </a>
                <a href="{{ route('admin.marketing.subscribers') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.marketing.subscribers*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="users" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Subscribers</span>
                </a>

                <!-- 4. Crawlers & Scrapers -->
                <p class="px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">Crawlers & Operations</p>
                <a href="{{ route('admin.actions') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.actions') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="cpu" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Crawler Operations</span>
                </a>

                <!-- 5. Directory -->
                <p class="px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">Directory</p>
                <a href="{{ route('admin.merchants') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.merchants*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="store" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Merchants / Stores</span>
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.users*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="user-cog" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Users & Roles</span>
                </a>
                <a href="{{ route('admin.social-accounts') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.social-accounts*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="share-2" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Social Channels</span>
                </a>
                <a href="{{ route('admin.links') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.links*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="link" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Link Generator</span>
                </a>

                <!-- 6. System -->
                <p class="px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">System</p>
                <a href="{{ route('admin.settings') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.settings*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="sliders" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Settings</span>
                </a>
                <form action="{{ route('admin.clear-cache') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 text-slate-400 hover:bg-white/5 hover:text-amber-300 text-left">
                        <i data-lucide="trash" class="w-4 h-4 mr-3"></i>
                        <span class="text-xs font-medium">Purge App Cache</span>
                    </button>
                </form>
            </nav>
            
            <div class="p-4 bg-black/20 border-t border-white/5">
                <form method="POST" action="{{ url('/publisher/logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-red-500/20 hover:text-red-400 transition-colors border border-transparent hover:border-red-500/20">
                        <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                        <span class="font-medium text-sm">Sign Out Session</span>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col relative overflow-hidden bg-slate-50">
            <!-- Decorative background elements -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-red-400/10 blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-orange-400/10 blur-3xl pointer-events-none"></div>

            <!-- Top Header -->
            <header class="h-20 glass-panel sticky top-0 z-10 flex items-center justify-between px-10 shadow-[0_4px_20px_-15px_rgba(0,0,0,0.1)]">
                <div>
                    <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-500">@yield('title')</h1>
                    <p class="text-xs text-slate-500 mt-1 font-medium tracking-wide">Platform Administration</p>
                </div>
                
                <div class="flex items-center space-x-5">
                    <button class="p-2.5 bg-white rounded-full shadow-sm border border-slate-100 text-slate-400 hover:text-red-600 transition-colors relative">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
                    <div class="h-8 w-px bg-slate-200"></div>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-red-100 to-rose-100 border-2 border-white shadow-sm flex items-center justify-center text-red-700 font-bold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="ml-3 hidden md:block">
                            <p class="text-sm font-semibold text-slate-700">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500">Super Admin</p>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-10 relative z-0">
                <div class="max-w-7xl mx-auto animate-fade-in">
                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
    @livewireScripts
</body>
</html>

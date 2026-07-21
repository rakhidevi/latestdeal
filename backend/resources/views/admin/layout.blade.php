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
                        primary: { 500: '#ef4444', 600: '#dc2626', 900: '#0f172a' },
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
            
            <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto">
                <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 mt-2">Overview</p>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.dashboard') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                
                <p class="px-4 text-xs font-semibold text-red-400 uppercase tracking-wider mb-3 mt-6 flex items-center gap-1.5">
                    <i data-lucide="brain-circuit" class="w-3.5 h-3.5"></i>
                    User Intelligence (UIC)
                </p>
                
                <a href="{{ route('admin.uic.user-intelligence') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.uic.user-intelligence*') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="users" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">User Intelligence</span>
                </a>

                <a href="{{ route('admin.uic.traffic-sources') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.uic.traffic-sources') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="compass" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Traffic Sources</span>
                </a>

                <a href="{{ route('admin.uic.ai-conversations') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.uic.ai-conversations') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="bot" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">AI Conversations</span>
                </a>

                <a href="{{ route('admin.uic.affiliate-analytics') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.uic.affiliate-analytics') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="trending-up" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Affiliate Analytics</span>
                </a>

                <a href="{{ route('admin.uic.search-analytics') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.uic.search-analytics') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="search" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Search Analytics</span>
                </a>

                <a href="{{ route('admin.uic.conversion-funnel') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.uic.conversion-funnel') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="filter" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Conversion Funnel</span>
                </a>

                <a href="{{ route('admin.uic.geographic-insights') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.uic.geographic-insights') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="globe" class="w-4 h-4 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Geographic Insights</span>
                </a>
                
                <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 mt-8">Monitoring</p>
                <a href="{{ route('admin.actions') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.actions') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="activity" class="w-5 h-5 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="font-medium">Scraping Actions</span>
                </a>
                
                <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 mt-8">Management</p>
                <a href="{{ route('admin.deals') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.deals') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="shopping-bag" class="w-5 h-5 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="font-medium">Deals Catalog</span>
                </a>
                
                <a href="{{ route('admin.merchants') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.merchants') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="store" class="w-5 h-5 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="font-medium">Merchants</span>
                </a>
                
                <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.users') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="users" class="w-5 h-5 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="font-medium">Publishers</span>
                </a>
                
                <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 mt-8">Tools</p>
                <a href="{{ route('admin.links') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.links') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="link" class="w-5 h-5 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="font-medium">Link Generator</span>
                </a>

                <a href="{{ route('admin.social-accounts') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.social-accounts') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="share-2" class="w-5 h-5 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="font-medium">Social Accounts</span>
                </a>
                
                <a href="{{ route('admin.settings') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.settings') ? 'bg-red-600/20 text-red-400 shadow-inner border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="cpu" class="w-5 h-5 mr-3 transition-transform group-hover:scale-110"></i>
                    <span class="font-medium">AI Settings</span>
                </a>
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
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>

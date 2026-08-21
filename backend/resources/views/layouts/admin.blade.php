<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('header', 'Admin') - LatestDeal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 500: '#ef4444', 600: '#dc2626', 900: '#0f172a' },
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
        .sidebar-gradient { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
        [x-cloak] { display: none !important; }
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
                <span class="font-bold text-xl tracking-tight">LatestDeal</span>
            </div>

            <nav class="flex-1 px-4 py-8 space-y-1 overflow-y-auto">
                <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Overview</p>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.dashboard') ? 'bg-red-600/20 text-red-400 border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <p class="px-4 text-xs font-semibold text-red-400 uppercase tracking-wider mb-3 mt-6 flex items-center gap-1.5">
                    <i data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                    Marketing Engine
                </p>
                <a href="{{ route('admin.marketing.dashboard') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.marketing.dashboard') ? 'bg-red-600/20 text-red-400 border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 mr-3"></i>
                    <span class="text-sm font-medium">Marketing Overview</span>
                </a>
                <a href="{{ route('admin.marketing.campaigns') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.marketing.campaigns') ? 'bg-red-600/20 text-red-400 border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="mail" class="w-4 h-4 mr-3"></i>
                    <span class="text-sm font-medium">Campaigns</span>
                </a>
                <a href="{{ route('admin.marketing.settings') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.marketing.settings') ? 'bg-red-600/20 text-red-400 border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="settings" class="w-4 h-4 mr-3"></i>
                    <span class="text-sm font-medium">Email Settings</span>
                </a>

                <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 mt-8">Management</p>
                <a href="/pulse" target="_blank" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group text-slate-300 hover:bg-white/5 hover:text-white">
                    <i data-lucide="activity" class="w-5 h-5 mr-3"></i>
                    <span class="font-medium">Pulse Telemetry</span>
                </a>
                <a href="{{ route('admin.discovery-profiles') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.discovery-profiles') ? 'bg-red-600/20 text-red-400 border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="search" class="w-5 h-5 mr-3"></i>
                    <span class="font-medium">Discovery Profiles</span>
                </a>
                <a href="{{ route('admin.deals') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.deals') ? 'bg-red-600/20 text-red-400 border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="shopping-bag" class="w-5 h-5 mr-3"></i>
                    <span class="font-medium">Deals Catalog</span>
                </a>
                <a href="{{ route('admin.merchants') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.merchants') ? 'bg-red-600/20 text-red-400 border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="store" class="w-5 h-5 mr-3"></i>
                    <span class="font-medium">Merchants</span>
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.users') ? 'bg-red-600/20 text-red-400 border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                    <span class="font-medium">Publishers</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.settings') ? 'bg-red-600/20 text-red-400 border border-red-500/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="cpu" class="w-5 h-5 mr-3"></i>
                    <span class="font-medium">AI Settings</span>
                </a>
            </nav>

            <div class="p-4 bg-black/20 border-t border-white/5">
                <form method="POST" action="{{ url('/publisher/logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-red-500/20 hover:text-red-400 transition-colors border border-transparent hover:border-red-500/20">
                        <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                        <span class="font-medium text-sm">Sign Out</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden bg-slate-50">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-red-400/10 blur-3xl pointer-events-none"></div>

            <!-- Header -->
            <header class="h-20 glass-panel sticky top-0 z-10 flex items-center justify-between px-10 shadow-sm flex-shrink-0">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">@yield('header', 'Admin')</h1>
                    <p class="text-xs text-slate-500 mt-1">Marketing Engine · LatestDeal Platform</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-red-600 flex items-center gap-1.5 transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Admin Dashboard
                    </a>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-red-100 to-rose-100 border-2 border-white shadow-sm flex items-center justify-center text-red-700 font-bold">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-10">
                <div class="max-w-7xl mx-auto animate-fade-in">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
    <script>lucide.createIcons();</script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LatestDeal Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="h-16 flex items-center px-6 border-b border-gray-800 font-bold text-xl tracking-tight">
                LatestDeal Admin
            </div>
            <nav class="flex-1 overflow-y-auto py-4">
                <a href="/admin/marketing" class="block px-6 py-3 text-sm font-medium hover:bg-gray-800 text-gray-300 hover:text-white transition">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    Marketing Center
                </a>
            </nav>
            <div class="p-4 border-t border-gray-800">
                <a href="/" class="text-sm text-gray-400 hover:text-white">← Back to Site</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center px-8 shadow-sm">
                <h1 class="text-lg font-semibold text-gray-800">
                    @yield('header', 'Admin Dashboard')
                </h1>
            </header>
            
            <div class="p-8">
                @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>

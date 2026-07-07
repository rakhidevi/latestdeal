<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LatestDeal - Discover the Best Verified Deals Worldwide</title>
    
    @yield('meta')

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            :root { color-scheme: light; }
            html.dark { color-scheme: dark; }

            body {
                @apply min-h-screen bg-slate-50 text-gray-900 transition-colors duration-300;
                background-image:
                    radial-gradient(circle at 0% 0%, rgba(239, 68, 68, 0.18), transparent 35%),
                    radial-gradient(circle at 100% 0%, rgba(244, 63, 94, 0.14), transparent 30%),
                    linear-gradient(to bottom, #fffaf5, #f8fafc 35%);
            }
            html.dark body {
                @apply bg-slate-950 text-slate-100;
                background-image:
                    radial-gradient(circle at 0% 0%, rgba(239, 68, 68, 0.12), transparent 28%),
                    radial-gradient(circle at 100% 0%, rgba(251, 113, 133, 0.08), transparent 24%),
                    linear-gradient(to bottom, #0f172a, #020617 50%);
            }
            .section-title { @apply text-2xl font-black tracking-tight text-gray-900 dark:text-slate-100; }
            .section-subtitle { @apply mt-1 text-sm text-gray-600 dark:text-slate-400; }
            .panel { @apply rounded-2xl border border-red-100/80 bg-white/90 p-4 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/80; }
            .surface { @apply rounded-2xl border border-red-100 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900; }
            .btn-primary { @apply rounded-xl bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600; }
            .btn-secondary { @apply rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800; }
            .input-base { @apply w-full rounded-xl border border-red-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-red-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100; }
        }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('themeSwitcher', () => ({
                isDark: false,
                init() {
                    const stored = localStorage.getItem("adh-theme");
                    this.isDark = stored === "dark";
                    if(this.isDark) document.documentElement.classList.add("dark");
                },
                toggleDark() {
                    this.isDark = !this.isDark;
                    document.documentElement.classList.toggle("dark", this.isDark);
                    localStorage.setItem("adh-theme", this.isDark ? "dark" : "light");
                }
            }))
        })
    </script>
</head>
<body x-data="themeSwitcher" class="antialiased">
    
    <header class="sticky top-0 z-40 border-b border-red-100/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/85">
      <div class="mx-auto grid max-w-7xl gap-3 px-4 py-3 md:px-6 lg:grid-cols-[220px_1fr_auto] lg:items-center">
        <a href="/" class="flex items-center">
          <img src="/images/logo.png" alt="LatestDeal" class="h-10 w-auto" />
        </a>

        <div class="flex flex-col gap-2 md:flex-row">
          <form action="/" method="GET" class="flex-1">
            <input name="q" value="{{ request('q') }}" placeholder="Search deals, products, brands" class="input-base w-full" />
          </form>
        </div>

        <div class="flex items-center justify-end gap-2">
          <button @click="toggleDark()" class="btn-secondary px-3" type="button" x-text="isDark ? 'Light' : 'Dark'"></button>
          <a href="/?category=all" class="btn-primary">Browse Deals</a>
        </div>
      </div>
      <div class="mx-auto hidden max-w-7xl items-center gap-6 px-4 pb-3 text-sm font-medium text-gray-600 md:flex md:px-6 dark:text-slate-300">
        <a href="/" class="transition hover:text-red-600">Home</a>
        <a href="/?tag=today" class="transition hover:text-red-600">Today Deals</a>
        <a href="/?sort=discount" class="transition hover:text-red-600">Top Discounts</a>
        <a href="/?brand=amazon" class="transition hover:text-red-600">Amazon Deals</a>
        <a href="/?category=electronics" class="transition hover:text-red-600">Electronics</a>
        <a href="/?category=fashion" class="transition hover:text-red-600">Fashion</a>
      </div>
    </header>

    <main class="mx-auto min-h-[calc(100vh-130px)] max-w-7xl px-4 py-6 md:px-6">
        @yield('content')
    </main>

    <footer class="border-t border-red-100 bg-white py-6 text-center text-sm text-gray-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
        LatestDeal • Autonomous global deal discovery
    </footer>

    @if(View::exists('components.alert-modal'))
        <x-alert-modal />
    @endif
    @if(View::exists('components.cookie-consent'))
        <x-cookie-consent />
    @endif
</body>
</html>

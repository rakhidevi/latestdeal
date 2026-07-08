<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LatestDeal - Coming Soon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 50%, #faf5ff 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05), 0 0 20px rgba(255,255,255,0.4) inset;
        }

        .gradient-text {
            background: linear-gradient(135deg, #10b981 0%, #0ea5e9 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .animated-blob {
            position: absolute;
            filter: blur(60px);
            z-index: -1;
            opacity: 0.6;
            animation: float 10s infinite ease-in-out alternate;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -30px) scale(1.1); }
        }
    </style>
</head>
<body class="relative overflow-hidden">
    
    <!-- Background blobs -->
    <div class="animated-blob bg-emerald-200 w-96 h-96 rounded-full top-10 left-10" style="animation-delay: 0s;"></div>
    <div class="animated-blob bg-sky-200 w-96 h-96 rounded-full bottom-10 right-10" style="animation-delay: -5s;"></div>

    <div class="relative z-10 w-full max-w-2xl px-6">
        <div class="glass-panel rounded-3xl p-10 md:p-16 text-center transform transition-all hover:scale-[1.01] duration-500">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-lg mb-8">
                <i data-lucide="sparkles" class="w-10 h-10 text-emerald-500"></i>
            </div>
            
            <h1 class="text-5xl md:text-6xl font-black text-slate-800 mb-4 tracking-tight">
                Something <span class="gradient-text">Amazing</span> is Coming.
            </h1>
            
            <p class="text-lg md:text-xl text-slate-600 mb-10 leading-relaxed font-light">
                We are currently upgrading LatestDeal.in to bring you an unparalleled, AI-driven shopping experience. Get ready for the best deals on the internet.
            </p>

            <form action="{{ route('subscribe') }}" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                @csrf
                <input type="email" name="email" placeholder="Enter your email to get notified" required class="flex-1 px-5 py-4 rounded-xl border border-white/50 bg-white/50 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all font-medium text-slate-700 shadow-inner">
                <button type="submit" class="px-8 py-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">Notify Me</button>
            </form>
            
            @if(session('success'))
                <p class="mt-4 text-emerald-600 font-bold bg-emerald-50 inline-block px-4 py-2 rounded-full border border-emerald-100">{{ session('success') }}</p>
            @endif

            <div class="mt-12 pt-8 border-t border-slate-200/50 flex items-center justify-center gap-6">
                <a href="#" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/50 hover:bg-white text-slate-400 hover:text-emerald-500 transition-all"><i data-lucide="twitter" class="w-5 h-5"></i></a>
                <a href="#" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/50 hover:bg-white text-slate-400 hover:text-emerald-500 transition-all"><i data-lucide="instagram" class="w-5 h-5"></i></a>
                <a href="#" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/50 hover:bg-white text-slate-400 hover:text-emerald-500 transition-all"><i data-lucide="facebook" class="w-5 h-5"></i></a>
            </div>
        </div>
        
        <p class="text-center text-slate-400 mt-8 text-sm font-medium">
            &copy; {{ date('Y') }} LatestDeal.in. All rights reserved.
        </p>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>

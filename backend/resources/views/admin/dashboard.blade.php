@extends('admin.layout')

@section('title', 'System Insights')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
    
    <!-- Queue Backlog Card -->
    <div class="glass-panel rounded-3xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300 shadow-lg">
        <div class="absolute top-0 right-0 -mr-6 -mt-6 w-24 h-24 rounded-full bg-blue-500/10 blur-xl group-hover:bg-blue-500/20 transition-colors"></div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-500 tracking-wide uppercase">Queue Backlog</h3>
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                <i data-lucide="layers" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-baseline">
            <p class="text-4xl font-black text-slate-800 tracking-tight">{{ $queueCount }}</p>
            <span class="ml-2 text-sm font-medium text-slate-500">jobs pending</span>
        </div>
    </div>
    
    <!-- Failed Jobs Card -->
    <div class="glass-panel rounded-3xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300 shadow-lg">
        <div class="absolute top-0 right-0 -mr-6 -mt-6 w-24 h-24 rounded-full bg-red-500/10 blur-xl group-hover:bg-red-500/20 transition-colors"></div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-500 tracking-wide uppercase">Failed Jobs</h3>
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-baseline">
            <p class="text-4xl font-black text-red-600 tracking-tight">{{ $failedJobs }}</p>
            <span class="ml-2 text-sm font-medium text-slate-500">requires attention</span>
        </div>
    </div>

    <!-- Total Clicks Card -->
    <div class="glass-panel rounded-3xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300 shadow-lg">
        <div class="absolute top-0 right-0 -mr-6 -mt-6 w-24 h-24 rounded-full bg-emerald-500/10 blur-xl group-hover:bg-emerald-500/20 transition-colors"></div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-500 tracking-wide uppercase">Platform Clicks</h3>
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                <i data-lucide="mouse-pointer-click" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-baseline">
            <p class="text-4xl font-black text-slate-800 tracking-tight">{{ number_format($metrics['total_clicks'] ?? 0) }}</p>
            <span class="ml-2 text-sm font-medium text-slate-500">all time</span>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="glass-panel rounded-3xl p-8 shadow-lg">
    <div class="flex items-center justify-between mb-6 border-b border-slate-200 pb-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">System Actions</h3>
            <p class="text-sm text-slate-500 mt-1">Manage background workers and system health</p>
        </div>
        <i data-lucide="cpu" class="w-8 h-8 text-slate-300"></i>
    </div>
    
    <div class="flex flex-wrap gap-4">
        <form action="{{ route('admin.queue.work') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl shadow-md hover:shadow-xl hover:from-blue-700 hover:to-indigo-700 transition-all transform hover:-translate-y-0.5">
                <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                Run Queue Worker
            </button>
        </form>
        <form action="{{ route('admin.queue.clear') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center px-6 py-3 bg-white border border-slate-200 text-slate-700 font-medium rounded-xl shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all transform hover:-translate-y-0.5">
                <i data-lucide="trash-2" class="w-4 h-4 mr-2 text-red-500"></i>
                Clear Failed Jobs
            </button>
        </form>
    </div>
</div>
@endsection

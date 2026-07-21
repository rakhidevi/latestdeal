@extends('admin.layout')

@section('title', 'User Intelligence - UIC')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">User Intelligence</h1>
        <p class="text-sm text-slate-500 mt-1">Visitor activity, returning users, and active sessions</p>
    </div>
    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
        {{ $liveVisitors }} Online
    </span>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="glass-panel p-6 rounded-2xl">
        <h3 class="text-xs font-bold text-slate-400 uppercase">Total Visitors</h3>
        <p class="text-3xl font-black text-slate-800 mt-2">{{ number_format($totalVisitors) }}</p>
    </div>
    <div class="glass-panel p-6 rounded-2xl">
        <h3 class="text-xs font-bold text-slate-400 uppercase">Returning Users</h3>
        <p class="text-3xl font-black text-indigo-600 mt-2">{{ number_format($returningUsers) }}</p>
    </div>
    <div class="glass-panel p-6 rounded-2xl">
        <h3 class="text-xs font-bold text-slate-400 uppercase">New Visitors Today</h3>
        <p class="text-3xl font-black text-emerald-600 mt-2">{{ number_format($uniqueVisitorsToday) }}</p>
    </div>
    <div class="glass-panel p-6 rounded-2xl">
        <h3 class="text-xs font-bold text-slate-400 uppercase">Live (15m)</h3>
        <p class="text-3xl font-black text-red-600 mt-2">{{ number_format($liveVisitors) }}</p>
    </div>
</div>

<div class="glass-panel rounded-3xl p-8 shadow-lg">
    <h3 class="text-xl font-bold text-slate-800 mb-6">Recent Visitor Profiles</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs">
                <tr>
                    <th class="px-4 py-3">Visitor UUID</th>
                    <th class="px-4 py-3">First Visit</th>
                    <th class="px-4 py-3">Last Visit</th>
                    <th class="px-4 py-3 text-center">Return Visits</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($visitors as $v)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-4 font-mono font-bold text-slate-700">{{ substr($v->visitor_uuid, 0, 16) }}...</td>
                        <td class="px-4 py-4 text-xs text-slate-500">{{ $v->first_visit ? \Carbon\Carbon::parse($v->first_visit)->diffForHumans() : 'N/A' }}</td>
                        <td class="px-4 py-4 text-xs text-slate-500">{{ $v->last_visit ? \Carbon\Carbon::parse($v->last_visit)->diffForHumans() : 'N/A' }}</td>
                        <td class="px-4 py-4 text-center font-bold text-indigo-600">{{ $v->return_visit_count ?? 0 }}</td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('admin.uic.user-detail', ['uuid' => $v->visitor_uuid]) }}" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">View Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400 italic">No visitors recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

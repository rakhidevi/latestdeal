@props(['status'])

@php
    $status = strtolower($status);
    
    // Default styling
    $colorClass = 'bg-slate-100 text-slate-600 border-slate-200';
    $dotClass = 'bg-slate-400';
    $label = ucfirst($status);

    switch ($status) {
        case 'healthy':
        case 'completed':
        case 'sent':
        case 'active':
            $colorClass = 'bg-green-50 text-green-700 border-green-200';
            $dotClass = 'bg-green-500';
            break;
            
        case 'warning':
        case 'paused':
            $colorClass = 'bg-yellow-50 text-yellow-700 border-yellow-200';
            $dotClass = 'bg-yellow-500';
            break;
            
        case 'error':
        case 'failed':
            $colorClass = 'bg-red-50 text-red-700 border-red-200';
            $dotClass = 'bg-red-500';
            break;
            
        case 'processing':
        case 'sending':
        case 'running':
            $colorClass = 'bg-blue-50 text-blue-700 border-blue-200';
            $dotClass = 'bg-blue-500 animate-pulse';
            break;
            
        case 'scheduled':
            $colorClass = 'bg-purple-50 text-purple-700 border-purple-200';
            $dotClass = 'bg-purple-500';
            break;
            
        case 'disabled':
        case 'archived':
        case 'draft':
            $colorClass = 'bg-slate-50 text-slate-600 border-slate-200';
            $dotClass = 'bg-slate-400';
            break;
    }
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $colorClass }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
    {{ $label }}
</span>

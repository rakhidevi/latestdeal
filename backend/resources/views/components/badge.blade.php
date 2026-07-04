@props(['color' => 'gray'])

@php
    $colors = [
        'red' => 'bg-red-100 text-red-800',
        'green' => 'bg-green-100 text-green-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'gray' => 'bg-gray-100 text-gray-800',
        'accent' => 'bg-accent text-white' // Solid urgent red
    ][$color];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors }}">
    {{ $slot }}
</span>

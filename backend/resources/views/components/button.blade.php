@props(['variant' => 'primary'])

@php
    $classes = [
        'primary' => 'bg-primary hover:bg-blue-700 text-white',
        'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-800',
        'danger' => 'bg-accent hover:bg-red-700 text-white',
    ][$variant];
@endphp

<button {{ $attributes->merge(['class' => "inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md font-semibold text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary $classes"]) }}>
    {{ $slot }}
</button>

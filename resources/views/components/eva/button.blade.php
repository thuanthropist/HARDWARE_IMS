@props(['variant' => 'primary', 'href' => null, 'type' => 'button'])

@php
    $classes = [
        'primary' => 'bg-amber-500 text-white hover:bg-amber-600 focus-visible:outline-amber-500',
        'secondary' => 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:outline-red-600',
        'accent' => 'bg-amber-500 text-white hover:bg-amber-600 focus-visible:outline-amber-500',
    ][$variant];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(["inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2", $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class(["inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2", $classes]) }}>
        {{ $slot }}
    </button>
@endif

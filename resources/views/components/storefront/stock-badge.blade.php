@props(['status'])
@php
    $meta = match ($status) {
        'in_stock' => ['label' => 'In Stock', 'class' => 'sf-badge-in-stock'],
        'low_stock' => ['label' => 'Low Stock', 'class' => 'sf-badge-low-stock'],
        default => ['label' => 'Out of Stock', 'class' => 'sf-badge-out-stock'],
    };
@endphp
<span {{ $attributes->merge(['class' => 'sf-badge-stock '.$meta['class']]) }}>{{ $meta['label'] }}</span>

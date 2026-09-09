@props(['message' => null, 'phone' => null])

@php
    $number = $phone ?? config('services.whatsapp.number');
    $message ??= setting('whatsapp.default_message', "Hi! I'd like some help with a project.");
@endphp

<a href="https://wa.me/{{ $number }}?text={{ urlencode($message) }}" target="_blank" rel="noopener" {{ $attributes }}>
    {{ $slot }}
</a>

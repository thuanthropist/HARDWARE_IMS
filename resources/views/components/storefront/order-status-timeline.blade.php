@props(['order'])
@php
    $steps = $order->delivery_method === 'pickup'
        ? [
            'pending_confirmation' => 'Pending Confirmation',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'ready_for_pickup' => 'Ready for Pickup',
            'completed' => 'Completed',
        ]
        : [
            'pending_confirmation' => 'Pending Confirmation',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'out_for_delivery' => 'Out for Delivery',
            'completed' => 'Completed',
        ];
    $keys = array_keys($steps);
    $currentIndex = array_search($order->status, $keys, true);
@endphp

@if ($order->status === 'cancelled')
    <div class="alert alert-danger mb-0">This order was cancelled.</div>
@elseif ($order->status === 'rejected')
    <div class="alert alert-danger mb-0">
        This order was rejected.
        @if ($order->rejection_reason)
            <div class="small mt-1">{{ $order->rejection_reason }}</div>
        @endif
    </div>
@elseif ($order->status === 'partially_fulfilled')
    <div class="alert alert-warning mb-0">
        This order was partially fulfilled — some items are on backorder or unavailable. See the item list below for details.
    </div>
@else
    <div class="d-flex flex-wrap">
        @foreach ($steps as $key => $label)
            @php
                $stepIndex = array_search($key, $keys, true);
                $state = $currentIndex === false ? 'upcoming' : ($stepIndex < $currentIndex ? 'done' : ($stepIndex === $currentIndex ? 'current' : 'upcoming'));
            @endphp
            <div class="text-center flex-fill px-1">
                <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-1 fw-bold"
                     style="width: 2.25rem; height: 2.25rem; background: {{ $state === 'upcoming' ? '#e5e7eb' : 'var(--sf-primary)' }}; color: {{ $state === 'upcoming' ? '#6b7280' : '#fff' }};">
                    {{ $state === 'done' ? '✓' : $stepIndex + 1 }}
                </div>
                <div class="small {{ $state === 'current' ? 'fw-bold' : 'text-body-secondary' }}">{{ $label }}</div>
            </div>
        @endforeach
    </div>
@endif

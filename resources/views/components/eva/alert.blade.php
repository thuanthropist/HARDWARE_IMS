@props(['type' => 'success', 'message' => null])

@if ($message)
    <div @class([
        'mb-4 flex items-center gap-2 rounded-lg border px-4 py-3 text-sm',
        'border-emerald-200 bg-emerald-50 text-emerald-700' => $type === 'success',
        'border-red-200 bg-red-50 text-red-700' => $type === 'error',
    ])>
        {{ $message }}
    </div>
@endif

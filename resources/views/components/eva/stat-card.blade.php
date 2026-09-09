@props(['label', 'value', 'hint' => null, 'accent' => 'emerald'])

<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
    <p @class([
        'mt-2 text-2xl font-semibold',
        'text-emerald-600' => $accent === 'emerald',
        'text-amber-600' => $accent === 'amber',
        'text-slate-900' => $accent === 'slate',
    ])>{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
</div>

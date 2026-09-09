@props(['color' => 'slate'])

<span @class([
    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
    'bg-emerald-100 text-emerald-700' => $color === 'emerald',
    'bg-red-100 text-red-700' => $color === 'red',
    'bg-amber-100 text-amber-700' => $color === 'amber',
    'bg-sky-100 text-sky-700' => $color === 'sky',
    'bg-slate-100 text-slate-700' => $color === 'slate',
])>
    {{ $slot }}
</span>

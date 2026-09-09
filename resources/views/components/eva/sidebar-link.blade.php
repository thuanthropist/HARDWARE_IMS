@props(['href', 'active' => false])

<a href="{{ $href }}"
   @class([
       'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
       'bg-emerald-50 text-emerald-700' => $active,
       'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $active,
   ])>
    {{ $icon ?? '' }}
    <span class="flex-1">{{ $slot }}</span>
</a>

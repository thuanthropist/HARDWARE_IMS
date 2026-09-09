@props(['title' => null])

<div {{ $attributes->class(['rounded-xl border border-slate-200 bg-white shadow-sm']) }}>
    @if ($title || isset($actions))
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            @if ($title)
                <h2 class="text-sm font-semibold text-slate-900">{{ $title }}</h2>
            @endif
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div class="p-5">
        {{ $slot }}
    </div>
</div>

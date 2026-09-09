@props(['tabs' => null, 'paginator' => null, 'empty' => 'No records found.'])

<div class="rounded-xl border border-slate-200 bg-white shadow-sm">
    @if ($tabs)
        <div class="flex gap-1 border-b border-slate-100 px-3 pt-2">
            @foreach ($tabs as $tab)
                <a href="{{ $tab['href'] }}"
                   @class([
                       'rounded-t-lg px-4 py-2.5 text-sm font-medium transition',
                       'bg-emerald-50 text-emerald-800' => $tab['active'],
                       'text-slate-500 hover:text-slate-800' => ! $tab['active'],
                   ])>
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
    @endif

    @isset($actions)
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
            <div>{{ $filters ?? '' }}</div>
            <div class="flex items-center gap-2">{{ $actions }}</div>
        </div>
    @endisset

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50">
                {{ $head }}
            </thead>
            <tbody class="divide-y divide-slate-100">
                {{ $slot }}
            </tbody>
        </table>

        @if ($paginator && $paginator->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-slate-400">{{ $empty }}</p>
        @endif
    </div>

    @if ($paginator && $paginator->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $paginator->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@props(['action', 'label' => 'Delete', 'confirmText' => 'This action cannot be undone.'])

<div x-data="{ open: false }" class="inline-block">
    <button type="button" @click="open = true" {{ $attributes->class(['text-sm font-medium text-red-600 hover:text-red-800']) }}>
        {{ $label }}
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4" @keydown.escape.window="open = false">
        <div x-show="open" @click.outside="open = false" class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
            <h3 class="text-base font-semibold text-slate-900">Are you sure?</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $confirmText }}</p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="open = false" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg bg-red-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

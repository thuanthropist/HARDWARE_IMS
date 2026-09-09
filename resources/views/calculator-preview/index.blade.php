<x-layouts.admin title="Preview Calculator" subtitle="Run a planning tool exactly as a customer would, before it goes live">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($calculatorTypes as $calculatorType)
            <a href="{{ route('calculator-preview.show', $calculatorType) }}"
               class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md">
                <div class="mb-2 flex items-center justify-between">
                    <x-eva.badge color="emerald">{{ $calculatorType->department->name }}</x-eva.badge>
                    <x-eva.badge :color="$calculatorType->is_active ? 'emerald' : 'slate'">
                        {{ $calculatorType->is_active ? 'Active' : 'Inactive' }}
                    </x-eva.badge>
                </div>
                <h3 class="text-base font-semibold text-slate-900">{{ $calculatorType->name }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $calculatorType->description }}</p>
            </a>
        @endforeach
    </div>
</x-layouts.admin>

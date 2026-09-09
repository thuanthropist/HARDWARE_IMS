<x-layouts.admin title="Smart Planning Tools" subtitle="Configure the calculation engine behind each planning tool">
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="route('settings.index', ['tab' => 'planning'])">&larr; Back to Settings</x-eva.button>
    </x-slot:headerActions>

    <x-eva.table-card :paginator="$calculatorTypes">
        <x-slot:actions>
            <x-eva.button :href="route('calculator-types.create')">+ New Calculator</x-eva.button>
        </x-slot:actions>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Key</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Department</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Inputs</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Formulas</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Submissions</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
            </tr>
        </x-slot:head>

        @foreach ($calculatorTypes as $calculatorType)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $calculatorType->name }}</td>
                <td class="px-5 py-3 text-sm font-mono text-xs text-slate-500">{{ $calculatorType->key }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $calculatorType->department->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $calculatorType->input_fields_count }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $calculatorType->formulas_count }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $calculatorType->submissions_count }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="$calculatorType->is_active ? 'emerald' : 'slate'">
                        {{ $calculatorType->is_active ? 'Active' : 'Inactive' }}
                    </x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('calculator-preview.show', $calculatorType) }}" class="mr-3 font-medium text-emerald-700 hover:text-emerald-900">Preview</a>
                    <a href="{{ route('calculator-types.edit', $calculatorType) }}" class="mr-3 font-medium text-emerald-700 hover:text-emerald-900">Configure</a>
                    <x-eva.confirm-delete :action="route('calculator-types.destroy', $calculatorType)" />
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>

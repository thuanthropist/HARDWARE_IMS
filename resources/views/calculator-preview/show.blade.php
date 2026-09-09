<x-layouts.admin :title="'Preview: ' . $calculatorType->name" subtitle="Runs the live engine end-to-end. Nothing here is saved as a customer submission.">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-eva.card title="Customer Input">
            <form
                method="POST"
                action="{{ route('calculator-preview.run', $calculatorType) }}"
                x-data="{
                    values: @js($values),
                    addRow(fieldKey) {
                        if (!Array.isArray(this.values[fieldKey])) this.values[fieldKey] = [];
                        this.values[fieldKey].push({});
                    },
                    removeRow(fieldKey, index) { this.values[fieldKey].splice(index, 1); },
                }"
                class="space-y-4"
            >
                @csrf

                @foreach ($calculatorType->inputFields as $field)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ $field->label }} @if ($field->unit)<span class="text-slate-400">({{ $field->unit }})</span>@endif
                            @if ($field->is_required)<span class="text-red-500">*</span>@endif
                        </label>

                        @if ($field->input_type === 'repeater')
                            <div class="space-y-2 rounded-lg border border-slate-200 p-3">
                                <template x-for="(row, index) in (values['{{ $field->field_key }}'] || [])" :key="index">
                                    <div class="flex flex-wrap items-end gap-2 border-b border-slate-100 pb-2">
                                        @foreach ($field->repeaterFields() as $subField)
                                            <div>
                                                <label class="mb-0.5 block text-xs text-slate-500">{{ $subField['label'] }}</label>
                                                <input :name="'input_data[{{ $field->field_key }}][' + index + '][{{ $subField['key'] }}]'"
                                                       x-model="row['{{ $subField['key'] }}']"
                                                       type="{{ ($subField['type'] ?? 'text') === 'number' ? 'number' : 'text' }}"
                                                       class="w-28 rounded border border-slate-300 px-2 py-1 text-sm">
                                            </div>
                                        @endforeach
                                        <button type="button" @click="removeRow('{{ $field->field_key }}', index)" class="text-sm text-red-500 hover:text-red-700">✕</button>
                                    </div>
                                </template>
                                <button type="button" @click="addRow('{{ $field->field_key }}')" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">+ Add Row</button>
                            </div>
                        @elseif ($field->input_type === 'select' || $field->input_type === 'radio')
                            <select name="input_data[{{ $field->field_key }}]" x-model="values['{{ $field->field_key }}']"
                                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                                @foreach ($field->choices() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ $field->input_type === 'number' ? 'number' : 'text' }}"
                                   name="input_data[{{ $field->field_key }}]" x-model="values['{{ $field->field_key }}']"
                                   class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                        @endif
                    </div>
                @endforeach

                <div class="flex justify-end gap-2 pt-2">
                    <x-eva.button variant="secondary" :href="route('calculator-preview.index')">Back</x-eva.button>
                    <x-eva.button type="submit">Calculate</x-eva.button>
                </div>
            </form>
        </x-eva.card>

        <x-eva.card title="Material List & Pricing">
            @if ($error)
                <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ $error }}</p>
            @elseif (! $result)
                <p class="py-8 text-center text-sm text-slate-400">Fill in the form and click Calculate to see results here.</p>
            @else
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Item</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Qty</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Product</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($result['items'] as $key => $row)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-slate-900">{{ $row['label'] }}</td>
                                    <td class="px-3 py-2 text-right">{{ is_numeric($row['quantity']) ? number_format((float) $row['quantity'], 2) : $row['quantity'] }} {{ $row['unit'] }}</td>
                                    <td class="px-3 py-2">
                                        @if ($row['status'] === 'matched')
                                            <span class="text-slate-700">{{ $row['product']['name'] }}</span>
                                            @if (! $row['product']['in_stock'])
                                                <x-eva.badge color="amber">Backorder</x-eva.badge>
                                            @endif
                                        @elseif ($row['status'] === 'no_product_found')
                                            <x-eva.badge color="red">Material needed — contact us</x-eva.badge>
                                        @else
                                            <span class="text-slate-400">Informational only</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right font-medium">
                                        {{ $row['line_total'] !== null ? number_format((float) $row['line_total']) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="px-3 py-3 text-right font-medium text-slate-600">Estimated Total</td>
                                <td class="px-3 py-3 text-right font-semibold text-slate-900">{{ number_format($result['estimated_total']) }} TZS</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </x-eva.card>
    </div>
</x-layouts.admin>

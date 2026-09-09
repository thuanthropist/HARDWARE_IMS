<x-layouts.admin title="Configure Calculator" :subtitle="$calculatorType->name">
    <div class="space-y-6">
        <x-eva.card title="Calculator Details">
            <form method="POST" action="{{ route('calculator-types.update', $calculatorType) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-eva.input name="key" label="Key" :value="$calculatorType->key" required />
                    <x-eva.input name="name" label="Name" :value="$calculatorType->name" required />
                </div>
                <x-eva.select name="department_id" label="Department" required
                    :options="$departments->pluck('name', 'id')" :value="$calculatorType->department_id" />
                <x-eva.textarea name="description" label="Description" :value="$calculatorType->description" />
                <x-eva.checkbox name="is_active" label="Active" :checked="$calculatorType->is_active" />

                <div class="flex justify-end gap-2 pt-2">
                    <x-eva.button variant="secondary" :href="route('calculator-types.index')">Back</x-eva.button>
                    <x-eva.button type="submit">Save Changes</x-eva.button>
                </div>
            </form>
        </x-eva.card>

        {{-- INPUT FIELDS --}}
        <x-eva.card title="Input Fields">
            <p class="mb-4 text-sm text-slate-500">
                Defines the customer-facing input form. For <code class="rounded bg-slate-100 px-1">select</code>/<code class="rounded bg-slate-100 px-1">radio</code>
                fields, put <code class="rounded bg-slate-100 px-1">{"choices":{"value":"Label"}}</code> in Options. For
                <code class="rounded bg-slate-100 px-1">repeater</code> fields (repeatable groups like a Solar appliance list), put
                <code class="rounded bg-slate-100 px-1">{"fields":[{"key":"wattage","label":"Wattage","type":"number","unit":"W"}]}</code>.
            </p>

            @if ($calculatorType->inputFields->isNotEmpty())
                @foreach ($calculatorType->inputFields as $field)
                    <form id="field-form-{{ $field->id }}" method="POST" action="{{ route('calculator-input-fields.update', $field) }}">
                        @csrf
                        @method('PUT')
                    </form>
                @endforeach

                <div class="mb-6 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Key</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Label</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Type</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Unit</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Options (JSON)</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold uppercase text-slate-500">Req.</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Order</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($calculatorType->inputFields as $field)
                                @php $formId = 'field-form-'.$field->id; @endphp
                                <tr>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="field_key" value="{{ $field->field_key }}" class="w-28 rounded border border-slate-300 px-2 py-1 text-sm font-mono text-xs"></td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="label" value="{{ $field->label }}" class="w-32 rounded border border-slate-300 px-2 py-1 text-sm"></td>
                                    <td class="px-3 py-2">
                                        <select form="{{ $formId }}" name="input_type" class="rounded border border-slate-300 px-2 py-1 text-sm">
                                            @foreach (['number' => 'Number', 'select' => 'Select', 'radio' => 'Radio', 'text' => 'Text', 'repeater' => 'Repeater'] as $value => $label)
                                                <option value="{{ $value }}" @selected($field->input_type === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="unit" value="{{ $field->unit }}" class="w-16 rounded border border-slate-300 px-2 py-1 text-sm"></td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="options_json" value="{{ $field->options ? json_encode($field->options) : '' }}" class="w-56 rounded border border-slate-300 px-2 py-1 font-mono text-xs"></td>
                                    <td class="px-3 py-2 text-center"><input form="{{ $formId }}" type="checkbox" name="is_required" value="1" @checked($field->is_required) class="h-4 w-4 rounded border-slate-300 text-emerald-700"></td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="sort_order" type="number" value="{{ $field->sort_order }}" class="w-16 rounded border border-slate-300 px-2 py-1 text-sm"></td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <button form="{{ $formId }}" type="submit" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">Save</button>
                                        <form method="POST" action="{{ route('calculator-input-fields.destroy', $field) }}" class="inline" onsubmit="return confirm('Remove this input field?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-2 text-xs font-medium text-red-600 hover:text-red-800">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="rounded-lg border border-dashed border-slate-300 p-4">
                <p class="mb-3 text-sm font-medium text-slate-700">Add input field</p>
                <form method="POST" action="{{ route('calculator-input-fields.store', $calculatorType) }}" class="grid grid-cols-2 gap-3 sm:grid-cols-7">
                    @csrf
                    <input name="field_key" placeholder="field_key" required class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                    <input name="label" placeholder="Label" required class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                    <select name="input_type" class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                        <option value="number">Number</option>
                        <option value="select">Select</option>
                        <option value="radio">Radio</option>
                        <option value="text">Text</option>
                        <option value="repeater">Repeater</option>
                    </select>
                    <input name="unit" placeholder="Unit" class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                    <input name="options_json" placeholder="Options JSON" class="rounded border border-slate-300 px-2 py-1.5 font-mono text-xs">
                    <label class="flex items-center gap-1.5 text-xs text-slate-600">
                        <input type="checkbox" name="is_required" value="1" checked class="h-4 w-4 rounded border-slate-300 text-emerald-700"> Required
                    </label>
                    <button type="submit" class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-600">Add</button>
                </form>
            </div>
        </x-eva.card>

        {{-- FORMULAS --}}
        <x-eva.card title="Formulas">
            <p class="mb-4 text-sm text-slate-500">
                Evaluate in Order, top to bottom. Each formula's result becomes a variable (its Output Key) available to formulas below it —
                so a "breaker count" formula can reference an earlier "total circuits" formula. See the functions reference below.
            </p>

            @if ($calculatorType->formulas->isNotEmpty())
                @foreach ($calculatorType->formulas as $formula)
                    <form id="formula-form-{{ $formula->id }}" method="POST" action="{{ route('calculator-formulas.update', $formula) }}">
                        @csrf
                        @method('PUT')
                    </form>
                @endforeach

                <div class="mb-6 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Output Key</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Label</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Expression</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Unit</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Order</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($calculatorType->formulas as $formula)
                                @php $formId = 'formula-form-'.$formula->id; @endphp
                                <tr>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="output_key" value="{{ $formula->output_key }}" class="w-40 rounded border border-slate-300 px-2 py-1 font-mono text-xs"></td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="label" value="{{ $formula->label }}" class="w-36 rounded border border-slate-300 px-2 py-1 text-sm"></td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="formula_expression" value="{{ $formula->formula_expression }}" class="w-80 rounded border border-slate-300 px-2 py-1 font-mono text-xs"></td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="unit" value="{{ $formula->unit }}" class="w-16 rounded border border-slate-300 px-2 py-1 text-sm"></td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="sort_order" type="number" value="{{ $formula->sort_order }}" class="w-16 rounded border border-slate-300 px-2 py-1 text-sm"></td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <button form="{{ $formId }}" type="submit" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">Save</button>
                                        <form method="POST" action="{{ route('calculator-formulas.destroy', $formula) }}" class="inline" onsubmit="return confirm('Remove this formula?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-2 text-xs font-medium text-red-600 hover:text-red-800">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mb-6 rounded-lg border border-dashed border-slate-300 p-4">
                <p class="mb-3 text-sm font-medium text-slate-700">Add formula</p>
                <form method="POST" action="{{ route('calculator-formulas.store', $calculatorType) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-6">
                    @csrf
                    <input name="output_key" placeholder="output_key" required class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                    <input name="label" placeholder="Label" required class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                    <input name="formula_expression" placeholder="e.g. rooms * outlets_per_room" required class="sm:col-span-2 rounded border border-slate-300 px-2 py-1.5 font-mono text-xs">
                    <input name="unit" placeholder="Unit" class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                    <button type="submit" class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-600">Add</button>
                </form>
            </div>

            <details class="mb-6 rounded-lg bg-slate-50 p-4 text-xs text-slate-600">
                <summary class="cursor-pointer text-sm font-medium text-slate-700">Functions reference</summary>
                <ul class="mt-2 space-y-1 font-mono">
                    <li>ceil(x), floor(x), round(x, precision = 0), max(a, b), min(a, b)</li>
                    <li>count(repeaterFieldKey) — number of rows in a repeater field</li>
                    <li>sum(repeaterFieldKey, "subFieldKey") — sum of one sub-field across all rows</li>
                    <li>sum_product(repeaterFieldKey, "a", "b") — sum of subField-a × subField-b across all rows</li>
                    <li>Standard operators: + - * / %, comparisons (==, &gt;, &lt;=...), ternary (cond ? a : b)</li>
                </ul>
            </details>

            {{-- TEST RUNNER --}}
            <div
                x-data="{
                    sampleInputs: @js($testerDefaults),
                    testing: false,
                    testResult: null,
                    testError: null,
                    addRow(fieldKey) { this.sampleInputs[fieldKey].push({}); },
                    removeRow(fieldKey, index) { this.sampleInputs[fieldKey].splice(index, 1); },
                    async runTest() {
                        this.testing = true; this.testError = null; this.testResult = null;
                        try {
                            const response = await fetch('{{ route('calculator-formulas.test', $calculatorType) }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                body: JSON.stringify({ input_data: this.sampleInputs }),
                            });
                            const data = await response.json();
                            this.testing = false;
                            if (data.success) { this.testResult = data.output; } else { this.testError = data.error; }
                        } catch (e) {
                            this.testing = false;
                            this.testError = 'Request failed: ' + e.message;
                        }
                    },
                }"
                class="rounded-lg border border-emerald-200 bg-emerald-50/50 p-4"
            >
                <h3 class="mb-3 text-sm font-semibold text-slate-900">Test Formulas</h3>
                <p class="mb-3 text-xs text-slate-500">Runs the currently saved formulas against sample values — save a formula edit, then test it here.</p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($calculatorType->inputFields as $field)
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">{{ $field->label }}</label>

                            @if ($field->input_type === 'repeater')
                                <div class="space-y-2 rounded border border-slate-200 bg-white p-2">
                                    <template x-for="(row, index) in sampleInputs['{{ $field->field_key }}']" :key="index">
                                        <div class="flex items-end gap-1.5">
                                            @foreach ($field->repeaterFields() as $subField)
                                                <input :placeholder="'{{ $subField['label'] }}'" x-model="row['{{ $subField['key'] }}']"
                                                       class="w-24 rounded border border-slate-300 px-1.5 py-1 text-xs">
                                            @endforeach
                                            <button type="button" @click="removeRow('{{ $field->field_key }}', index)" class="text-xs text-red-500">✕</button>
                                        </div>
                                    </template>
                                    <button type="button" @click="addRow('{{ $field->field_key }}')" class="text-xs font-medium text-emerald-700">+ Add row</button>
                                </div>
                            @elseif ($field->input_type === 'select' || $field->input_type === 'radio')
                                <select x-model="sampleInputs['{{ $field->field_key }}']" class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                                    @foreach ($field->choices() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $field->input_type === 'number' ? 'number' : 'text' }}"
                                       x-model="sampleInputs['{{ $field->field_key }}']"
                                       class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            @endif
                        </div>
                    @endforeach
                </div>

                <button type="button" @click="runTest" :disabled="testing"
                        class="mt-4 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 disabled:opacity-50">
                    <span x-show="!testing">Run Test</span>
                    <span x-show="testing" x-cloak>Running…</span>
                </button>

                <p x-show="testError" x-text="testError" x-cloak class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600"></p>

                <div x-show="testResult" x-cloak class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Output</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Quantity</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Unit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(row, key) in testResult" :key="key">
                                <tr>
                                    <td class="px-3 py-2" x-text="row.label"></td>
                                    <td class="px-3 py-2 text-right font-medium" x-text="row.quantity"></td>
                                    <td class="px-3 py-2 text-slate-500" x-text="row.unit"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </x-eva.card>

        {{-- PRODUCT MAPPINGS --}}
        <x-eva.card title="Output → Product Mappings">
            <p class="mb-4 text-sm text-slate-500">
                Maps an output_key to real catalog products via department/category/attribute filters, e.g.
                <code class="rounded bg-slate-100 px-1">{"category":"cables-wires","attributes":{"cable_gauge":"2.5mm²"}}</code>.
                Outputs without a mapping are shown as informational only (no price).
            </p>

            @if ($calculatorType->outputProductMappings->isNotEmpty())
                @foreach ($calculatorType->outputProductMappings as $mapping)
                    <form id="mapping-form-{{ $mapping->id }}" method="POST" action="{{ route('calculator-output-mappings.update', $mapping) }}">
                        @csrf
                        @method('PUT')
                    </form>
                @endforeach

                <div class="mb-6 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Output Key</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Filters (JSON)</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Strategy</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($calculatorType->outputProductMappings as $mapping)
                                @php $formId = 'mapping-form-'.$mapping->id; @endphp
                                <tr>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="output_key" value="{{ $mapping->output_key }}" class="w-40 rounded border border-slate-300 px-2 py-1 font-mono text-xs"></td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="filters_json" value="{{ json_encode($mapping->product_attribute_filters) }}" class="w-96 rounded border border-slate-300 px-2 py-1 font-mono text-xs"></td>
                                    <td class="px-3 py-2">
                                        <select form="{{ $formId }}" name="selection_strategy" class="rounded border border-slate-300 px-2 py-1 text-sm">
                                            @foreach (['cheapest_in_stock' => 'Cheapest in stock', 'cheapest' => 'Cheapest', 'highest_stock' => 'Highest stock'] as $value => $label)
                                                <option value="{{ $value }}" @selected($mapping->selection_strategy === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <button form="{{ $formId }}" type="submit" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">Save</button>
                                        <form method="POST" action="{{ route('calculator-output-mappings.destroy', $mapping) }}" class="inline" onsubmit="return confirm('Remove this mapping?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-2 text-xs font-medium text-red-600 hover:text-red-800">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="rounded-lg border border-dashed border-slate-300 p-4">
                <p class="mb-3 text-sm font-medium text-slate-700">Add product mapping</p>
                <form method="POST" action="{{ route('calculator-output-mappings.store', $calculatorType) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                    @csrf
                    <input name="output_key" placeholder="output_key" required class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                    <input name="filters_json" placeholder='{"category":"...","attributes":{}}' required class="sm:col-span-2 rounded border border-slate-300 px-2 py-1.5 font-mono text-xs">
                    <select name="selection_strategy" class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                        <option value="cheapest_in_stock">Cheapest in stock</option>
                        <option value="cheapest">Cheapest</option>
                        <option value="highest_stock">Highest stock</option>
                    </select>
                    <button type="submit" class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-600 sm:col-span-4 sm:w-32">Add</button>
                </form>
            </div>
        </x-eva.card>
    </div>
</x-layouts.admin>

<x-layouts.storefront :title="$calculatorType->name">
    <section class="text-white" style="background: linear-gradient(135deg, var(--sf-dark), var({{ $calculatorType->department->colorVar }})); padding-block: 8rem 3rem;">
        <div class="container">
            <div class="sf-fade-up">
                <span class="sf-eyebrow mb-3">🧮 Free Planning Tool</span>
                <h1 class="fw-bold mb-2" style="font-size: 2rem;">{{ $calculatorType->name }}</h1>
                <p class="text-white-50 mb-0" style="max-width: 620px;">{{ $calculatorType->description }}</p>
            </div>
        </div>
    </section>

    <section class="sf-section pt-4">
        <div class="container">
            <div class="row g-4">
                {{-- INPUT FORM --}}
                <div class="col-lg-5" data-aos="fade-up">
                    <form
                        method="POST"
                        action="{{ route('storefront.tools.calculate', $calculatorType) }}"
                        x-data="{
                            values: @js($values),
                            addRow(fieldKey) {
                                if (!Array.isArray(this.values[fieldKey])) this.values[fieldKey] = [];
                                this.values[fieldKey].push({});
                            },
                            removeRow(fieldKey, index) { this.values[fieldKey].splice(index, 1); },
                        }"
                        class="card border-0 shadow-sm rounded-4 p-4"
                    >
                        @csrf

                        @foreach ($calculatorType->inputFields as $field)
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">
                                    {{ $field->label }}
                                    @if ($field->unit)<span class="text-body-secondary">({{ $field->unit }})</span>@endif
                                    @if ($field->is_required)<span class="text-danger">*</span>@endif
                                </label>

                                @if ($field->input_type === 'repeater')
                                    <div class="border rounded-3 p-3">
                                        <template x-for="(row, index) in (values['{{ $field->field_key }}'] || [])" :key="index">
                                            <div class="d-flex flex-wrap align-items-end gap-2 pb-2 mb-2 border-bottom">
                                                @foreach ($field->repeaterFields() as $subField)
                                                    <div>
                                                        <label class="form-label small text-body-secondary mb-1">{{ $subField['label'] }}</label>
                                                        <input :name="'input_data[{{ $field->field_key }}][' + index + '][{{ $subField['key'] }}]'"
                                                               x-model="row['{{ $subField['key'] }}']"
                                                               type="{{ ($subField['type'] ?? 'text') === 'number' ? 'number' : 'text' }}"
                                                               class="form-control form-control-sm" style="width: 120px;">
                                                    </div>
                                                @endforeach
                                                <button type="button" @click="removeRow('{{ $field->field_key }}', index)" class="btn btn-sm btn-outline-danger">&times;</button>
                                            </div>
                                        </template>
                                        <button type="button" @click="addRow('{{ $field->field_key }}')" class="btn btn-sm btn-outline-primary">+ Add Row</button>
                                    </div>
                                @elseif ($field->input_type === 'select' || $field->input_type === 'radio')
                                    <select name="input_data[{{ $field->field_key }}]" x-model="values['{{ $field->field_key }}']" class="form-select">
                                        @foreach ($field->choices() as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $field->input_type === 'number' ? 'number' : 'text' }}"
                                           name="input_data[{{ $field->field_key }}]" x-model="values['{{ $field->field_key }}']"
                                           class="form-control" @if ($field->is_required) required @endif>
                                @endif
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary w-100 mt-2">Calculate My Materials</button>
                    </form>
                </div>

                {{-- RESULTS --}}
                <div class="col-lg-7">
                    @if ($error)
                        <div class="alert alert-danger" data-aos="fade-up">{{ $error }}</div>
                    @elseif (! $submission)
                        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-body-secondary" data-aos="fade-up">
                            <p class="mb-0">Fill in the form and click <strong>Calculate My Materials</strong> to see a priced material list here.</p>
                        </div>
                    @else
                        <div class="card border-0 shadow-sm rounded-4 p-4 sf-fade-up">
                            <h5 class="fw-bold mb-3">Your Material List</h5>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr class="small text-body-secondary text-uppercase">
                                            <th>Item</th>
                                            <th class="text-end">Qty</th>
                                            <th>Product</th>
                                            <th class="text-end">Line Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($submission->computed_output as $row)
                                            <tr>
                                                <td class="fw-semibold">{{ $row['label'] }}</td>
                                                <td class="text-end">
                                                    {{ is_numeric($row['quantity']) ? number_format((float) $row['quantity'], 2) : $row['quantity'] }}
                                                    {{ $row['unit'] }}
                                                </td>
                                                <td>
                                                    @if ($row['status'] === 'matched')
                                                        {{ $row['product']['name'] }}
                                                        @if (! $row['product']['in_stock'])
                                                            <span class="sf-badge-stock sf-badge-low-stock ms-1">Backorder</span>
                                                        @endif
                                                    @elseif ($row['status'] === 'no_product_found')
                                                        <span class="sf-badge-stock sf-badge-out-stock">Material needed — contact us</span>
                                                    @else
                                                        <span class="text-body-secondary">Informational only</span>
                                                    @endif
                                                </td>
                                                <td class="text-end fw-semibold">
                                                    {{ $row['line_total'] !== null ? number_format((float) $row['line_total']) . ' TZS' : '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end fw-semibold">Estimated Total</td>
                                            <td class="text-end fs-5 fw-bold" style="color: var(--sf-primary);">
                                                {{ number_format((float) $submission->estimated_total) }} TZS
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                @if (collect($submission->computed_output)->contains('status', 'matched'))
                                    <form method="POST" action="{{ route('storefront.cart.addCalculatorResults', $submission) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">Add All to Cart</button>
                                    </form>
                                @endif
                                <a href="{{ route('storefront.quotes.create', ['submission' => $submission->id]) }}" class="btn btn-outline-primary">
                                    Request Professional Quote
                                </a>
                            </div>

                            @if (collect($submission->computed_output)->contains('status', 'no_product_found'))
                                <p class="small text-body-secondary mt-3 mb-0">
                                    Some materials on this list aren't in our catalog yet — request a quote and our team will source them for you.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.storefront>

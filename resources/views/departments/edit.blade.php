<x-layouts.admin title="Edit Department" :subtitle="$department->name">
    <div class="space-y-6">
        <x-eva.card title="Department Details">
            <form method="POST" action="{{ route('departments.update', $department) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="flex items-center gap-4">
                    @if ($department->image_path)
                        <img src="{{ asset('storage/'.$department->image_path) }}" alt="{{ $department->name }}" class="h-20 w-32 rounded-lg border border-slate-200 object-cover">
                    @else
                        <div class="flex h-20 w-32 items-center justify-center rounded-lg border border-dashed border-slate-300 text-xs text-slate-400">No photo</div>
                    @endif
                    <div class="flex-1">
                        <label for="image" class="mb-1 block text-sm font-medium text-slate-700">Cover Photo</label>
                        <input type="file" name="image" id="image" accept="image/*"
                            class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-amber-700 hover:file:bg-amber-100">
                        <p class="mt-1 text-xs text-slate-400">Shown as the background on the storefront department card and page header.</p>
                        @error('image')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <x-eva.input name="name" label="Name" :value="$department->name" required />
                <x-eva.input name="slug" label="Slug" :value="$department->slug" />
                <x-eva.input name="code" label="SKU Code" :value="$department->code" placeholder="e.g. ELEC, SOLR" />
                <x-eva.input name="icon" label="Icon" :value="$department->icon" />
                <x-eva.textarea name="description" label="Description" :value="$department->description" />
                <x-eva.input name="sort_order" label="Sort Order" type="number" :value="$department->sort_order" />
                <x-eva.checkbox name="is_active" label="Active" :checked="$department->is_active" />

                <div class="flex justify-end gap-2 pt-2">
                    <x-eva.button variant="secondary" :href="route('departments.index')">Back</x-eva.button>
                    <x-eva.button type="submit">Save Changes</x-eva.button>
                </div>
            </form>
        </x-eva.card>

        <x-eva.card title="Attribute Schema">
            <p class="mb-4 text-sm text-slate-500">
                These fields drive the dynamic attribute inputs shown on the Product form whenever this department is selected.
                Lower sort order shows first.
            </p>

            @if ($department->attributeSchemas->isNotEmpty())
                {{-- Detached forms: inputs below reference these via the form="" attribute, since a
                     <form> element cannot legally wrap <td>s across a <tr> boundary. --}}
                @foreach ($department->attributeSchemas as $schema)
                    <form id="attr-form-{{ $schema->id }}" method="POST" action="{{ route('department-attribute-schemas.update', $schema) }}">
                        @csrf
                        @method('PUT')
                    </form>
                @endforeach

                <div class="mb-6 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Label</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Key</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Type</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Unit</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Options (select)</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Required</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Order</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($department->attributeSchemas as $schema)
                                @php $formId = 'attr-form-'.$schema->id; @endphp
                                <tr>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="label" value="{{ $schema->label }}" class="w-32 rounded border border-slate-300 px-2 py-1 text-sm"></td>
                                    <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $schema->attribute_key }}</td>
                                    <td class="px-3 py-2">
                                        <select form="{{ $formId }}" name="input_type" class="rounded border border-slate-300 px-2 py-1 text-sm">
                                            @foreach (['text' => 'Text', 'number' => 'Number', 'select' => 'Select', 'textarea' => 'Textarea'] as $value => $label)
                                                <option value="{{ $value }}" @selected($schema->input_type === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="unit" value="{{ $schema->unit }}" class="w-20 rounded border border-slate-300 px-2 py-1 text-sm"></td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="options" value="{{ implode(', ', $schema->options ?? []) }}" placeholder="comma,separated" class="w-40 rounded border border-slate-300 px-2 py-1 text-sm"></td>
                                    <td class="px-3 py-2 text-center"><input form="{{ $formId }}" type="checkbox" name="is_required" value="1" @checked($schema->is_required) class="h-4 w-4 rounded border-slate-300 text-emerald-700"></td>
                                    <td class="px-3 py-2"><input form="{{ $formId }}" name="sort_order" type="number" value="{{ $schema->sort_order }}" class="w-16 rounded border border-slate-300 px-2 py-1 text-sm"></td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <button form="{{ $formId }}" type="submit" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">Save</button>
                                    </td>
                                    <td class="px-3 py-2">
                                        <form method="POST" action="{{ route('department-attribute-schemas.destroy', $schema) }}" onsubmit="return confirm('Remove this attribute?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-800">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="rounded-lg border border-dashed border-slate-300 p-4">
                <p class="mb-3 text-sm font-medium text-slate-700">Add attribute</p>
                <form method="POST" action="{{ route('department-attribute-schemas.store', $department) }}" class="grid grid-cols-2 gap-3 sm:grid-cols-6">
                    @csrf
                    <input name="label" placeholder="Label (e.g. Voltage)" required class="col-span-2 rounded border border-slate-300 px-2 py-1.5 text-sm sm:col-span-2">
                    <select name="input_type" class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                        <option value="text">Text</option>
                        <option value="number" selected>Number</option>
                        <option value="select">Select</option>
                        <option value="textarea">Textarea</option>
                    </select>
                    <input name="unit" placeholder="Unit (e.g. V)" class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                    <input name="options" placeholder="Options (select only)" class="rounded border border-slate-300 px-2 py-1.5 text-sm">
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 text-xs text-slate-600">
                            <input type="checkbox" name="is_required" value="1" class="h-4 w-4 rounded border-slate-300 text-emerald-700"> Required
                        </label>
                        <button type="submit" class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-600">Add</button>
                    </div>
                </form>
            </div>
        </x-eva.card>
    </div>
</x-layouts.admin>

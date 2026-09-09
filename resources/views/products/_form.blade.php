@php
    $selectedDepartment = old('department_id', $product->department_id ?? null);
    $selectedCategory = old('category_id', $product->category_id ?? null);
    $initialAttributes = old('attributes', $currentAttributes);
@endphp

<form
    method="POST"
    action="{{ $product ? route('products.update', $product) : route('products.store') }}"
    enctype="multipart/form-data"
    class="space-y-6"
    x-data="productForm({
        departmentSchemas: @js($departmentSchemas),
        departmentCategories: @js($departmentCategories),
        selectedDepartment: {{ $selectedDepartment ?? 'null' }},
        selectedCategory: {{ $selectedCategory ?? 'null' }},
        initialAttributes: @js($initialAttributes),
    })"
>
    @csrf
    @if ($product)
        @method('PUT')
    @endif

    <div class="flex items-center gap-4">
        @if ($product?->image_path)
            <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="h-20 w-20 rounded-lg border border-slate-200 object-cover">
        @else
            <div class="flex h-20 w-20 items-center justify-center rounded-lg border border-dashed border-slate-300 text-xs text-slate-400">No photo</div>
        @endif
        <div class="flex-1">
            <label for="image" class="mb-1 block text-sm font-medium text-slate-700">Photo</label>
            <input type="file" name="image" id="image" accept="image/*"
                class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-amber-700 hover:file:bg-amber-100">
            @error('image')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-eva.input name="name" label="Product Name" :value="$product->name ?? ''" required />
        <x-eva.input name="sku" label="SKU" :value="$product->sku ?? ''" required />
        <x-eva.input name="slug" label="Slug" :value="$product->slug ?? ''" placeholder="Auto-generated from name if left blank" />

        <div>
            <label for="department_id" class="mb-1 block text-sm font-medium text-slate-700">Department <span class="text-red-500">*</span></label>
            <select id="department_id" name="department_id" x-model.number="departmentId" @change="categoryId = null"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                <option value="">Select department</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
            @error('department_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="category_id" class="mb-1 block text-sm font-medium text-slate-700">Category <span class="text-red-500">*</span></label>
            <select id="category_id" name="category_id" x-model.number="categoryId"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                <option value="">Select category</option>
                <template x-for="cat in currentCategories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>
            @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <x-eva.select name="brand_id" label="Brand" placeholder="No brand"
            :options="$brands->pluck('name', 'id')" :value="$product->brand_id ?? ''" />

        <x-eva.input name="unit_of_measure" label="Unit of Measure" :value="$product->unit_of_measure ?? ''" placeholder="e.g. piece, bag, roll" required />

        <x-eva.input name="cost_price" label="Cost Price (TZS)" type="number" step="0.01" :value="$product->cost_price ?? ''" required />
        <x-eva.input name="selling_price" label="Selling Price (TZS)" type="number" step="0.01" :value="$product->selling_price ?? ''" required />
        <x-eva.input name="reorder_point" label="Reorder Point" type="number" :value="$product->reorder_point ?? 0" />
    </div>

    <x-eva.textarea name="description" label="Description" :value="$product->description ?? ''" />

    <div class="flex items-center gap-6">
        <x-eva.checkbox name="is_active" label="Active" :checked="$product->is_active ?? true" />
        <x-eva.checkbox name="track_batches" label="Track batches / expiry dates for this product" :checked="$product->track_batches ?? false" />
    </div>
    <p class="-mt-3 text-xs text-slate-400">Enable only for items with a shelf life (adhesives, sealants, certain batteries) — most hardware stock doesn't need this.</p>

    <div x-show="departmentId" x-cloak class="rounded-lg border border-slate-200 p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-900">Department Attributes</h3>
        <p x-show="currentSchema.length === 0" class="text-sm text-slate-400">This department has no attribute schema defined yet.</p>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <template x-for="attr in currentSchema" :key="attr.key">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        <span x-text="attr.label"></span>
                        <span x-show="attr.unit" x-text="'(' + attr.unit + ')'" class="text-slate-400"></span>
                        <span x-show="attr.required" class="text-red-500">*</span>
                    </label>

                    <template x-if="attr.type === 'select'">
                        <select :name="'attributes[' + attr.key + ']'" x-model="values[attr.key]"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select...</option>
                            <template x-for="opt in attr.options" :key="opt">
                                <option :value="opt" x-text="opt"></option>
                            </template>
                        </select>
                    </template>

                    <template x-if="attr.type === 'textarea'">
                        <textarea :name="'attributes[' + attr.key + ']'" x-model="values[attr.key]" rows="2"
                                  class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500"></textarea>
                    </template>

                    <template x-if="attr.type !== 'select' && attr.type !== 'textarea'">
                        <input :type="attr.type === 'number' ? 'number' : 'text'" :name="'attributes[' + attr.key + ']'" x-model="values[attr.key]"
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                    </template>
                </div>
            </template>
        </div>
        @error('attributes.*')
            <p class="mt-3 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex justify-end gap-2 pt-2">
        <x-eva.button variant="secondary" :href="route('products.index')">Cancel</x-eva.button>
        <x-eva.button type="submit">{{ $product ? 'Save Changes' : 'Create Product' }}</x-eva.button>
    </div>
</form>

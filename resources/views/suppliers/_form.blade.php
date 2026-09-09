<form method="POST" action="{{ $supplier ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="space-y-4">
    @csrf
    @if ($supplier)
        @method('PUT')
    @endif

    <x-eva.input name="name" label="Name" :value="$supplier->name ?? ''" required />
    <x-eva.input name="contact_person" label="Contact Person" :value="$supplier->contact_person ?? ''" />
    <x-eva.input name="phone" label="Phone" :value="$supplier->phone ?? ''" />
    <x-eva.input name="email" label="Email" type="email" :value="$supplier->email ?? ''" />
    <x-eva.textarea name="address" label="Address" :value="$supplier->address ?? ''" />

    <div>
        <p class="mb-1 block text-sm font-medium text-slate-700">Supplies Departments</p>
        <p class="mb-2 text-xs text-slate-500">Tagging narrows this supplier's product suggestions when building a purchase order.</p>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            @php $suppliedIds = old('department_ids', $supplier?->departments->pluck('id')->all() ?? []); @endphp
            @foreach ($departments as $department)
                <label class="flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700">
                    <input type="checkbox" name="department_ids[]" value="{{ $department->id }}"
                           @checked(in_array($department->id, $suppliedIds))
                           class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500">
                    {{ $department->name }}
                </label>
            @endforeach
        </div>
    </div>

    <x-eva.checkbox name="is_active" label="Active" :checked="$supplier->is_active ?? true" />

    <div class="flex justify-end gap-2 pt-2">
        <x-eva.button variant="secondary" :href="route('suppliers.index')">Cancel</x-eva.button>
        <x-eva.button type="submit">{{ $supplier ? 'Save Changes' : 'Create Supplier' }}</x-eva.button>
    </div>
</form>

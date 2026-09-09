<form method="POST" action="{{ $warehouse ? route('warehouses.update', $warehouse) : route('warehouses.store') }}" class="space-y-4">
    @csrf
    @if ($warehouse)
        @method('PUT')
    @endif

    <x-eva.input name="name" label="Name" :value="$warehouse->name ?? ''" required />
    <x-eva.input name="location" label="Location" :value="$warehouse->location ?? ''" />
    <x-eva.checkbox name="is_active" label="Active" :checked="$warehouse->is_active ?? true" />

    <div class="flex justify-end gap-2 pt-2">
        <x-eva.button variant="secondary" :href="route('warehouses.index')">Cancel</x-eva.button>
        <x-eva.button type="submit">{{ $warehouse ? 'Save Changes' : 'Create Warehouse' }}</x-eva.button>
    </div>
</form>

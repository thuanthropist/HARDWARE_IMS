<form method="POST" action="{{ $brand ? route('brands.update', $brand) : route('brands.store') }}" class="space-y-4">
    @csrf
    @if ($brand)
        @method('PUT')
    @endif

    <x-eva.input name="name" label="Name" :value="$brand->name ?? ''" required />
    <x-eva.input name="slug" label="Slug" :value="$brand->slug ?? ''" placeholder="Auto-generated from name if left blank" />

    <div class="flex justify-end gap-2 pt-2">
        <x-eva.button variant="secondary" :href="route('brands.index')">Cancel</x-eva.button>
        <x-eva.button type="submit">{{ $brand ? 'Save Changes' : 'Create Brand' }}</x-eva.button>
    </div>
</form>

<form method="POST" action="{{ $category ? route('categories.update', $category) : route('categories.store') }}" class="space-y-4">
    @csrf
    @if ($category)
        @method('PUT')
    @endif

    <x-eva.select name="department_id" label="Department" required
        :options="$departments->pluck('name', 'id')" placeholder="Select department"
        :value="$category->department_id ?? ''" />

    <x-eva.select name="parent_id" label="Parent Category (optional)"
        :options="$parentOptions->pluck('name', 'id')" placeholder="No parent"
        :value="$category->parent_id ?? ''" />

    <x-eva.input name="name" label="Name" :value="$category->name ?? ''" required />
    <x-eva.input name="slug" label="Slug" :value="$category->slug ?? ''" placeholder="Auto-generated from name if left blank" />

    <div class="flex justify-end gap-2 pt-2">
        <x-eva.button variant="secondary" :href="route('categories.index')">Cancel</x-eva.button>
        <x-eva.button type="submit">{{ $category ? 'Save Changes' : 'Create Category' }}</x-eva.button>
    </div>
</form>

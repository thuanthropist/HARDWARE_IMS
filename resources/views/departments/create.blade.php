<x-layouts.admin title="New Department">
    <x-eva.card>
        <form method="POST" action="{{ route('departments.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="image" class="mb-1 block text-sm font-medium text-slate-700">Cover Photo</label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-amber-700 hover:file:bg-amber-100">
                <p class="mt-1 text-xs text-slate-400">Shown as the background on the storefront department card and page header.</p>
                @error('image')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <x-eva.input name="name" label="Name" required />
            <x-eva.input name="slug" label="Slug" placeholder="Auto-generated from name if left blank" />
            <x-eva.input name="code" label="SKU Code" placeholder="Auto-generated from name if left blank — e.g. ELEC, SOLR" />
            <x-eva.input name="icon" label="Icon" placeholder="e.g. cube, bolt, sun, wrench" />
            <x-eva.textarea name="description" label="Description" />
            <x-eva.input name="sort_order" label="Sort Order" type="number" value="0" />
            <x-eva.checkbox name="is_active" label="Active" :checked="true" />

            <div class="flex justify-end gap-2 pt-2">
                <x-eva.button variant="secondary" :href="route('departments.index')">Cancel</x-eva.button>
                <x-eva.button type="submit">Create Department</x-eva.button>
            </div>
        </form>
    </x-eva.card>
</x-layouts.admin>

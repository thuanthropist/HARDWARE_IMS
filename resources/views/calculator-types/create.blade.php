<x-layouts.admin title="New Calculator">
    <x-eva.card>
        <form method="POST" action="{{ route('calculator-types.store') }}" class="space-y-4">
            @csrf
            <x-eva.input name="key" label="Key" placeholder="e.g. wiring, solar, plumbing, building" required />
            <x-eva.input name="name" label="Name" placeholder="e.g. Wiring Consultant" required />
            <x-eva.select name="department_id" label="Department" required placeholder="Select department"
                :options="$departments->pluck('name', 'id')" />
            <x-eva.textarea name="description" label="Description" />
            <x-eva.checkbox name="is_active" label="Active" :checked="true" />

            <div class="flex justify-end gap-2 pt-2">
                <x-eva.button variant="secondary" :href="route('calculator-types.index')">Cancel</x-eva.button>
                <x-eva.button type="submit">Create Calculator</x-eva.button>
            </div>
        </form>
    </x-eva.card>
</x-layouts.admin>

<x-layouts.admin title="New Role">
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="route('settings.index', ['tab' => 'users'])">&larr; Back to Settings</x-eva.button>
    </x-slot:headerActions>

    <x-eva.card>
        <form method="POST" action="{{ route('roles.store') }}" class="space-y-5">
            @csrf
            <x-eva.input name="name" label="Role Name" required placeholder="e.g. Sales Assistant" />

            <div>
                <p class="mb-2 text-sm font-medium text-slate-700">Permissions</p>
                @include('roles._permission-groups', ['checked' => []])
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-eva.button variant="secondary" :href="route('settings.index', ['tab' => 'users'])">Cancel</x-eva.button>
                <x-eva.button type="submit">Create Role</x-eva.button>
            </div>
        </form>
    </x-eva.card>
</x-layouts.admin>

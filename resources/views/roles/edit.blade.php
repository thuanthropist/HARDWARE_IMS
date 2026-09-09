<x-layouts.admin title="Edit Role: {{ $role->name }}">
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="route('settings.index', ['tab' => 'users'])">&larr; Back to Settings</x-eva.button>
    </x-slot:headerActions>

    <x-eva.card>
        <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <p class="mb-1 block text-sm font-medium text-slate-700">Role Name</p>
                <p class="text-sm text-slate-900">{{ $role->name }}</p>
                <p class="mt-1 text-xs text-slate-400">Role names can't be changed once created — several places in the app (like Notifications recipients) match roles by name.</p>
            </div>

            <div>
                <p class="mb-2 text-sm font-medium text-slate-700">Permissions</p>
                @include('roles._permission-groups', ['checked' => $rolePermissions])
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-eva.button variant="secondary" :href="route('settings.index', ['tab' => 'users'])">Cancel</x-eva.button>
                <x-eva.button type="submit">Save Permissions</x-eva.button>
            </div>
        </form>
    </x-eva.card>
</x-layouts.admin>

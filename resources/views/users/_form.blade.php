@php
    $currentRole = $user?->roles->first()?->name;
@endphp

<form method="POST" action="{{ $user ? route('users.update', $user) : route('users.store') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @if ($user)
        @method('PUT')
    @endif

    @include('users._profile-fields', ['user' => $user])

    <div class="grid gap-4 sm:grid-cols-2">
        <x-eva.select name="role" label="User Role" required placeholder="Select role"
            :options="$roles->pluck('name', 'name')" :value="$currentRole" />

        <div>
            <x-eva.select name="department_id" label="Department" placeholder="All departments (unrestricted)"
                :options="$departments->pluck('name', 'id')" :value="$user->department_id ?? ''" />
            <p class="mt-1 text-xs text-slate-400">Leave unset for unrestricted analytics access.</p>
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-2">
        <x-eva.button variant="secondary" :href="route('users.index')">Cancel</x-eva.button>
        <x-eva.button type="submit" variant="accent">{{ $user ? 'Save Changes' : 'Create Staff' }}</x-eva.button>
    </div>
</form>

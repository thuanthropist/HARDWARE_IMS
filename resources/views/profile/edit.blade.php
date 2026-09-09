<x-layouts.admin title="Edit My Account">
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="route('profile.show')">&larr; Back to My Profile</x-eva.button>
    </x-slot:headerActions>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-base font-semibold text-emerald-800">Account Details</h2>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            @include('users._profile-fields', ['user' => $user])

            <div class="flex justify-end gap-2 pt-2">
                <x-eva.button variant="secondary" :href="route('profile.show')">Cancel</x-eva.button>
                <x-eva.button type="submit" variant="accent">Save Changes</x-eva.button>
            </div>
        </form>
    </div>
</x-layouts.admin>

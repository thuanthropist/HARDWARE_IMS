<x-layouts.admin title="New Staff">
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="route('users.index')">&larr; Back to Staff</x-eva.button>
    </x-slot:headerActions>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-base font-semibold text-emerald-800">Staff Details</h2>
        @include('users._form', ['user' => null])
    </div>
</x-layouts.admin>

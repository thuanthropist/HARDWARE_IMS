@php
    $firstName = Str::before($user->name, ' ');
    $lastName = Str::contains($user->name, ' ') ? Str::after($user->name, ' ') : '';
    $editRoute ??= route('users.edit', $user);
    $backRoute ??= route('users.index');
    $backLabel ??= '← Back to Staff';
@endphp

<x-layouts.admin title="Staff Profile" :subtitle="$user->name">
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="$backRoute">{{ $backLabel }}</x-eva.button>
    </x-slot:headerActions>

    <div class="space-y-5">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                @if ($user->avatar_path)
                    <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-full object-cover">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-xl font-semibold text-amber-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <p class="text-lg font-semibold text-emerald-800">{{ $user->name }}</p>
                    <p class="text-sm text-slate-600">{{ $user->roles->pluck('name')->implode(', ') ?: 'No role assigned' }}</p>
                    <p class="text-sm text-slate-400">{{ $user->department->name ?? 'All departments' }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-semibold text-emerald-800">Personal Information</h2>
                <x-eva.button variant="accent" :href="$editRoute">
                    Edit
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                </x-eva.button>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">First Name</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $firstName }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Last Name</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $lastName ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Date of Birth</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $user->date_of_birth?->format('d-m-Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Email Address</p>
                    <p class="mt-1 break-all text-sm text-slate-900">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Phone Number</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $user->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">User Role</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $user->roles->pluck('name')->implode(', ') ?: '—' }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-semibold text-emerald-800">Address</h2>
                <x-eva.button variant="secondary" :href="$editRoute">
                    Edit
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                </x-eva.button>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Country</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $user->country ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">City</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $user->city ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Postal Code</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $user->postal_code ?? '—' }}</p>
                </div>
                <div class="sm:col-span-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Street Address</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $user->address ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>

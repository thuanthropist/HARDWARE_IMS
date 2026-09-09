<div class="flex items-center gap-4">
    @if ($user?->avatar_path)
        <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-full border border-slate-200 object-cover">
    @else
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-lg font-semibold text-amber-700">
            {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
        </div>
    @endif
    <div class="flex-1">
        <label for="avatar" class="mb-1 block text-sm font-medium text-slate-700">Photo</label>
        <input type="file" name="avatar" id="avatar" accept="image/*"
            class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-amber-700 hover:file:bg-amber-100">
        @error('avatar')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <x-eva.input name="name" label="Full Name" :value="$user->name ?? ''" required />
    <x-eva.input name="email" label="Email" type="email" :value="$user->email ?? ''" required />
</div>

<x-eva.input name="password" label="Password" type="password" :required="! $user"
    placeholder="{{ $user ? 'Leave blank to keep current password' : '' }}" />

<div class="grid gap-4 sm:grid-cols-2">
    <x-eva.input name="phone" label="Phone Number" :value="$user->phone ?? ''" />
    <x-eva.input name="date_of_birth" label="Date of Birth" type="date" :value="$user?->date_of_birth?->format('Y-m-d') ?? ''" />
</div>

<x-eva.input name="address" label="Address" :value="$user->address ?? ''" />

<div class="grid gap-4 sm:grid-cols-3">
    <x-eva.input name="city" label="City" :value="$user->city ?? ''" />
    <x-eva.input name="country" label="Country" :value="$user->country ?? ''" />
    <x-eva.input name="postal_code" label="Postal Code" :value="$user->postal_code ?? ''" />
</div>

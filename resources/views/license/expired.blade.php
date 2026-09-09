<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>License Expired · {{ setting('business.name', 'Hardware IMS') }}</title>
    @if (setting('business.logo_path'))
        <link rel="icon" href="{{ asset('storage/'.setting('business.logo_path')) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-full items-center justify-center font-sans antialiased">
    <div class="w-full max-w-md px-4">
        <div class="mb-6 flex flex-col items-center text-center">
            @if (setting('business.logo_path'))
                <img src="{{ asset('storage/'.setting('business.logo_path')) }}" alt="{{ setting('business.name', 'Hardware IMS') }}" class="mb-3 h-12 w-12 rounded-xl object-contain">
            @else
                <span class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-700 text-lg font-bold text-white">
                    {{ strtoupper(substr(setting('business.name', 'Hardware IMS'), 0, 2)) }}
                </span>
            @endif
            <h1 class="text-lg font-semibold text-slate-900">{{ setting('business.name', 'Hardware IMS') }}</h1>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">License Expired</h2>
                    <p class="text-sm text-slate-500">Access is blocked until it's renewed.</p>
                </div>
            </div>

            @can('manage-license')
                <p class="mb-4 text-sm text-slate-600">
                    Enter a renewal key for this license, or a new license key, to restore access immediately.
                </p>

                <form method="POST" action="{{ route('license.activate') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="license_key" class="mb-1 block text-sm font-medium text-slate-700">License Key</label>
                        <input type="text" name="license_key" id="license_key" required autofocus
                            class="block w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 {{ $errors->has('license_key') ? 'border-red-300' : 'border-slate-300' }}"
                            placeholder="Paste your renewal or new license key">
                        @error('license_key')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-eva.alert type="error" :message="session('error')" />
                    <x-eva.alert type="success" :message="session('success')" />

                    <button type="submit" class="w-full rounded-lg bg-amber-500 px-3.5 py-2 text-sm font-medium text-white transition hover:bg-amber-600">
                        Activate License
                    </button>
                </form>

                <p class="mt-3 text-center text-xs text-slate-400">
                    Prefer the full license screen? <a href="{{ route('license.index') }}" class="font-medium text-emerald-700 hover:text-emerald-900">Open License Settings &rarr;</a>
                </p>
            @else
                <p class="mb-4 text-sm text-slate-600">
                    Ask an administrator to renew or activate a license key to restore access. If you believe this is a mistake, contact support below.
                </p>
            @endcan

            @if (config('license-client.support.name') || config('license-client.support.email') || config('license-client.support.phone'))
                <div class="mt-5 rounded-lg bg-slate-50 p-3 text-left text-sm">
                    <p class="font-medium text-slate-700">{{ config('license-client.support.name', 'Support') }}</p>
                    @if ($email = config('license-client.support.email'))
                        <p class="text-slate-500">Email: <a href="mailto:{{ $email }}" class="text-emerald-700 hover:text-emerald-900">{{ $email }}</a></p>
                    @endif
                    @if ($phone = config('license-client.support.phone'))
                        <p class="text-slate-500">Phone: <a href="tel:{{ $phone }}" class="text-emerald-700 hover:text-emerald-900">{{ $phone }}</a></p>
                    @endif
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-sm text-slate-400 hover:text-slate-600">Log out</button>
        </form>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in · {{ setting('business.name', 'Hardware IMS') }}</title>
    @if (setting('business.logo_path'))
        <link rel="icon" href="{{ asset('storage/'.setting('business.logo_path')) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-full items-center justify-center font-sans antialiased">
    <div class="w-full max-w-sm">
        <div class="mb-8 flex flex-col items-center">
            @if (setting('business.logo_path'))
                <img src="{{ asset('storage/'.setting('business.logo_path')) }}" alt="{{ setting('business.name', 'Hardware IMS') }}" class="mb-3 h-12 w-12 rounded-xl object-contain">
            @else
                <span class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-700 text-lg font-bold text-white">
                    {{ strtoupper(substr(setting('business.name', 'Hardware IMS'), 0, 2)) }}
                </span>
            @endif
            <h1 class="text-xl font-semibold text-slate-900">{{ setting('business.name', 'Hardware IMS') }}</h1>
            <p class="text-sm text-slate-500">Sign in to the admin panel</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-eva.alert type="error" :message="$errors->first()" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <x-eva.input name="email" label="Email address" type="email" required autofocus />
                <x-eva.input name="password" label="Password" type="password" required />

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500">
                    Remember me
                </label>

                <x-eva.button type="submit" class="w-full justify-center">Log in</x-eva.button>
            </form>
        </div>
    </div>
</body>
</html>

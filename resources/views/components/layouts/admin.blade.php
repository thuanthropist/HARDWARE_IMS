<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} · {{ setting('business.name', 'Hardware IMS') }}</title>
    @if (setting('business.logo_path'))
        <link rel="icon" href="{{ asset('storage/'.setting('business.logo_path')) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full overflow-hidden font-sans antialiased text-slate-800">
    <div class="flex h-full" x-data="{ sidebarOpen: false }">
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col border-r border-slate-200 bg-white text-slate-600 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center gap-2 px-5 py-5">
                @if (setting('business.logo_path'))
                    <img src="{{ asset('storage/'.setting('business.logo_path')) }}" alt="{{ setting('business.name', 'Hardware IMS') }}" class="h-8 w-8 rounded-lg object-contain">
                @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-sm font-bold text-white">
                        {{ strtoupper(substr(setting('business.name', 'Hardware IMS'), 0, 2)) }}
                    </span>
                @endif
                <span class="truncate text-lg font-semibold tracking-tight text-slate-900">{{ setting('business.name', 'Hardware IMS') }}</span>
                <button type="button" @click="sidebarOpen = false" class="ml-auto rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Overview</p>

                <x-eva.sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" /></svg>
                    </x-slot:icon>
                    Dashboard
                </x-eva.sidebar-link>

                @can('view-reports')
                <x-eva.sidebar-link :href="route('analytics.index')" :active="request()->routeIs('analytics.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6m0 13H5a1 1 0 01-1-1V6a1 1 0 011-1h4m0 14h6m0 0h4a1 1 0 001-1v-9a1 1 0 00-1-1h-4m0 11V9" /></svg>
                    </x-slot:icon>
                    Analytics
                </x-eva.sidebar-link>
                @endcan

                @can('manage-products')
                <p class="mb-1 mt-4 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Catalog</p>

                <x-eva.sidebar-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </x-slot:icon>
                    Products
                </x-eva.sidebar-link>

                <x-eva.sidebar-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z" /></svg>
                    </x-slot:icon>
                    Categories
                </x-eva.sidebar-link>

                <x-eva.sidebar-link :href="route('brands.index')" :active="request()->routeIs('brands.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M3 11l8-8h6l4 4v6l-8 8-10-10z" /></svg>
                    </x-slot:icon>
                    Brands
                </x-eva.sidebar-link>
                @endcan

                @canany(['manage-suppliers', 'manage-stock'])
                <p class="mb-1 mt-4 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Procurement</p>

                @can('manage-suppliers')
                <x-eva.sidebar-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h13l4 4v6h-4M3 7v10h10M3 7l3-4h7l3 4M7 21a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4z" /></svg>
                    </x-slot:icon>
                    Suppliers
                </x-eva.sidebar-link>
                @endcan

                @can('manage-stock')
                <x-eva.sidebar-link :href="route('purchase-orders.index')" :active="request()->routeIs('purchase-orders.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 2h6a1 1 0 011 1v2H8V3a1 1 0 011-1zM5 6h14l-1 15H6L5 6zm4 4v7m6-7v7" /></svg>
                    </x-slot:icon>
                    Purchase Orders
                </x-eva.sidebar-link>
                @endcan
                @endcanany

                @canany(['manage-warehouses', 'manage-stock'])
                <p class="mb-1 mt-4 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Inventory</p>

                @can('manage-warehouses')
                <x-eva.sidebar-link :href="route('warehouses.index')" :active="request()->routeIs('warehouses.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21V8l9-5 9 5v13M9 21v-6h6v6" /></svg>
                    </x-slot:icon>
                    Warehouses
                </x-eva.sidebar-link>
                @endcan

                @can('manage-stock')
                <x-eva.sidebar-link :href="route('product-lookup.index')" :active="request()->routeIs('product-lookup.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7V5a1 1 0 011-1h2M4 17v2a1 1 0 001 1h2m10-14h2a1 1 0 011 1v2m-4 12h2a1 1 0 001-1v-2M8 8v8m3-8v8m3-8v8m3-8v8" /></svg>
                    </x-slot:icon>
                    Product Lookup
                </x-eva.sidebar-link>

                <x-eva.sidebar-link :href="route('stock-adjustments.index')" :active="request()->routeIs('stock-adjustments.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0-16l-4 4m4-4l4 4m-4 12l-4-4m4 4l4-4" /></svg>
                    </x-slot:icon>
                    Stock Adjustments
                </x-eva.sidebar-link>

                <x-eva.sidebar-link :href="route('stock-transfers.index')" :active="request()->routeIs('stock-transfers.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 7l4-4M4 7l4 4M20 17H4m16 0l-4-4m4 4l-4 4" /></svg>
                    </x-slot:icon>
                    Stock Transfers
                </x-eva.sidebar-link>

                <x-eva.sidebar-link :href="route('product-batches.index')" :active="request()->routeIs('product-batches.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </x-slot:icon>
                    Batches &amp; Expiry
                </x-eva.sidebar-link>

                <x-eva.sidebar-link :href="route('stock-movements.index')" :active="request()->routeIs('stock-movements.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" /></svg>
                    </x-slot:icon>
                    Stock Movements
                </x-eva.sidebar-link>
                @endcan
                @endcanany

                @canany(['manage-orders', 'manage-quotes', 'manage-pos'])
                <p class="mb-1 mt-4 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Sales</p>

                @can('manage-orders')
                <x-eva.sidebar-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 2h6a1 1 0 011 1v2H8V3a1 1 0 011-1zM5 6h14l-1 15H6L5 6zm4 4v7m6-7v7" /></svg>
                    </x-slot:icon>
                    <span class="flex flex-1 items-center justify-between">
                        Orders
                        @php($pendingOrdersCount = \App\Models\Order::pendingConfirmation()->count())
                        @if ($pendingOrdersCount > 0)
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-semibold text-white">{{ $pendingOrdersCount }}</span>
                        @endif
                    </span>
                </x-eva.sidebar-link>
                @endcan

                @can('manage-quotes')
                <x-eva.sidebar-link :href="route('quotes.index')" :active="request()->routeIs('quotes.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h6m-6 4h3M5 3h14a1 1 0 011 1v16a1 1 0 01-1 1H5a1 1 0 01-1-1V4a1 1 0 011-1z" /></svg>
                    </x-slot:icon>
                    <span class="flex flex-1 items-center justify-between">
                        Quotes
                        @php($pendingQuotesCount = \App\Models\Quote::pending()->count())
                        @if ($pendingQuotesCount > 0)
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-semibold text-white">{{ $pendingQuotesCount }}</span>
                        @endif
                    </span>
                </x-eva.sidebar-link>
                @endcan

                @can('manage-pos')
                <x-eva.sidebar-link :href="route('pos.index')" :active="request()->routeIs('pos.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M6 10V6a2 2 0 012-2h8a2 2 0 012 2v4m-14 0v8a2 2 0 002 2h10a2 2 0 002-2v-8" /></svg>
                    </x-slot:icon>
                    Point of Sale
                </x-eva.sidebar-link>
                @endcan
                @endcanany

                @canany(['manage-settings', 'manage-departments', 'manage-users', 'manage-license', 'manage-calculators'])
                <p class="mb-1 mt-4 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Administration</p>

                <x-eva.sidebar-link :href="route('settings.index')" :active="request()->routeIs('settings.*') || request()->routeIs('license.*') || request()->routeIs('audit-logs.*') || request()->routeIs('calculator-types.*') || request()->routeIs('calculator-preview.*')">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </x-slot:icon>
                    Settings
                </x-eva.sidebar-link>
                @endcanany
            </nav>

            <div class="border-t border-slate-200 px-4 py-4">
                <a href="{{ route('profile.show') }}" class="mb-3 flex items-center gap-3 rounded-lg p-1 -m-1 transition hover:bg-slate-100">
                    @if (auth()->user()->avatar_path)
                        <img src="{{ asset('storage/'.auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}" class="h-9 w-9 rounded-full object-cover">
                    @else
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700">
                            {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-slate-900">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-400">{{ auth()->user()->getRoleNames()->first() }}</p>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 5v1a3 3 0 01-3 3H6a3 3 0 01-3-3V6a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <x-license::grace-banner />

            <div class="px-8 pt-6">
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-emerald-900 px-5 py-3">
                    <button type="button" @click="sidebarOpen = true" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-slate-600 hover:bg-emerald-50 lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>

                    @can('manage-stock')
                        <form method="GET" action="{{ route('product-lookup.index') }}" class="min-w-0 flex-1 sm:max-w-xs">
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M19 11a8 8 0 11-16 0 8 8 0 0116 0z" /></svg>
                                <input type="text" name="code" placeholder="Quick SKU / barcode lookup"
                                    class="w-full rounded-lg border-0 bg-white py-2 pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-amber-400">
                            </div>
                        </form>
                    @else
                        <span></span>
                    @endcan

                    <div class="flex items-center gap-3">
                        <span class="hidden text-sm font-medium text-emerald-100 sm:block">{{ now()->translatedFormat('l, d F') }}</span>

                        @auth
                            <div class="relative" x-data="{ open: false }">
                                <button type="button" @click="open = !open" @click.outside="open = false"
                                        class="relative flex h-9 w-9 items-center justify-center rounded-lg bg-white text-slate-600 hover:bg-emerald-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    @php($unreadNotificationCount = auth()->user()->unreadNotifications()->count())
                                    @if ($unreadNotificationCount > 0)
                                        <span class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-500 px-1 text-xs font-semibold text-white">
                                            {{ $unreadNotificationCount }}
                                        </span>
                                    @endif
                                </button>

                                <div x-show="open" x-cloak class="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-slate-200 bg-white shadow-lg">
                                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                        <span class="text-sm font-semibold text-slate-900">Notifications</span>
                                        <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">View all</a>
                                    </div>
                                    <div class="max-h-80 overflow-y-auto">
                                        @forelse (auth()->user()->unreadNotifications()->latest()->take(8)->get() as $notification)
                                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                                @csrf
                                                <button type="submit" class="block w-full border-b border-slate-50 px-4 py-3 text-left text-sm hover:bg-slate-50">
                                                    {{ $notification->data['title'] ?? 'Notification' }}
                                                    <span class="mt-0.5 block text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                                </button>
                                            </form>
                                        @empty
                                            <p class="px-4 py-6 text-center text-sm text-slate-400">You're all caught up.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>

            <header class="flex items-center justify-between px-8 py-4">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
                    @isset($subtitle)
                        <p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>
                    @endisset
                </div>
                <div class="flex items-center gap-3">
                    {{ $headerActions ?? '' }}
                </div>
            </header>

            <main class="flex-1 overflow-y-auto px-8 py-6">
                <x-eva.alert type="success" :message="session('success')" />
                <x-eva.alert type="error" :message="session('error')" />

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>

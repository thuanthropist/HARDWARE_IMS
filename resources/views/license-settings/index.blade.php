<x-layouts.admin title="License">
    <x-slot:subtitle>Manage the license that unlocks this app's premium features.</x-slot:subtitle>
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="route('settings.index', ['tab' => 'license'])">&larr; Back to Settings</x-eva.button>
    </x-slot:headerActions>

    @php
        $statusColor = match ($status) {
            'active' => 'emerald',
            'grace' => 'amber',
            'expired', 'invalid' => 'red',
            default => 'slate',
        };
        $statusLabel = match ($status) {
            'active' => 'Active',
            'grace' => 'Grace Period',
            'expired' => 'Expired',
            'invalid' => 'Invalid',
            default => 'Not Activated',
        };
    @endphp

    <div class="space-y-6">
        @if ($status === 'grace')
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Your license expired{{ $daysUntilExpiry !== null ? ' '.abs($daysUntilExpiry).' day(s) ago' : '' }} and is running on a grace period.
                Renew soon to avoid losing access when the grace period ends.
            </div>
        @elseif ($status === 'expired' || $status === 'invalid')
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                Your license is {{ $status }}. Admin panel access is blocked until a valid license key is activated.
            </div>
        @endif

        <x-eva.card title="License Status">
            <x-slot:actions>
                <x-eva.badge :color="$statusColor">{{ $statusLabel }}</x-eva.badge>
            </x-slot:actions>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Product</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-900">{{ $productCode }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">License Key</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-900">{{ $licenseKey ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Expires</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-900">
                        {{ $expiresAt?->format('d M Y') ?? '—' }}
                        @if ($daysUntilExpiry !== null)
                            <span class="text-slate-400">({{ $daysUntilExpiry }} days)</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Max Users</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-900">{{ $maxUsers ?? 'Unlimited' }}</dd>
                </div>
            </dl>

            <div class="mt-5">
                <dt class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">Enabled Features</dt>
                <dd class="flex flex-wrap gap-2">
                    @forelse ($features as $feature)
                        <x-eva.badge color="emerald">{{ $feature }}</x-eva.badge>
                    @empty
                        <span class="text-sm text-slate-400">No features enabled.</span>
                    @endforelse
                </dd>
            </div>
        </x-eva.card>

        <x-eva.card title="Activate License">
            <form method="POST" action="{{ route('license.activate') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <x-eva.input name="license_key" label="License Key" required :value="$licenseKey" />
                </div>
                <x-eva.button type="submit">Activate</x-eva.button>
            </form>
            <p class="mt-3 text-xs text-slate-400">
                Activating replaces any currently stored license token. License server: {{ $serverUrl }}
            </p>
        </x-eva.card>
    </div>
</x-layouts.admin>

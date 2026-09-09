<x-layouts.admin title="Settings">
    <x-slot:subtitle>Configure system-wide behavior instead of scattering it across .env or hardcoded values.</x-slot:subtitle>

    @php
        $availableTabs = array_filter([
            'general' => auth()->user()->can('manage-settings') ? 'General' : null,
            'departments' => auth()->user()->can('manage-departments') ? 'Departments & Attributes' : null,
            'planning' => auth()->user()->can('manage-calculators') ? 'Planning Tools' : null,
            'numbering' => auth()->user()->can('manage-settings') ? 'Numbering Formats' : null,
            'tax' => auth()->user()->can('manage-settings') ? 'Tax & Currency' : null,
            'notifications' => auth()->user()->can('manage-settings') ? 'Notifications' : null,
            'users' => auth()->user()->can('manage-users') ? 'Users & Roles' : null,
            'audit' => auth()->user()->can('manage-users') ? 'Audit Log' : null,
            'whatsapp' => auth()->user()->can('manage-settings') ? 'WhatsApp & Contact' : null,
            'backup' => auth()->user()->can('manage-settings') ? 'Backup' : null,
            'license' => auth()->user()->can('manage-license') ? 'License' : null,
        ]);
        $requestedTab = request('tab');
        $defaultTab = ($requestedTab && array_key_exists($requestedTab, $availableTabs)) ? $requestedTab : array_key_first($availableTabs);
    @endphp

    <div x-data="{ tab: '{{ $defaultTab }}' }">
        <div class="mb-6 border-b border-slate-200">
            <nav class="-mb-px flex flex-wrap gap-1">
                @foreach ($availableTabs as $tabKey => $tabLabel)
                    <button type="button" @click="tab = '{{ $tabKey }}'"
                        :class="tab === '{{ $tabKey }}' ? 'border-emerald-700 text-emerald-700' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'"
                        class="border-b-2 px-4 py-3 text-sm font-medium transition">
                        {{ $tabLabel }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- General / Business Info --}}
        @can('manage-settings')
        <div x-show="tab === 'general'" x-cloak class="space-y-6">
            <x-eva.card title="Business Info">
                <form method="POST" action="{{ route('settings.general.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div class="flex items-center gap-4">
                        @if ($business['logo_path'] ?? null)
                            <img src="{{ asset('storage/'.$business['logo_path']) }}" alt="Logo" class="h-16 w-16 rounded-lg border border-slate-200 object-contain">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-lg border border-dashed border-slate-300 text-xs text-slate-400">No logo</div>
                        @endif
                        <div class="flex-1">
                            <label for="logo" class="mb-1 block text-sm font-medium text-slate-700">Logo</label>
                            <input type="file" name="logo" id="logo" accept="image/*"
                                class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-amber-700 hover:file:bg-amber-100">
                            @error('logo')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-eva.input name="name" label="Business Name" required :value="$business['name'] ?? null" />
                        <x-eva.input name="email" label="Email" type="email" :value="$business['email'] ?? null" />
                        <x-eva.input name="phone" label="Phone" :value="$business['phone'] ?? null" />
                        <x-eva.select name="timezone" label="Default Timezone" required
                            :options="array_combine($timezones, $timezones)" :value="$business['timezone'] ?? 'Africa/Dar_es_Salaam'" />
                    </div>

                    <x-eva.textarea name="address" label="Address" :rows="2" :value="$business['address'] ?? null" />

                    @php
                        $dateFormatOptions = [];
                        foreach ($dateFormats as $format => $example) {
                            $dateFormatOptions[$format] = "{$example} ({$format})";
                        }
                    @endphp
                    <x-eva.select name="date_format" label="Date Format" required
                        :options="$dateFormatOptions" :value="$business['date_format'] ?? 'd M Y'" />

                    <div class="flex justify-end pt-2">
                        <x-eva.button type="submit">Save Business Info</x-eva.button>
                    </div>
                </form>
            </x-eva.card>
        </div>
        @endcan

        {{-- Departments & Attributes --}}
        @can('manage-departments')
        <div x-show="tab === 'departments'" x-cloak>
            <x-eva.card title="Departments & Attributes">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 p-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Departments</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-900">{{ $departmentsSummary['departmentsCount'] }}</dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Active Departments</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-900">{{ $departmentsSummary['activeDepartmentsCount'] }}</dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Attribute Fields Configured</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-900">{{ $departmentsSummary['attributeFieldsCount'] }}</dd>
                    </div>
                </div>

                <p class="mt-4 text-sm text-slate-500">
                    Departments and their per-department attribute schemas (the dynamic product fields shown on the Product form) are managed on their own dedicated screens, not duplicated here.
                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <x-eva.button :href="route('departments.index')">Manage Departments</x-eva.button>
                    <x-eva.button variant="secondary" :href="route('departments.create')">+ New Department</x-eva.button>
                </div>
                <p class="mt-2 text-xs text-slate-400">Attribute schemas are configured per department — open a department and scroll to its Attribute Schema section to add or edit fields.</p>
            </x-eva.card>
        </div>
        @endcan

        {{-- Planning Tools --}}
        @can('manage-calculators')
        <div x-show="tab === 'planning'" x-cloak>
            <x-eva.card title="Planning Tools">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 p-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Calculators</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-900">{{ $planningToolsSummary['calculatorCount'] }}</dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Active Calculators</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-900">{{ $planningToolsSummary['activeCalculatorCount'] }}</dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Submissions</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-900">{{ $planningToolsSummary['submissionCount'] }}</dd>
                    </div>
                </div>

                <p class="mt-4 text-sm text-slate-500">
                    Each calculator's input fields, formulas, and product output mappings are configured on its own dedicated screen, not duplicated here.
                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <x-eva.button :href="route('calculator-types.index')">Manage Planning Tools</x-eva.button>
                    <x-eva.button variant="secondary" :href="route('calculator-types.create')">+ New Calculator</x-eva.button>
                </div>
            </x-eva.card>
        </div>
        @endcan

        {{-- Numbering Formats --}}
        @can('manage-settings')
        <div x-show="tab === 'numbering'" x-cloak>
            <x-eva.card title="Numbering Formats">
                <form method="POST" action="{{ route('settings.numbering.update') }}" class="space-y-5">
                    @csrf
                    @foreach ($numbering as $type => $config)
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-900">{{ $config['label'] }}</h3>
                                <span class="text-xs text-slate-400">Next: <span class="font-mono font-medium text-slate-700">{{ $config['preview'] }}</span></span>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <x-eva.input name="{{ $type }}_pattern" label="Pattern" :value="$config['pattern']" required />
                                </div>
                                <x-eva.input name="{{ $type }}_start" label="Starting Sequence" type="number" :value="$config['start']" />
                            </div>
                        </div>
                    @endforeach

                    <p class="text-xs text-slate-400">
                        Use {{ '{YEAR}' }} and {{ '{SEQ}' }} as placeholders — {{ '{SEQ}' }} must be last and always renders as a 4-digit zero-padded number, e.g. <span class="font-mono">PO-{{ '{YEAR}' }}-{{ '{SEQ}' }}</span> → <span class="font-mono">PO-{{ now()->year }}-0001</span>.
                        "Starting Sequence" only takes effect the first time a number is generated under a brand-new pattern — once records exist under it, the next number always continues from the highest existing one.
                    </p>

                    <div class="flex justify-end pt-2">
                        <x-eva.button type="submit">Save Numbering Formats</x-eva.button>
                    </div>
                </form>
            </x-eva.card>
        </div>
        @endcan

        {{-- Tax & Currency --}}
        @can('manage-settings')
        <div x-show="tab === 'tax'" x-cloak>
            <x-eva.card title="Tax & Currency">
                <form method="POST" action="{{ route('settings.tax.update') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-eva.input name="vat_rate" label="VAT Rate (%)" type="number" step="0.01" required :value="$tax['vat_rate_percent']" />
                        <div></div>
                        <x-eva.input name="currency_code" label="Currency Code" required :value="$tax['currency_code']" />
                        <x-eva.input name="currency_symbol" label="Currency Symbol" required :value="$tax['currency_symbol']" />
                        <x-eva.input name="reorder_multiplier" label="Reorder Multiplier" type="number" step="0.1" required :value="$tax['reorder_multiplier']" />
                    </div>

                    <p class="text-xs text-slate-400">
                        VAT is applied on top of the subtotal across POS sales, quotes, and the storefront cart/checkout — prices are never treated as VAT-inclusive.
                        The reorder multiplier drives the "suggested reorder quantity" in low-stock alerts: <span class="font-mono">max(0, reorder_point &times; multiplier &minus; current_stock)</span>.
                    </p>

                    <div class="flex justify-end pt-2">
                        <x-eva.button type="submit">Save Tax & Currency</x-eva.button>
                    </div>
                </form>
            </x-eva.card>
        </div>
        @endcan

        {{-- Notifications --}}
        @can('manage-settings')
        <div x-show="tab === 'notifications'" x-cloak class="space-y-6">
            <x-eva.card title="Alert Types & Recipients">
                <form method="POST" action="{{ route('settings.notifications.update') }}" class="space-y-5">
                    @csrf

                    @foreach ($notifications as $type => $config)
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-900">{{ $config['label'] }}</h3>
                                <x-eva.checkbox name="{{ $type }}_enabled" label="Enabled" :checked="$config['enabled']" />
                            </div>
                            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">Recipients</p>
                            <div class="flex flex-wrap gap-4">
                                @foreach ($allRoles as $role)
                                    @php $checkboxId = "{$type}_role_" . \Illuminate\Support\Str::slug($role); @endphp
                                    <label for="{{ $checkboxId }}" class="flex items-center gap-2 text-sm text-slate-700">
                                        <input type="checkbox" name="{{ $type }}_roles[]" id="{{ $checkboxId }}" value="{{ $role }}"
                                            @checked(in_array($role, $config['roles'], true))
                                            class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500">
                                        {{ $role }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div>
                        <p class="mb-1 text-sm font-medium text-slate-700">SMS Driver</p>
                        @php
                            $smsDriverOptions = [];
                            foreach ($smsDrivers as $driver) {
                                $smsDriverOptions[$driver] = $driver === 'none' ? 'None (disabled)' : ucfirst($driver);
                            }
                        @endphp
                        <div class="max-w-xs">
                            <x-eva.select name="sms_driver" :options="$smsDriverOptions" :value="$smsDriver" />
                        </div>
                        <p class="mt-1 text-xs text-slate-400">SMS sending isn't wired up yet — this only records which provider a future phase should integrate. Order confirmations currently log an "SMS stub" line instead of sending.</p>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-eva.button type="submit">Save Notification Settings</x-eva.button>
                    </div>
                </form>
            </x-eva.card>

            <x-eva.card title="Email Delivery">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Mail Driver</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ $mailInfo['driver'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">From Address</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ $mailInfo['from_address'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">From Name</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ $mailInfo['from_name'] ?? '—' }}</dd>
                    </div>
                </dl>
                <p class="mt-4 text-xs text-slate-400">
                    Mail driver, SMTP credentials, and the from-address are read from <span class="font-mono">.env</span> (<span class="font-mono">MAIL_*</span>) rather than duplicated here —
                    SMTP host/username/password are credentials that belong in the environment file, not a database table.
                    Edit <span class="font-mono">.env</span> and run <span class="font-mono">php artisan config:clear</span> to change them; this panel is read-only.
                </p>
            </x-eva.card>
        </div>
        @endcan

        {{-- Users & Roles --}}
        @can('manage-users')
        <div x-show="tab === 'users'" x-cloak>
            <x-eva.card title="Users & Roles">
                <div class="rounded-lg border border-slate-200 p-4 sm:w-64">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Users</dt>
                    <dd class="mt-1 text-2xl font-semibold text-slate-900">{{ $usersSummary['usersCount'] }}</dd>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <x-eva.button :href="route('users.index')">Manage Users</x-eva.button>
                    <x-eva.button variant="secondary" :href="route('users.create')">+ New User</x-eva.button>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-700">Roles &amp; Permissions</p>
                    @can('manage-roles')
                        <x-eva.button variant="secondary" :href="route('roles.create')">+ New Role</x-eva.button>
                    @endcan
                </div>

                <div class="mt-2 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Role</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Permissions</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Users</th>
                                @can('manage-roles')
                                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($usersSummary['roles'] as $role)
                                <tr>
                                    <td class="px-4 py-2 font-medium text-slate-900">{{ $role['name'] }}</td>
                                    <td class="px-4 py-2 text-right text-slate-600">{{ $role['permissionCount'] }}</td>
                                    <td class="px-4 py-2 text-right text-slate-600">{{ $role['userCount'] }}</td>
                                    @can('manage-roles')
                                        <td class="px-4 py-2 text-right">
                                            <a href="{{ route('roles.edit', $role['id']) }}" class="mr-3 font-medium text-emerald-700 hover:text-emerald-900">Edit</a>
                                            @if (! in_array($role['name'], ['Admin', 'Manager', 'Warehouse Staff', 'Viewer'], true) && $role['userCount'] === 0)
                                                <x-eva.confirm-delete :action="route('roles.destroy', $role['id'])" />
                                            @endif
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 text-xs text-slate-400">
                    A role's permission set is edited from here — click a role's "Edit" link. Role names can't be changed once created, since several places in the app match roles by name.
                    A user's role is assigned from the Users screen above.
                </p>
            </x-eva.card>
        </div>
        @endcan

        {{-- Audit Log --}}
        @can('manage-users')
        <div x-show="tab === 'audit'" x-cloak>
            <x-eva.card title="Audit Log">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border border-slate-200 p-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Entries</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-900">{{ $auditLogSummary['totalCount'] }}</dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Today</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-900">{{ $auditLogSummary['todayCount'] }}</dd>
                    </div>
                </div>

                @if ($auditLogSummary['latest'])
                    <p class="mt-4 text-sm text-slate-500">
                        Most recent: <span class="font-medium text-slate-700">{{ $auditLogSummary['latest']->user->name ?? 'System' }}</span>
                        {{ $auditLogSummary['latest']->action }} — {{ $auditLogSummary['latest']->created_at->diffForHumans() }}
                    </p>
                @else
                    <p class="mt-4 text-sm text-slate-400">No audit log entries recorded yet.</p>
                @endif

                <p class="mt-2 text-sm text-slate-500">
                    Every recorded staff action across the system — search and filter on its own dedicated screen.
                </p>

                <div class="mt-4">
                    <x-eva.button :href="route('audit-logs.index')">View Audit Log</x-eva.button>
                </div>
            </x-eva.card>
        </div>
        @endcan

        {{-- WhatsApp & Contact --}}
        @can('manage-settings')
        <div x-show="tab === 'whatsapp'" x-cloak>
            <x-eva.card title="WhatsApp & Contact">
                <form method="POST" action="{{ route('settings.whatsapp.update') }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-eva.input name="number" label="WhatsApp Number" required :value="$whatsapp['number']"
                            placeholder="255712345678" />
                        <div></div>
                    </div>
                    <p class="-mt-3 text-xs text-slate-400">International format, digits only, no leading + or spaces — this is the number every "Chat on WhatsApp" link across the storefront points to.</p>

                    <x-eva.textarea name="default_message" label="Default Pre-Filled Message" :rows="2" required :value="$whatsapp['default_message']" />
                    <p class="-mt-3 text-xs text-slate-400">Used by the generic WhatsApp buttons (navbar, footer, floating button). Product, order, and quote WhatsApp links keep their own contextual message regardless of this setting.</p>

                    <div>
                        <p class="mb-1 text-sm font-medium text-slate-700">Public Contact Page Overrides</p>
                        <p class="mb-3 text-xs text-slate-400">Optional. Leave blank to show the same address/phone/email set in the General tab. Fill these in only if the storefront's public contact page should show something different — e.g. a customer-service line instead of the internal office number.</p>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-eva.input name="contact_phone" label="Contact Page Phone" :value="$whatsapp['contact_phone'] ?? null" />
                            <x-eva.input name="contact_email" label="Contact Page Email" type="email" :value="$whatsapp['contact_email'] ?? null" />
                        </div>
                        <div class="mt-4">
                            <x-eva.textarea name="contact_address" label="Contact Page Address" :rows="2" :value="$whatsapp['contact_address'] ?? null" />
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-eva.button type="submit">Save WhatsApp & Contact</x-eva.button>
                    </div>
                </form>
            </x-eva.card>
        </div>
        @endcan

        {{-- Backup --}}
        @can('manage-settings')
        <div x-show="tab === 'backup'" x-cloak class="space-y-6">
            <x-eva.card title="Manual Backup">
                <p class="text-sm text-slate-500">
                    Dumps the full database via <span class="font-mono">mysqldump</span>, gzips it, and stores it privately — backups are never web-accessible except through this authenticated download link.
                    No backup package (e.g. spatie/laravel-backup) is installed; this is a minimal, self-contained mechanism.
                </p>
                <form method="POST" action="{{ route('settings.backup.run') }}" class="mt-4">
                    @csrf
                    <x-eva.button type="submit">Backup Now</x-eva.button>
                </form>

                <div class="mt-6 border-t border-slate-100 pt-5">
                    <p class="mb-2 text-sm font-medium text-slate-700">Scheduled Backups</p>
                    <form method="POST" action="{{ route('settings.backup.frequency') }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="max-w-xs">
                            <x-eva.select name="frequency" label="Frequency"
                                :options="['none' => 'Off', 'daily' => 'Daily (03:30)', 'weekly' => 'Weekly, Mondays (03:30)']"
                                :value="$backupFrequency" />
                        </div>
                        <x-eva.button type="submit" variant="secondary">Save</x-eva.button>
                    </form>
                    <p class="mt-2 text-xs text-slate-400">Requires the scheduler to be running (<span class="font-mono">php artisan schedule:work</span>, or a system cron calling <span class="font-mono">schedule:run</span> every minute) — same as the other scheduled jobs in this app.</p>
                </div>
            </x-eva.card>

            <x-eva.card title="Recent Backups">
                @if ($backups->isEmpty())
                    <p class="text-sm text-slate-400">No backups yet — click "Backup Now" above to create one.</p>
                @else
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">File</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Created</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Size</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($backups as $backup)
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-xs text-slate-700">{{ $backup['filename'] }}</td>
                                        <td class="px-4 py-2 text-slate-600">{{ $backup['created_at']->format('d M Y H:i') }}</td>
                                        <td class="px-4 py-2 text-right text-slate-600">{{ number_format($backup['size'] / 1024 / 1024, 2) }} MB</td>
                                        <td class="px-4 py-2 text-right">
                                            <a href="{{ route('settings.backup.download', $backup['filename']) }}" class="font-medium text-emerald-700 hover:text-emerald-900">Download</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-eva.card>
        </div>
        @endcan

        {{-- License --}}
        @can('manage-license')
        <div x-show="tab === 'license'" x-cloak>
            <x-eva.card title="License">
                @php
                    $licenseStatusColor = match ($licenseSummary['status']) {
                        'active' => 'emerald',
                        'grace' => 'amber',
                        'expired', 'invalid' => 'red',
                        default => 'slate',
                    };
                    $licenseStatusLabel = match ($licenseSummary['status']) {
                        'active' => 'Active',
                        'grace' => 'Grace Period',
                        'expired' => 'Expired',
                        'invalid' => 'Invalid',
                        default => 'Not Activated',
                    };
                @endphp

                <div class="flex flex-wrap items-center gap-4">
                    <div class="rounded-lg border border-slate-200 p-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Status</dt>
                        <dd class="mt-1"><x-eva.badge :color="$licenseStatusColor">{{ $licenseStatusLabel }}</x-eva.badge></dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Expires</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">
                            {{ $licenseSummary['expiresAt']?->format('d M Y') ?? '—' }}
                            @if ($licenseSummary['daysUntilExpiry'] !== null)
                                <span class="text-slate-400">({{ $licenseSummary['daysUntilExpiry'] }} days)</span>
                            @endif
                        </dd>
                    </div>
                </div>

                @if ($licenseSummary['isInGracePeriod'])
                    <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">Your license is in its grace period — renew soon to avoid losing access.</p>
                @endif

                <p class="mt-4 text-sm text-slate-500">Activation, feature entitlements, and renewal all happen on the dedicated License screen.</p>

                <div class="mt-4">
                    <x-eva.button :href="route('license.index')">Manage License</x-eva.button>
                </div>
            </x-eva.card>
        </div>
        @endcan
    </div>
</x-layouts.admin>

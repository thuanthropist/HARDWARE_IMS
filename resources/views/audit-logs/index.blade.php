<x-layouts.admin title="Audit Log" subtitle="Every recorded staff action across the system">
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="route('settings.index', ['tab' => 'audit'])">&larr; Back to Settings</x-eva.button>
    </x-slot:headerActions>

    <x-eva.table-card :paginator="$logs" empty="No audit log entries match this filter.">
        <x-slot:filters>
            <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="user_id" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                    <option value="">All users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>

                <select name="action" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                    <option value="">All actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                    @endforeach
                </select>

                <select name="model_type" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                    <option value="">All models</option>
                    @foreach ($modelTypes as $modelType)
                        <option value="{{ $modelType }}" @selected(request('model_type') === $modelType)>{{ class_basename($modelType) }}</option>
                    @endforeach
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                <span class="text-sm text-slate-400">to</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">

                <button type="submit" class="rounded-lg bg-amber-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-600">Filter</button>
                @if (request()->anyFilled(['user_id', 'action', 'model_type', 'date_from', 'date_to']))
                    <a href="{{ route('audit-logs.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>
        </x-slot:filters>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">When</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">User</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Affected</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Details</th>
            </tr>
        </x-slot:head>

        @foreach ($logs as $log)
            <tr>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $log->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-sm text-slate-900">{{ $log->user->name ?? 'System' }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge color="emerald">{{ $log->action }}</x-eva.badge>
                </td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</td>
                <td class="px-5 py-3 text-xs text-slate-500">
                    @if ($log->new_values)
                        <details>
                            <summary class="cursor-pointer text-emerald-700">View</summary>
                            <pre class="mt-1 max-w-xs overflow-x-auto rounded bg-slate-50 p-2">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                        </details>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>

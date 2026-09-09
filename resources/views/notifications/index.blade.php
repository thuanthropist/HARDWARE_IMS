<x-layouts.admin title="Notifications" subtitle="Everything that's come through for your account">
    <x-slot:headerActions>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <x-eva.button variant="secondary" type="submit">Mark All Read</x-eva.button>
        </form>
    </x-slot:headerActions>

    <x-eva.table-card :paginator="$notifications" empty="No notifications yet.">
        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Notification</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Received</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500"></th>
            </tr>
        </x-slot:head>

        @foreach ($notifications as $notification)
            <tr>
                <td class="px-5 py-3 text-sm text-slate-900">{{ $notification->data['title'] ?? 'Notification' }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $notification->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-sm">
                    @if ($notification->read_at)
                        <x-eva.badge color="slate">Read</x-eva.badge>
                    @else
                        <x-eva.badge color="emerald">Unread</x-eva.badge>
                    @endif
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" class="font-medium text-emerald-700 hover:text-emerald-900">View</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>

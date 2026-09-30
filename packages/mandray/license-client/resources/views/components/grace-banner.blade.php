@if ($show)
    <div
        x-data="{ dismissed: false }"
        x-show="!dismissed"
        x-cloak
        class="relative flex items-center justify-between gap-4 border-b border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
        role="alert"
    >
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <span>
                @if ($daysUntilExpiry !== null && $daysUntilExpiry >= 0)
                    Your license expires in {{ $daysUntilExpiry }} {{ $daysUntilExpiry === 1 ? 'day' : 'days' }}. Please renew soon to avoid interruption.
                @else
                    Your license has expired and is running on a grace period. Please renew immediately to avoid interruption.
                @endif
            </span>
        </div>
        <button type="button" @click="dismissed = true" class="shrink-0 text-amber-700 hover:text-amber-900" aria-label="Dismiss">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif

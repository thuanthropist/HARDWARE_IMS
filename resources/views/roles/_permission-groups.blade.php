@php
    $checked = $checked ?? [];
@endphp

<div class="space-y-5">
    @foreach ($permissionGroups as $group => $permissions)
        <div>
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">{{ $group }}</p>
            <div class="flex flex-wrap gap-4">
                @foreach ($permissions as $permission)
                    @php $checkboxId = 'permission_' . \Illuminate\Support\Str::slug($permission); @endphp
                    <label for="{{ $checkboxId }}" class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="permissions[]" id="{{ $checkboxId }}" value="{{ $permission }}"
                            @checked(in_array($permission, old('permissions', $checked), true))
                            class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500">
                        {{ $permission }}
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

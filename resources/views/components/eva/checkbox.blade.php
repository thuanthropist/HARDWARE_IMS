@props(['name', 'label' => null, 'checked' => false])

<label for="{{ $name }}" class="flex items-center gap-2 text-sm text-slate-700">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $name }}"
        value="1"
        @checked(old($name, $checked))
        {{ $attributes->class(['h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500']) }}
    >
    {{ $label }}
</label>

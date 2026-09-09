<x-layouts.admin title="Edit Warehouse" :subtitle="$warehouse->name">
    <x-eva.card>
        @include('warehouses._form')
    </x-eva.card>
</x-layouts.admin>

<x-layouts.admin title="Edit Supplier" :subtitle="$supplier->name">
    <x-eva.card>
        @include('suppliers._form')
    </x-eva.card>
</x-layouts.admin>

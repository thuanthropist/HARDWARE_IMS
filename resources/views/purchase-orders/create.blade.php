<x-layouts.admin title="New Purchase Order">
    <x-eva.card>
        @include('purchase-orders._form', ['purchaseOrder' => null])
    </x-eva.card>
</x-layouts.admin>

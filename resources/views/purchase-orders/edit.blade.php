<x-layouts.admin title="Edit Purchase Order" :subtitle="$purchaseOrder->po_number">
    <x-eva.card>
        @include('purchase-orders._form')
    </x-eva.card>
</x-layouts.admin>

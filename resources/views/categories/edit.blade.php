<x-layouts.admin title="Edit Category" :subtitle="$category->name">
    <x-eva.card>
        @include('categories._form')
    </x-eva.card>
</x-layouts.admin>

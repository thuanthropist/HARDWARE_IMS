<x-layouts.admin title="Dashboard" subtitle="Overview of your inventory across all departments">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-eva.stat-card label="Total Products" :value="number_format($totalProducts)" accent="emerald" />
        <x-eva.stat-card label="Total Stock Value" :value="number_format($stockValue, 0).' TZS'" accent="emerald" />
        <x-eva.stat-card label="Low Stock Items" :value="number_format($lowStockCount)" accent="amber" hint="At or below reorder point" />
    </div>

    <div class="mt-6">
        <x-eva.card title="Products by Department">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($departments as $department)
                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="text-sm font-medium text-slate-500">{{ $department->name }}</p>
                        <p class="mt-1 text-xl font-semibold text-slate-900">{{ number_format($department->products_count) }}</p>
                    </div>
                @endforeach
            </div>
        </x-eva.card>
    </div>

    <div class="mt-6">
        <x-eva.card title="Low Stock & Suggested Reorder">
            <x-slot:actions>
                <form method="GET" action="{{ route('dashboard') }}">
                    <select name="low_stock_department" onchange="this.form.submit()"
                            class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                        <option value="">All departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected($lowStockFilterDepartment === $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </form>
            </x-slot:actions>

            @if ($lowStockProducts->isEmpty())
                <p class="py-6 text-center text-sm text-slate-400">Nothing is at or below its reorder point right now.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Product</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Department</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Current Stock</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Reorder Point</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Suggested Reorder Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($lowStockProducts as $product)
                                @php $suggested = max(0, ($product->reorder_point * 2) - $product->currentStock); @endphp
                                <tr>
                                    <td class="px-3 py-2 font-medium text-slate-900">{{ $product->name }}</td>
                                    <td class="px-3 py-2 text-slate-600">{{ $product->department->name }}</td>
                                    <td class="px-3 py-2 text-right text-amber-600 font-medium">{{ number_format($product->currentStock) }}</td>
                                    <td class="px-3 py-2 text-right text-slate-600">{{ number_format($product->reorder_point) }}</td>
                                    <td class="px-3 py-2 text-right font-semibold text-emerald-700">{{ number_format($suggested) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-eva.card>
    </div>
</x-layouts.admin>

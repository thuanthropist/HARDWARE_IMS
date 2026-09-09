@php
    $exportParams = http_build_query(array_filter([
        'department_id' => $selectedDepartmentId,
        'date_from' => $from,
        'date_to' => $to,
    ]));
@endphp

<x-layouts.admin title="Analytics" subtitle="Department-aware performance across sales, stock and planning tools">
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('analytics.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">From</label>
                <input type="date" name="date_from" value="{{ $from }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">To</label>
                <input type="date" name="date_to" value="{{ $to }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Department</label>
                @if ($lockedDepartment)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-600">
                        {{ $departments->firstWhere('id', $selectedDepartmentId)?->name ?? 'Your department' }}
                    </div>
                @else
                    <select name="department_id" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                        <option value="">All departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected($selectedDepartmentId == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            <button type="submit" class="rounded-lg bg-amber-500 px-4 py-1.5 text-sm font-medium text-white hover:bg-amber-600">Apply</button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- TOP PRODUCTS -->
        <x-eva.card title="Top-Selling Products">
            <x-slot:actions>
                <a href="{{ route('analytics.export', 'top-products') }}?{{ $exportParams }}" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">Export CSV</a>
            </x-slot:actions>
            @if ($topProducts->isEmpty())
                <p class="py-8 text-center text-sm text-slate-400">No sales in this period.</p>
            @else
                <canvas id="chart-top-products" height="220"></canvas>
                <table class="mt-4 min-w-full divide-y divide-slate-100 text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($topProducts as $row)
                            <tr>
                                <td class="py-1.5 text-slate-900">{{ $row['product']->name }}</td>
                                <td class="py-1.5 text-right text-slate-500">{{ $row['qty'] }} units</td>
                                <td class="py-1.5 text-right font-medium">TZS {{ number_format($row['revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-eva.card>

        <!-- LOW / OUT OF STOCK -->
        <x-eva.card title="Low & Out-of-Stock by Department">
            <x-slot:actions>
                <a href="{{ route('analytics.export', 'low-stock') }}?{{ $exportParams }}" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">Export CSV</a>
            </x-slot:actions>
            @if ($stockStatusByDepartment->isEmpty())
                <p class="py-8 text-center text-sm text-slate-400">No data.</p>
            @else
                <canvas id="chart-stock-status" height="220"></canvas>
            @endif
        </x-eva.card>

        <!-- STOCK VALUATION -->
        <x-eva.card title="Stock Valuation by Department">
            <x-slot:actions>
                <a href="{{ route('analytics.export', 'stock-valuation') }}?{{ $exportParams }}" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">Export CSV</a>
            </x-slot:actions>
            @if ($stockValuation->isEmpty())
                <p class="py-8 text-center text-sm text-slate-400">No data.</p>
            @else
                <canvas id="chart-stock-valuation" height="220"></canvas>
            @endif
        </x-eva.card>

        <!-- PROFIT MARGIN -->
        <x-eva.card title="Profit Margin — Top Products">
            @if ($profitMargins->isEmpty())
                <p class="py-8 text-center text-sm text-slate-400">No sales in this period.</p>
            @else
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="py-2">Product</th>
                            <th class="py-2 text-right">Revenue</th>
                            <th class="py-2 text-right">Profit</th>
                            <th class="py-2 text-right">Margin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($profitMargins as $row)
                            <tr>
                                <td class="py-1.5">{{ $row['product']->name }}</td>
                                <td class="py-1.5 text-right">{{ number_format($row['revenue']) }}</td>
                                <td class="py-1.5 text-right {{ $row['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($row['profit']) }}</td>
                                <td class="py-1.5 text-right font-medium">{{ $row['margin_pct'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-eva.card>

        <!-- REORDER SUGGESTIONS -->
        <x-eva.card title="Reorder Suggestions">
            <x-slot:actions>
                <a href="{{ route('analytics.export', 'reorder-suggestions') }}?{{ $exportParams }}" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">Export CSV</a>
            </x-slot:actions>
            <p class="mb-3 text-xs text-slate-500">Based on 30-day sales velocity and a 14-day lead time.</p>
            @if ($reorderSuggestions->isEmpty())
                <p class="py-8 text-center text-sm text-slate-400">No reorders suggested right now.</p>
            @else
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="py-2">Product</th>
                            <th class="py-2 text-right">Daily Velocity</th>
                            <th class="py-2 text-right">Current Stock</th>
                            <th class="py-2 text-right">Suggested Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($reorderSuggestions->take(10) as $row)
                            <tr>
                                <td class="py-1.5">{{ $row['product']->name }}</td>
                                <td class="py-1.5 text-right">{{ $row['daily_velocity'] }}</td>
                                <td class="py-1.5 text-right">{{ $row['current_stock'] }}</td>
                                <td class="py-1.5 text-right font-semibold text-amber-600">{{ $row['suggested_reorder_qty'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-eva.card>

        <!-- ORDER FUNNEL -->
        <x-eva.card title="Order Funnel">
            <x-slot:actions>
                <a href="{{ route('analytics.export', 'order-funnel') }}?{{ $exportParams }}" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">Export CSV</a>
            </x-slot:actions>
            <p class="mb-3 text-xs text-slate-500">Store-wide — orders span multiple departments.</p>
            @if ($orderFunnel->isEmpty())
                <p class="py-8 text-center text-sm text-slate-400">No orders in this period.</p>
            @else
                <canvas id="chart-order-funnel" height="220"></canvas>
            @endif
        </x-eva.card>

        <!-- QUOTE FUNNEL -->
        <x-eva.card title="Quote Funnel">
            <x-slot:actions>
                <a href="{{ route('analytics.export', 'quote-funnel') }}?{{ $exportParams }}" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">Export CSV</a>
            </x-slot:actions>
            <p class="mb-3 text-xs text-slate-500">
                Store-wide.
                @if ($quoteFunnel['acceptance_rate'] !== null)
                    Acceptance rate: <strong>{{ $quoteFunnel['acceptance_rate'] }}%</strong>
                @endif
            </p>
            @if ($quoteFunnel['counts']->isEmpty())
                <p class="py-8 text-center text-sm text-slate-400">No quotes in this period.</p>
            @else
                <canvas id="chart-quote-funnel" height="220"></canvas>
            @endif
        </x-eva.card>

        <!-- CALCULATOR USAGE -->
        <x-eva.card title="Calculator Usage Insights" class="lg:col-span-2">
            @if ($calculatorInsights['total_submissions'] === 0)
                <p class="py-8 text-center text-sm text-slate-400">No calculator submissions in this period.</p>
            @else
                <div class="mb-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <p class="text-xs text-slate-500">Total Submissions</p>
                        <p class="text-lg font-bold text-slate-900">{{ $calculatorInsights['total_submissions'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Added to Cart</p>
                        <p class="text-lg font-bold text-slate-900">{{ $calculatorInsights['cart_conversion_rate'] }}%</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Requested as Quote</p>
                        <p class="text-lg font-bold text-slate-900">{{ $calculatorInsights['quote_conversion_rate'] }}%</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Quotes &rarr; Orders</p>
                        <p class="text-lg font-bold text-slate-900">{{ $calculatorInsights['quote_to_order_count'] }}</p>
                    </div>
                </div>

                <canvas id="chart-calculator-usage" height="180"></canvas>

                @if (! empty($calculatorInsights['common_inputs']))
                    <div class="mt-6 space-y-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Typical Inputs</p>
                        @foreach ($calculatorInsights['common_inputs'] as $typeName => $fields)
                            <div>
                                <p class="mb-1 text-sm font-medium text-slate-900">{{ $typeName }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($fields as $field)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700">
                                            {{ $field['label'] }}: avg {{ $field['avg'] }} (range {{ $field['min'] }}&ndash;{{ $field['max'] }})
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </x-eva.card>
    </div>
</x-layouts.admin>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const palette = ['#047857', '#f59e0b', '#0ea5e9', '#8b5cf6', '#ef4444', '#ec4899', '#14b8a6', '#64748b'];

        @if ($topProducts->isNotEmpty())
            new Chart(document.getElementById('chart-top-products'), {
                type: 'bar',
                data: {
                    labels: @json($topProducts->pluck('product.name')),
                    datasets: [{
                        label: 'Revenue (TZS)',
                        data: @json($topProducts->pluck('revenue')),
                        backgroundColor: '#047857',
                        borderRadius: 6,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } },
                },
            });
        @endif

        @if ($stockStatusByDepartment->isNotEmpty())
            new Chart(document.getElementById('chart-stock-status'), {
                type: 'bar',
                data: {
                    labels: @json($stockStatusByDepartment->keys()),
                    datasets: [
                        { label: 'Low Stock', data: @json($stockStatusByDepartment->pluck('low_stock')), backgroundColor: '#f59e0b', borderRadius: 6 },
                        { label: 'Out of Stock', data: @json($stockStatusByDepartment->pluck('out_of_stock')), backgroundColor: '#ef4444', borderRadius: 6 },
                    ],
                },
                options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        @endif

        @if ($stockValuation->isNotEmpty())
            new Chart(document.getElementById('chart-stock-valuation'), {
                type: 'bar',
                data: {
                    labels: @json($stockValuation->pluck('department')),
                    datasets: [
                        { label: 'Cost Value', data: @json($stockValuation->pluck('cost_value')), backgroundColor: '#0ea5e9', borderRadius: 6 },
                        { label: 'Potential Revenue', data: @json($stockValuation->pluck('revenue_value')), backgroundColor: '#16a34a', borderRadius: 6 },
                    ],
                },
                options: { scales: { y: { beginAtZero: true } } },
            });
        @endif

        @if ($orderFunnel->isNotEmpty())
            new Chart(document.getElementById('chart-order-funnel'), {
                type: 'bar',
                data: {
                    labels: @json($orderFunnel->keys()->map(fn($s) => ucfirst(str_replace('_',' ',$s)))),
                    datasets: [{
                        label: 'Orders',
                        data: @json($orderFunnel->values()),
                        backgroundColor: palette,
                        borderRadius: 6,
                    }],
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        @endif

        @if ($quoteFunnel['counts']->isNotEmpty())
            new Chart(document.getElementById('chart-quote-funnel'), {
                type: 'bar',
                data: {
                    labels: @json($quoteFunnel['counts']->keys()->map(fn($s) => ucfirst($s))),
                    datasets: [{
                        label: 'Quotes',
                        data: @json($quoteFunnel['counts']->values()),
                        backgroundColor: palette,
                        borderRadius: 6,
                    }],
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        @endif

        @if ($calculatorInsights['total_submissions'] > 0)
            new Chart(document.getElementById('chart-calculator-usage'), {
                type: 'bar',
                data: {
                    labels: @json($calculatorInsights['by_type']->keys()),
                    datasets: [{
                        label: 'Submissions',
                        data: @json($calculatorInsights['by_type']->pluck('count')),
                        backgroundColor: palette,
                        borderRadius: 6,
                    }],
                },
                options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        @endif
    });
</script>

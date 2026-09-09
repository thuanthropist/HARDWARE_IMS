<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CalculatorSubmission;
use App\Models\CalculatorType;
use App\Models\Department;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        [$departmentId, $lockedDepartment] = $this->resolveDepartmentScope($request);
        [$from, $to] = $this->resolveDateRange($request);

        return view('analytics.index', [
            'departments' => Department::active()->orderBy('name')->get(),
            'lockedDepartment' => $lockedDepartment,
            'selectedDepartmentId' => $departmentId,
            'from' => $from,
            'to' => $to,
            'topProducts' => $this->topProducts($departmentId, $from, $to),
            'stockStatusByDepartment' => $this->stockStatusByDepartment($departmentId),
            'stockValuation' => $this->stockValuationByDepartment($departmentId),
            'profitMargins' => $this->profitMargins($departmentId, $from, $to),
            'reorderSuggestions' => $this->reorderSuggestions($departmentId),
            'orderFunnel' => $this->orderFunnel($from, $to),
            'quoteFunnel' => $this->quoteFunnel($from, $to),
            'calculatorInsights' => $this->calculatorInsights($from, $to),
        ]);
    }

    public function export(Request $request, string $report): StreamedResponse
    {
        [$departmentId] = $this->resolveDepartmentScope($request);
        [$from, $to] = $this->resolveDateRange($request);

        [$filename, $headers, $rows] = match ($report) {
            'top-products' => [
                'top-products.csv',
                ['Product', 'Department', 'Qty Sold', 'Revenue (TZS)'],
                $this->topProducts($departmentId, $from, $to, 100)
                    ->map(fn (array $r) => [$r['product']->name, $r['product']->department->name, $r['qty'], $r['revenue']]),
            ],
            'low-stock' => [
                'low-stock.csv',
                ['Department', 'Low Stock Count', 'Out of Stock Count'],
                $this->stockStatusByDepartment($departmentId)
                    ->map(fn (array $v, string $k) => [$k, $v['low_stock'], $v['out_of_stock']])->values(),
            ],
            'stock-valuation' => [
                'stock-valuation.csv',
                ['Department', 'Cost Value (TZS)', 'Revenue Value (TZS)'],
                $this->stockValuationByDepartment($departmentId)
                    ->map(fn ($r) => [$r->department, round((float) $r->cost_value), round((float) $r->revenue_value)]),
            ],
            'reorder-suggestions' => [
                'reorder-suggestions.csv',
                ['Product', 'Daily Velocity', 'Current Stock', 'Suggested Reorder Qty'],
                $this->reorderSuggestions($departmentId)
                    ->map(fn (array $r) => [$r['product']->name, $r['daily_velocity'], $r['current_stock'], $r['suggested_reorder_qty']]),
            ],
            'order-funnel' => [
                'order-funnel.csv',
                ['Status', 'Count'],
                $this->orderFunnel($from, $to)->map(fn ($v, $k) => [$k, $v])->values(),
            ],
            'quote-funnel' => [
                'quote-funnel.csv',
                ['Status', 'Count'],
                collect($this->quoteFunnel($from, $to)['counts'])->map(fn ($v, $k) => [$k, $v])->values(),
            ],
            default => abort(404),
        };

        return response()->streamDownload(function () use ($headers, $rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array{0: ?int, 1: bool}
     */
    private function resolveDepartmentScope(Request $request): array
    {
        $user = auth()->user();

        if ($user->department_id !== null) {
            return [$user->department_id, true];
        }

        return [$request->integer('department_id') ?: null, false];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveDateRange(Request $request): array
    {
        return [
            $request->input('date_from', now()->subDays(30)->toDateString()),
            $request->input('date_to', now()->toDateString()),
        ];
    }

    /**
     * Combined revenue/quantity per product across storefront orders (confirmed or
     * further along) and POS sales — the two real sale channels in this system.
     */
    private function productSalesUnion(?int $departmentId, string $from, string $to): QueryBuilder
    {
        $orderAgg = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereNotIn('orders.status', ['pending_confirmation', 'rejected', 'cancelled'])
            ->whereBetween(DB::raw('DATE(orders.created_at)'), [$from, $to])
            ->when($departmentId, fn (QueryBuilder $q) => $q->where('products.department_id', $departmentId))
            ->selectRaw('order_items.product_id as product_id, SUM(order_items.quantity) as qty, SUM(order_items.line_total) as revenue')
            ->groupBy('order_items.product_id');

        $posAgg = DB::table('pos_sale_items')
            ->join('pos_sales', 'pos_sales.id', '=', 'pos_sale_items.pos_sale_id')
            ->join('products', 'products.id', '=', 'pos_sale_items.product_id')
            ->whereBetween(DB::raw('DATE(pos_sales.created_at)'), [$from, $to])
            ->when($departmentId, fn (QueryBuilder $q) => $q->where('products.department_id', $departmentId))
            ->selectRaw('pos_sale_items.product_id as product_id, SUM(pos_sale_items.quantity) as qty, SUM(pos_sale_items.line_total) as revenue')
            ->groupBy('pos_sale_items.product_id');

        return DB::query()
            ->fromSub($orderAgg->unionAll($posAgg), 'combined')
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(revenue) as total_revenue')
            ->groupBy('product_id');
    }

    private function topProducts(?int $departmentId, string $from, string $to, int $limit = 10): Collection
    {
        $rows = $this->productSalesUnion($departmentId, $from, $to)
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        $products = Product::with('department')->whereIn('id', $rows->pluck('product_id'))->get()->keyBy('id');

        return $rows
            ->map(fn ($row) => [
                'product' => $products->get($row->product_id),
                'qty' => (int) $row->total_qty,
                'revenue' => (float) $row->total_revenue,
            ])
            ->filter(fn (array $row) => $row['product'] !== null)
            ->values();
    }

    private function profitMargins(?int $departmentId, string $from, string $to, int $limit = 10): Collection
    {
        $rows = $this->productSalesUnion($departmentId, $from, $to)
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        $products = Product::with('department')->whereIn('id', $rows->pluck('product_id'))->get()->keyBy('id');

        return $rows
            ->map(function ($row) use ($products) {
                $product = $products->get($row->product_id);

                if (! $product) {
                    return null;
                }

                $revenue = (float) $row->total_revenue;
                $cost = (float) $product->cost_price * (int) $row->total_qty;
                $profit = round($revenue - $cost, 2);

                return [
                    'product' => $product,
                    'qty' => (int) $row->total_qty,
                    'revenue' => $revenue,
                    'profit' => $profit,
                    'margin_pct' => $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0.0,
                ];
            })
            ->filter()
            ->values();
    }

    private function reorderSuggestions(?int $departmentId, int $velocityDays = 30, int $leadTimeDays = 14): Collection
    {
        $from = now()->subDays($velocityDays)->toDateString();
        $to = now()->toDateString();

        $rows = $this->productSalesUnion($departmentId, $from, $to)->get();

        $products = Product::query()
            ->active()
            ->withComputedStock()
            ->whereIn('id', $rows->pluck('product_id'))
            ->get()
            ->keyBy('id');

        return $rows
            ->map(function ($row) use ($products, $velocityDays, $leadTimeDays) {
                $product = $products->get($row->product_id);

                if (! $product) {
                    return null;
                }

                $velocity = (int) $row->total_qty / $velocityDays;
                $suggested = (int) ceil($velocity * $leadTimeDays) - $product->currentStock;

                if ($suggested <= 0) {
                    return null;
                }

                return [
                    'product' => $product,
                    'daily_velocity' => round($velocity, 2),
                    'current_stock' => $product->currentStock,
                    'suggested_reorder_qty' => $suggested,
                ];
            })
            ->filter()
            ->sortByDesc('suggested_reorder_qty')
            ->values();
    }

    private function stockStatusByDepartment(?int $departmentId): Collection
    {
        return Product::query()
            ->active()
            ->withComputedStock()
            ->with('department')
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->get()
            ->groupBy(fn (Product $p) => $p->department->name)
            ->map(fn (Collection $group) => [
                'low_stock' => $group->filter(fn (Product $p) => $p->stockStatus === 'low_stock')->count(),
                'out_of_stock' => $group->filter(fn (Product $p) => $p->stockStatus === 'out_of_stock')->count(),
            ]);
    }

    private function stockValuationByDepartment(?int $departmentId): Collection
    {
        return collect(DB::table('stock_levels')
            ->join('product_variants', 'product_variants.id', '=', 'stock_levels.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->join('departments', 'departments.id', '=', 'products.department_id')
            ->when($departmentId, fn (QueryBuilder $q) => $q->where('products.department_id', $departmentId))
            ->selectRaw('departments.name as department,
                SUM(stock_levels.quantity * products.cost_price) as cost_value,
                SUM(stock_levels.quantity * (products.selling_price + COALESCE(product_variants.additional_price, 0))) as revenue_value')
            ->groupBy('departments.name')
            ->get());
    }

    private function orderFunnel(string $from, string $to): Collection
    {
        return Order::query()
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
    }

    private function quoteFunnel(string $from, string $to): array
    {
        $counts = Quote::query()
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $decided = ($counts['accepted'] ?? 0) + ($counts['rejected'] ?? 0) + ($counts['expired'] ?? 0);
        $acceptanceRate = $decided > 0 ? round((($counts['accepted'] ?? 0) / $decided) * 100, 1) : null;

        return ['counts' => $counts, 'acceptance_rate' => $acceptanceRate];
    }

    private function calculatorInsights(string $from, string $to): array
    {
        $submissions = CalculatorSubmission::query()
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->with('calculatorType')
            ->get();

        $byType = $submissions
            ->filter(fn (CalculatorSubmission $s) => $s->calculatorType !== null)
            ->groupBy(fn (CalculatorSubmission $s) => $s->calculatorType->name)
            ->map(fn (Collection $group) => [
                'count' => $group->count(),
                'avg_estimated_total' => round((float) $group->avg('estimated_total'), 2),
                'converted_to_cart' => $group->where('converted_to_cart', true)->count(),
                'converted_to_quote' => $group->whereNotNull('converted_to_quote_id')->count(),
            ]);

        $commonInputs = [];

        foreach (CalculatorType::with('inputFields')->get() as $type) {
            $typeSubmissions = $submissions->where('calculator_type_id', $type->id);

            if ($typeSubmissions->isEmpty()) {
                continue;
            }

            $fieldStats = [];

            foreach ($type->inputFields->where('input_type', 'number') as $field) {
                $values = $typeSubmissions
                    ->map(fn (CalculatorSubmission $s) => $s->input_data[$field->field_key] ?? null)
                    ->filter(fn ($v) => is_numeric($v))
                    ->map(fn ($v) => (float) $v);

                if ($values->isEmpty()) {
                    continue;
                }

                $fieldStats[] = [
                    'label' => $field->label,
                    'avg' => round($values->avg(), 1),
                    'min' => $values->min(),
                    'max' => $values->max(),
                ];
            }

            if ($fieldStats !== []) {
                $commonInputs[$type->name] = $fieldStats;
            }
        }

        $quoteIds = $submissions->pluck('converted_to_quote_id')->filter()->values();
        $quotesConvertedToOrder = Quote::whereIn('id', $quoteIds)->whereHas('order')->count();

        $total = $submissions->count();

        return [
            'total_submissions' => $total,
            'by_type' => $byType,
            'common_inputs' => $commonInputs,
            'cart_conversion_rate' => $total > 0 ? round($submissions->where('converted_to_cart', true)->count() / $total * 100, 1) : 0.0,
            'quote_conversion_rate' => $total > 0 ? round($submissions->whereNotNull('converted_to_quote_id')->count() / $total * 100, 1) : 0.0,
            'quote_to_order_count' => $quotesConvertedToOrder,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PosSaleRequest;
use App\Models\Department;
use App\Models\PosSale;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use App\Services\FulfillmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        return view('pos.index', [
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
            'canDiscount' => (bool) auth()->user()?->can('apply-pos-discount'),
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $code = trim((string) $request->query('code', ''));

        if ($code === '') {
            return response()->json(['found' => false]);
        }

        $variant = ProductVariant::query()
            ->where('sku', $code)
            ->orWhere('barcode', $code)
            ->with('product')
            ->first();

        if (! $variant || ! $variant->product->is_active) {
            return response()->json(['found' => false]);
        }

        $label = $variant->variant_name === 'Standard'
            ? $variant->product->name
            : "{$variant->product->name} ({$variant->variant_name})";

        return response()->json([
            'found' => true,
            'product_variant_id' => $variant->id,
            'name' => $label,
            'sku' => $variant->sku,
            'unit_price' => (float) $variant->product->selling_price + (float) $variant->additional_price,
            'available' => (int) $variant->availableStock,
        ]);
    }

    public function store(PosSaleRequest $request, FulfillmentService $fulfillment): RedirectResponse
    {
        $warehouseId = (int) $request->validated('warehouse_id');
        $itemsInput = $request->validated('items');

        $hasDiscount = collect($itemsInput)->contains(fn (array $item) => (float) ($item['discount_amount'] ?? 0) > 0);
        abort_if($hasDiscount && ! auth()->user()->can('apply-pos-discount'), 403, 'You are not permitted to apply discounts.');

        $variants = ProductVariant::query()
            ->with('product')
            ->whereIn('id', collect($itemsInput)->pluck('product_variant_id'))
            ->get()
            ->keyBy('id');

        $lines = [];
        $resolvedItems = [];

        foreach ($itemsInput as $item) {
            $variant = $variants->get((int) $item['product_variant_id']);
            abort_if(! $variant, 422, 'One of the scanned items is no longer available.');

            $unitPrice = (float) $variant->product->selling_price + (float) $variant->additional_price;
            $quantity = (int) $item['quantity'];
            $discount = min((float) ($item['discount_amount'] ?? 0), $unitPrice * $quantity);
            $lineTotal = round(($unitPrice * $quantity) - $discount, 2);

            $lines[] = ['product_variant_id' => $variant->id, 'quantity' => $quantity];
            $resolvedItems[] = [
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'product_name' => $variant->product->name,
                'sku' => $variant->sku,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'line_total' => $lineTotal,
            ];
        }

        $shortfalls = $fulfillment->findShortfalls($lines, $warehouseId);
        abort_if($shortfalls !== [], 422, 'Some items do not have enough stock at this warehouse — refresh and try again.');

        $sale = DB::transaction(function () use ($request, $warehouseId, $fulfillment, $lines, $resolvedItems): PosSale {
            $grossSubtotal = collect($resolvedItems)->sum(fn (array $i) => $i['unit_price'] * $i['quantity']);
            $discountTotal = collect($resolvedItems)->sum('discount_amount');
            $netSubtotal = $grossSubtotal - $discountTotal;
            $vatAmount = round($netSubtotal * setting('tax.vat_rate', 0.18), 2);
            $total = round($netSubtotal + $vatAmount, 2);

            $sale = PosSale::create([
                'sale_number' => PosSale::generateSaleNumber(),
                'cashier_id' => auth()->id(),
                'warehouse_id' => $warehouseId,
                'customer_name' => $request->validated('customer_name'),
                'customer_phone' => $request->validated('customer_phone'),
                'subtotal' => $grossSubtotal,
                'discount_amount' => $discountTotal,
                'vat_amount' => $vatAmount,
                'total' => $total,
                'payment_method' => $request->validated('payment_method'),
                'payment_reference' => $request->validated('payment_reference'),
            ]);

            foreach ($resolvedItems as $item) {
                $sale->items()->create($item);
            }

            $fulfillment->deduct($lines, $warehouseId, 'pos_sale', $sale->id);

            AuditLogService::log('pos.sale_completed', $sale, null, ['total' => $total]);

            return $sale;
        });

        return redirect()->route('pos.sales.show', $sale)->with('success', "Sale {$sale->sale_number} completed.");
    }

    public function salesIndex(Request $request): View
    {
        $sales = PosSale::query()
            ->with(['cashier', 'warehouse'])
            ->filter($request->only(['date_from', 'date_to', 'cashier_id', 'payment_method', 'department_id']))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pos.sales.index', [
            'sales' => $sales,
            'cashiers' => User::orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
        ]);
    }

    public function salesShow(PosSale $posSale): View
    {
        $posSale->load(['items', 'cashier', 'warehouse']);

        return view('pos.sales.show', ['sale' => $posSale]);
    }

    public function salesPdf(PosSale $posSale): Response
    {
        $posSale->load(['items', 'cashier', 'warehouse']);

        return Pdf::loadView('pdfs.pos-receipt', ['sale' => $posSale])->stream("{$posSale->sale_number}.pdf");
    }
}
